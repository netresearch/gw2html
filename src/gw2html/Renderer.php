<?php
namespace gw2html;

class Renderer
{
    protected $outdir;

    public function __construct($outdir)
    {
        $this->outdir = $outdir;

        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem(
            __DIR__ . '/../../data/templates/'
        );
        $this->twig = new \Twig_Environment(
            $loader,
            array(
                //'cache' => '/path/to/compilation_cache',
                'debug' => true
            )
        );
        $this->twig->addFunction(
            'email', new \Twig_Function_Function(array($this, 'htmlEmail'))
        );
        $this->twig->addFunction(
            'link', new \Twig_Function_Function(array($this, 'htmlLink'))
        );
        $this->twig->addFunction(
            'tel', new \Twig_Function_Function(array($this, 'htmlTelephone'))
        );
    }

    public function render($tplname, $vars = array())
    {
        $template = $this->twig->loadTemplate($tplname . '.htm');
        return $template->render($vars);
    }

    public function renderInto($filename, $tplname, $vars = array())
    {
        file_put_contents(
            $this->outdir . '/' . $filename,
            $this->render($tplname, $vars)
        );
    }

    public function htmlEmail($email, $append = '')
    {
        if (trim($email) == '') {
            return '';
        }

        return '<a href="mailto:' . htmlspecialchars($email) . '">'
            . htmlspecialchars($email)
            . '</a>' . $append;
    }

    public function htmlLink($url, $append = '')
    {
        if (trim($url) == '') {
            return '';
        }

        return '<a href="' . htmlspecialchars($url) . '">'
            . htmlspecialchars($url)
            . '</a>' . $append;
    }

    public function htmlTelephone($value)
    {
        if (trim($value) == '') {
            return '';
        }

        $number = str_replace(
            array('+', ' ', '-', '/'),
            array('00', '', '', ''),
            $this->format_telephone_number_rfc3966($value)
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
?>
