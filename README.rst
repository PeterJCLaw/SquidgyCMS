The All Squidgy Content Management System
=========================================

The All Squidgy Content Management System (SquidgyCMS) is a flat-file
CMS designed for use where you don't have access to a database. All the
data that it stores is contained on the file-system, and is strored in a
manner that is meant to be understandable by, and editable by, a human.


Getting Started
---------------

There is very little setup required for SquidgyCMS, all you need to do
is make the site data folder (Sites/$SITE/Data) and the users folder
(Sites/$SITE/Users) read-writeable by the webserver. These are the two
locations that SquidgyCMS stores all the module and user data
respectively.


Organisation
------------

Almost everything that does something in SquidgyCMS is a module,
including most of the core functionality.

