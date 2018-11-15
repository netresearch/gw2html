<?php
namespace gw2html;

/**
 * Class Contact
 * @package gw2html
 */
class Contact
{
    /**
     * @var array
     */
    public $contacts = [];

    /**
     * @param bool $bWithCompany
     * @return string
     */
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

    /**
     * @param bool $bWithCompany
     * @return string
     */
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

    /**
     * @return bool
     */
    public function isCompany()
    {
        return $this->Name == '' && $this->CompName != '';
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    public function getCompanyFilename()
    {
        return $this->sanitizeFilename($this->CompName);
    }

    /**
     * @param $filename
     * @return string
     */
    protected function sanitizeFilename($filename)
    {
        return trim(
            str_replace(
                [' ', '/', '"', '\'', '&'],
                ['-', '-', '', '', 'und'],
                strtolower($filename)
            ),
            '- .'
        );
    }

    /**
     * @return string
     */
    public function getMapLink()
    {
        $oneline = $this->Street1 . ', ' . $this->Zip1 . ' ' . $this->Town1;

        return sprintf(
            '(<a href="%s">Karte</a>)',
            'http://maps.google.de/?q='
            . urlencode($oneline)
        );
    }


    /**
     * @return bool
     */
    public function hasContacts()
    {
        return count($this->contacts) > 0;
    }

    /**
     * @param $pdo
     * @param $arFieldsToFetch
     * @return bool
     */
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
        $stmt->execute([':company' => $this->CompName]);
        while ($contact = $stmt->fetchObject('gw2html\Contact')) {
            if (!$contact->isCompany()) {
                $this->contacts[] = $contact;
            }
        }
    }
}
