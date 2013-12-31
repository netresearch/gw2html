#!/usr/bin/env php
<?php
namespace gw2html;
require_once __DIR__ . '/data/config.php';
set_include_path(__DIR__ . '/src/' . PATH_SEPARATOR . get_include_path());

spl_autoload_register(
    function ($class) {
        $file = str_replace(array('\\', '_'), '/', $class) . '.php';
        if (stream_resolve_include_path($file) !== false) {
            require $file;
        }
    }
);

$quiet = array_search('--quiet', $argv) > 0 || array_search('-q', $argv) > 0;

function log($msg)
{
    global $quiet;
    if (!$quiet) {
        echo $msg;
    }
}

$arFieldsToFetch = array(
    'Birthday',
    'ChristianName',
    'CompName',
    'Department',
    'FaxFieldStr1',
    'FaxFieldStr5',
    'gwImageGUID',
    'MailFieldStr1',//Email geschäftlich
    'MailFieldStr3',//Email privat
    'MailFieldStr5',//Email Firma
    'Name',
    'Notes',
    'PhoneFieldStr2',//Handy
    'PhoneFieldStr4',//Telefon geschäftlich
    'PhoneFieldStr7',//Telefon privat
    'PhoneFieldStr10',
    'PhoneId2',
    'PhoneId4',
    'PhoneId10',
    'Street1',
    'Town1',
    'WWWFieldStr1',
    'Zip1',
);


$pdo = new \PDO(
    'dblib:host=' . $dbhost . ';dbname=' . $dbname,
    $dbuser, $dbpass,
    array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
);
//work around PHP bug https://bugs.php.net/bug.php?id=65945
$pdo2 = new \PDO(
    'dblib:host=' . $dbhost . ';dbname=' . $dbname,
    $dbuser, $dbpass,
    array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
);

$stmt = $pdo->query(
    'SELECT ' . implode(',', $arFieldsToFetch)
    . ' FROM ADDRESS0'
    . ' WHERE ('
    . ' (Name IS NOT NULL AND Name != \'\')'
    . ' OR (CompName IS NOT NULL AND CompName != \'\')'
    . ' )'
    //. ' AND CompName = \'Netresearch GmbH & Co. KG\''
    . ' ORDER BY'
    . ' CASE WHEN Name IS NULL OR Name = \'\' THEN LTRIM(CompName) ELSE LTRIM(Name) END'
    . ', LTRIM(ChristianName), LTRIM(CompName)'
);

$renderer = new Renderer(
    __DIR__ . '/www/',
    array(
        'urlprefix' => $urlprefix,
        'indexes' => $indexes
    )
);

$all = new Index('index.htm', $renderer);

$companies = new Index('index-companies.htm', $renderer);
$companies->title = 'Adressbuch: Firmen';
$companies->bWithCompany = false;

$people = new Index('index-people.htm', $renderer);
$people->title = 'Adressbuch: Personen';

while ($contact = $stmt->fetchObject('gw2html\Contact')) {
    $contact->loadContacts($pdo2, $arFieldsToFetch);
    //html
    $renderer->renderInto(
        $contact->getFilename() . '.htm',
        'contact',
        array('contact' => $contact)
    );
    //snom xml
    $renderer->renderInto(
        $contact->getFilename() . '.xml',
        'contact-snom',
        array('contact' => $contact)
    );
    //snom company staff
    if ($contact->isCompany()) {
        $renderer->renderInto(
            $contact->getFilename() . '-staff.xml',
            'company-staff-snom',
            array('contact' => $contact)
        );
    }

    //indexes
    $all->add($contact);
    if ($contact->isCompany()) {
        $companies->add($contact);
    } else {
        $people->add($contact);
    }
    log('.');
}

$all->finish();
$companies->finish();
$people->finish();

$indexFormats = array(
    ''      => '.htm',
    '-snom' => '.xml'
);
foreach ($indexes as $indexData) {
    foreach ($indexFormats as $suffix => $extension) {
        $index = new Index($indexData['file'] . $extension, $renderer);
        $index->title = $indexData['title'];
        $index->template = 'index' . $suffix;

        foreach ($indexData['entries'] as $filename => $title) {
            if ($suffix == '-snom') {
                //directly link to company staff in snom xml files
                $filename .= '-staff';
            }
            $index->addPlain($filename, $title);
        }
        $index->finish();
    }
}

log("done\n");
?>
