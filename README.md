# Generic Data Access Objects (GDAO)

## Description

A package containing abstract class definitions that can be used as a basis for a Table Data Gateway-ish (http://bit.ly/1F8Zjfc) implementation of a DB access library that performs data manipulation (DM) tasks.
It has 3 Main Classes:

* a **Model** class (it interacts with a DB table by performing DM tasks like Selection, Insertion, Deletion & Updating of data)

* a **Record** class (represents a row of data in a DB table, & only accesses the DB via a Model)

* an optional **Collection** class (holds multiple Record objects & supports batch operations on Records)

This API is intended to make it easy / trivial to swap out different implementations of each of the Main classes.
For example, an application may have been written to use a package with **ModelYY**, **CollectionYY** and **RecordYY**.
In the future if another package implementing this API has a **ModelZZ** which performs some operations more
efficiently than **ModelYY**, you should be able to easily substitute **ModelYY** with **ModelZZ** if all your
data access code strictly adheres to the GDAO API (you would now end up using **ModelZZ**, **CollectionYY** 
and **RecordYY** in your updated code; which should all work nicely together).

This package isn't meant to perform DB schema management tasks like creating/altering tables, etc. However, it exposes a PDO object (via \GDAO\Model->getPDO()) that can be used to perform such tasks and other data manipulation (DM) tasks that cannot be accomplished via this API.


##  Assumptions and Conventions in this API. 

* Each database table has a single auto-incrementing numeric primary key column 

* This API is architected with the intent of having Records and Collections created via the Model.
> Users of any implementation of this API should not be directly instantiating new Collections or Records via their constructors, instead they should create them by calling the appropriate implementation of \GDAO\Model::createCollection(..) or \GDAO\Model::createRecord(..).

## Definition of terms used in the phpdoc comment blocks:
 
 * **Consumer:** an individual developer or group of developers that use a package / library containing concrete implementation of the APIs specified in the abstract classes in this package.
 
 * **Implementer:** The developer that creates a package / library containing concrete implementation of the APIs specified in the abstract classes in this package.
