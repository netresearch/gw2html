**************************************
Genesis World address book HTML export
**************************************

Export all contacts from a CAS__ `Genesis World`__ CRM database
into static HTML files.

The Genesis World LDAP bridge does not export data like mobile phone
numbers and cities, so this tool directly accesses the SQL server
database.

__ http://www.cas.de/
__ http://www.cas.de/produkte/unternehmen/cas-genesisworld.html


========
Features
========
- A HTML file for each contact and company in the address book
- Company file links all associated contacts (staff/employees)
- Index file for companies, people and all entries
- Custom index files
- Snom IP phone XML files (contacts, companies and custom indexes)

=====
Setup
=====
- Twig__ template engine::

    $ pear channel-discover pear.twig-project.org
    $ pear install twig/Twig

- PHP extension ``dblib`` required (Debian package ``php5_sybase``)
- Set ``tds version = 7.0`` in ``/etc/freetds/freetds.conf`` to enable UTF-8
  support.
- Copy ``data/config.php.dist`` to ``data/config.php`` and adjust it
- Point your web server document root to ``gw2html/www/``
- Setup a cronjob every night to run ``genhtml.php``

__ http://twig.sensiolabs.org/



Snom IP phone usage
===================
Direcly show an entry on your phone from your desktop browser::

  http://ip-of-phone/minibrowser.htm?url=http://example.org/entry.xml


==========
References
==========

Snom XML
========
- http://wiki.snom.com/XML/Minibrowser
- http://wiki.snom.com/XML/Minibrowser/SnomIPPhoneMenu
- http://wiki.snom.com/XML/Minibrowser/SnomIPPhoneDirectory


====
TODO
====
- Image export. I don't know where the images are stored in the database.
  If you know, please tell me.


=======
License
=======
gw2html is licensed under the AGPL__ v3.

__ https://www.gnu.org/licenses/agpl-3.0.html

======
Author
======
`Christian Weiske`__, `Netresearch GmbH & Co. KG`__

__ mailto:christian.weiske@netresearch.de
__ http://www.netresearch.de/
