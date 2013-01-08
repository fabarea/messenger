Configuration
==============

Extension can be configured in the Extension Manager.

Table structure
================

In order to have a table of users displayed in the BE module a "table structure" must be provided where it is defined
what sort of that should be displayed.


Table header
--------------

* propertyName - mandatory - the name of the property
* label - mandatory - the label of the property - example: LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:email,
* width - optional - a width for the column - "example: 30%"
* style - optional - a style for the column - "background-color: red"
* className - optional - class names for the column - "foo bar"

Note that the table structure is validate against a table structure validator.

PHP interface for User
=======================

A User (programming) interface is provided making sure a user can be correctly displayed within the table. This is

* interface: email, uid

Todo (long term)
=================

+ Add a possible "Mailing" Domain Model object.
+ Add filtering possibility.
+ Add an option to load or not the BE module.

Development
=============

Current repository is at https://github.com/gebruederheitz/messenger
