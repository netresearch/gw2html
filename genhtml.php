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


$arFieldsToFetch = array(
    'AddressLetter',
    'Birthday',
    'ChristianName',
    'CompName',
    'Department',
    'FaxFieldStr1',
    'FaxFieldStr5',
    'gwImageGUID',
    'MailFieldStr1',
    'MailFieldStr5',
    'Name',
    'Notes',
    'PhoneFieldStr2',
    'PhoneFieldStr4',
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
    //. ' AND CompName = \'Deutsche Telekom AG Leipzig\''
    . ' ORDER BY'
    . ' CASE WHEN Name IS NULL OR Name = \'\' THEN LTRIM(CompName) ELSE LTRIM(Name) END'
    . ', LTRIM(ChristianName), LTRIM(CompName)'
);

$renderer = new Renderer(__DIR__ . '/www/');

$all = new Index('index.htm', $renderer);

$companies = new Index('index-companies.htm', $renderer);
$companies->title = 'Adressbuch: Firmen';
$companies->bWithCompany = false;

$people = new Index('index-people.htm', $renderer);
$people->title = 'Adressbuch: Personen';

while ($contact = $stmt->fetchObject('gw2html\Contact')) {
    $contact->loadContacts($pdo2, $arFieldsToFetch);
    $renderer->renderInto(
        $contact->getFilename() . '.htm',
        'contact',
        array('contact' => $contact)
    );
    $all->add($contact);
    if ($contact->isCompany()) {
        $companies->add($contact);
    } else {
        $people->add($contact);
    }
    echo '.';
}

$all->finish();
$companies->finish();
$people->finish();

echo "done\n";
?>