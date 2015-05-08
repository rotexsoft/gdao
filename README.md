# Generic Data Access Objects (GDAO)

## Description

A package containing abstract class definitions that can be used as a basis for a Table Data Gateway-ish (http://bit.ly/1F8Zjfc) implementation of a DB access library that performs data manipulation (DM)  tasks.
It has 3 Main Classes:
* a Model class (it interacts with a DB table by performing DM tasks like Selection, Insertion, Deletion & Updating of data)
* a Record class (represents a row of data in a DB table, & only accesses the DB via a Model)
* an optional Collection class (holds multiple Record objects & supports batch operations on Records)

This package isn't meant to perform DB schema management tasks like creating/altering tables, etc. However, it exposes a PDO object (via \GDAO\Model->getPDO()) that can be used to perform such tasks.
