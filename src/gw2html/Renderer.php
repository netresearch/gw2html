<?php
namespace gw2html;

use Twig\Environment;
use Twig\TwigFunction;

/**
 * Class Renderer
 * @package gw2html
 */
class Renderer
{
    /**
     * @var
     */
    protected $outdir;
    /**
     * @var
     */
    protected $variables;

    /**
     * Renderer constructor.
     * @param $outdir
     * @param $variables
     */
    public function __construct($outdir, $variables)
    {
        $this->outdir = $outdir;
        $this->variables = $variables;

        $loader = new \Twig_Loader_Filesystem(
            __DIR__ . '/../../data/templates/'
        );

        $this->twig = new Environment(
            $loader,
            [
                //'cache' => '/path/to/compilation_cache',
                //'debug' => true,
            ]
        );
        $this->twig->addFunction(
            new TwigFunction('email', [$this, 'htmlEmail'])
        );
        $this->twig->addFunction(
            new TwigFunction('link', [$this, 'htmlLink'])
        );
        $this->twig->addFunction(
            new TwigFunction('tel', [$this, 'htmlTelephone'])
        );
        $this->twig->addFunction(
            new TwigFunction('snomtel', [$this, 'snomTelephone'])
        );
    }

    /**
     * @param $tplname
     * @param array $vars
     * @return false|string
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function render($tplname, $vars = [])
    {
        $template = $this->twig->resolveTemplate($tplname . '.twig');
        $vars = array_merge($this->variables, $vars);
        return $template->render($vars);
    }

    /**
     * @param $filename
     * @param $tplname
     * @param array $vars
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function renderInto($filename, $tplname, $vars = [])
    {
        file_put_contents(
            $this->outdir . '/' . $filename,
            $this->render($tplname, $vars)
        );
    }

    /**
     * @param $email
     * @param string $append
     * @return string
     */
    public function htmlEmail($email, $append = '')
    {
        if (trim($email) == '') {
            return '';
        }

        return '<a href="mailto:' . htmlspecialchars($email) . '">'
            . htmlspecialchars($email)
            . '</a>' . $append;
    }

    /**
     * @param $url
     * @param string $append
     * @return string
     */
    public function htmlLink($url, $append = '')
    {
        if (trim($url) == '') {
            return '';
        }

        return '<a href="' . htmlspecialchars($url) . '">'
            . htmlspecialchars($url)
            . '</a>' . $append;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function cleanTelephone($value)
    {
        return str_replace(
            [' ', '-', '/'],
            ['', '', ''],
            $this->format_telephone_number_rfc3966($value)
        );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function snomTelephone($value)
    {
        //snom does not like 0049 or +49
        // we also need a 0 to dial out of the office
        return str_replace(
            ['+49', '0049'],
            ['00', '00'],
            $this->format_telephone_number_rfc3966($value)
        );
    }

    /**
     * @param $value
     * @return string
     */
    public function htmlTelephone($value)
    {
        if (trim($value) == '') {
            return '';
        }

        $number =  str_replace(
            ['+'],
            ['00'],
            $this->cleanTelephone($value)
        );
        return sprintf('
            <a href="tel:%s">%s</a>
            ', $number, htmlspecialchars($value)
        );
    }

    /**
     * Convert a telephone number into a full-featured RFC 3966 telephone number
     *
     * @param string $orig Original telephone number, may be partial
     * @param array  $conf Configuration. Keys:
     *                     - "countryCode": default country code, "49" for Germany
     *                     - "areaCode": default area code, without leading zero
     *
     * @return string Full RFC 3966-compatible telephone number
     *
     * @author Christian Weiske <cweiske@cweiske.de>
     */
    public function format_telephone_number_rfc3966($orig, $conf = array())
    {
        if (!isset($conf['countryCode'])) {
            $conf['countryCode'] = '49';//germany
        }
        if (!isset($conf['areaCode'])) {
            $conf['areaCode'] = '341';
        }

        $num = preg_replace('#[^+0-9]#', '', $orig);
        if (substr($num, 0, 1) == '+') {
            //full telephone number
            $tel = $num;
        } else if (substr($num, 0, 2) == '00') {
            //full number with country code, but 00 instead of +
            $tel = '+' . substr($num, 2);
        } else if (substr($num, 0, 1) == '0') {
            //full number without country code
            $tel = '+' . $conf['countryCode'] . substr($num, 1);
        } else {
            //partial number, no country or area code
            $tel = '+' . $conf['countryCode'] . $conf['areaCode'] . $num;
        }

        return $tel;
    }
}
