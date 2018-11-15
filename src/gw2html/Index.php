<?php
namespace gw2html;

/**
 * Class Index
 * @package gw2html
 */
class Index
{
    /**
     * @var
     */
    public $filename;
    /**
     * @var Renderer
     */
    public $renderer;
    /**
     * @var string
     */
    public $title = 'Adressbuch';
    /**
     * @var string
     */
    public $template = 'index';
    /**
     * @var bool
     */
    public $bWithCompany = true;
    /**
     * @var string
     */
    public $list = '';


    /**
     * Index constructor.
     * @param $filename
     * @param Renderer $renderer
     */
    public function __construct($filename, Renderer $renderer)
    {
        $this->filename = $filename;
        $this->renderer = $renderer;
    }

    /**
     * @param Contact $contact
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function add(Contact $contact)
    {
        $this->list .= $this->renderer->render(
            $this->template . '-entry',
            array(
                'contact' => $contact,
                'bWithCompany' => $this->bWithCompany
            )
        );
    }

    /**
     * @param $filename
     * @param $title
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function addPlain($filename, $title)
    {
        $this->list .= $this->renderer->render(
            $this->template . '-entry-plain',
            array(
                'filename' => $filename,
                'title' => $title,
            )
        );
    }

    /**
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function finish()
    {
        $this->renderer->renderInto(
            $this->filename,
            $this->template,
            array(
                'title' => $this->title,
                'filename' => substr($this->filename, 0, -4),
                'entries' => $this->list
            )
        );
    }
}
