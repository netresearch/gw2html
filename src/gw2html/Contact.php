<?php
namespace gw2html;

class Contact
{
    public $contacts = array();

    public function getName($bWithCompany = true)
    {
        if ($this->isCompany()) {
            return $this->CompName;
        }

        $name = $this->ChristianName . ' ' . $this->Name;
        if ($bWithCompany) {
            $name .= ', ' . $this->CompName;
        }
        return trim($name, ' ,');
    }

    public function getSortName($bWithCompany = true)
    {
        if ($this->isCompany()) {
            return $this->CompName;
        }

        $name = $this->Name . ', ' . $this->ChristianName;
        if ($bWithCompany) {
            $name .= ', ' . $this->CompName;
        }
        return trim($name, ' ,');
    }

    public function isCompany()
    {
        return $this->Name == '' && $this->CompName != '';
    }

    public function getFilename()
    {
        if ($this->isCompany()) {
            return $this->getCompanyFilename();
        }

        $name = $this->ChristianName
            . '-' . $this->Name
            . '-' . $this->CompName;

        return $this->sanitizeFilename($name);
    }

    public function getCompanyFilename()
    {
        return $this->sanitizeFilename($this->CompName);
    }

    protected function sanitizeFilename($filename)
    {
        return trim(
            str_replace(
                array(' ', '/', '"', '\'', '&'),
                array('-', '-', '', '', 'und'),
                strtolower($filename)
            ),
            '- .'
        );
    }

    public function getMapLink()
    {
        $oneline = $this->Street1 . ', ' . $this->Zip1 . ' ' . $this->Town1;
        return sprintf(
            '(<a href="%s">Karte</a>)',
            'http://maps.google.de/?q='
            . urlencode($oneline)
        );
    }


    public function hasContacts()
    {
        return count($this->contacts) > 0;
    }

    public function loadContacts($pdo, $arFieldsToFetch)
    {
        if (!$this->isCompany()) {
            return false;
        }

        $stmt = $pdo->prepare(
            'SELECT ' . implode(',', $arFieldsToFetch)
            . ' FROM ADDRESS0 WHERE CompName = :company'
            . ' ORDER BY Name, ChristianName'
        );
        $stmt->execute(array(':company' => $this->CompName));
        while ($contact = $stmt->fetchObject('gw2html\Contact')) {
            if (!$contact->isCompany()) {
                $this->contacts[] = $contact;
            }
        }
    }
}
?>
