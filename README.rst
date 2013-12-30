**************************************
Genesis World address book HTML export
**************************************

Export all contacts into static HTML files.


=====
Setup
=====
- Twig template engine required
- PHP extension dblib required (Debian package ``php5_mssql``);
- Set `tds version = 7.0` in ``/etc/freetds/freetds.conf`` to enable UTF-8
  support.
- Setup a cronjob every night to run ``genhtml.php``
