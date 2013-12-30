<?php
namespace gw2html;

class Index
{
    public $filename;
    public $renderer;
    public $title = 'Adressbuch';
    public $template = 'index';
    public $bWithCompany = true;
    public $list = '';


    public function __construct($filename, Renderer $renderer)
    {
        $this->filename = $filename;
        $this->renderer = $renderer;
    }

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

    public function finish()
    {
        $this->renderer->renderInto(
            $this->filename,
            $this->template,
            array(
                'title' => $this->title,
                'entries' => $this->list
            )
        );
    }
}
?>
