# Generic Data Access Objects (GDAO) 

[![Build Status](https://img.shields.io/travis/rotexsoft/gdao/master.png?style=flat-square)](https://travis-ci.org/rotexsoft/gdao) &nbsp; 
[![Coverage Status](https://coveralls.io/repos/github/rotexsoft/gdao/badge.svg)](https://coveralls.io/github/rotexsoft/gdao) &nbsp; 
![GitHub repo size](https://img.shields.io/github/repo-size/rotexsoft/gdao) &nbsp; 
![Packagist Downloads](https://img.shields.io/packagist/dt/rotexsoft/gdao) &nbsp; 
![GitHub top language](https://img.shields.io/github/languages/top/rotexsoft/gdao) &nbsp; 
![Libraries.io dependency status for GitHub repo](https://img.shields.io/librariesio/github/rotexsoft/gdao) &nbsp; 
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/rotexsoft/gdao) &nbsp; 
![GitHub commits since latest release (by date)](https://img.shields.io/github/commits-since/rotexsoft/gdao/latest)
![GitHub last commit](https://img.shields.io/github/last-commit/rotexsoft/gdao) &nbsp; 
![GitHub Release Date](https://img.shields.io/github/release-date/rotexsoft/gdao)


## Description

A package containing class and interface definitions that can be used as a basis for a __**Table Data Gateway**__ (http://bit.ly/1F8Zjfc) and __**Data Mapper**__ (http://bit.ly/1hD2qCc) implementation of a database access library that performs data manipulation (DM) tasks.
Below are its main components:

* an abstract **Model** class (it's the __**Table Data Gateway**__ and __**Data Mapper**__ that interacts with a database table by performing DM tasks like Selection, Insertion, Deletion & Updating of data)

* a **RecordInterface** interface (contains definitions of methods that any class __**representing a row of data in a database table**__, MUST IMPLEMENT. Such classes can only access a database table via an instance of the Model class)

* an optional **CollectionInterface** interface (contains definitions of methods that any class that is to __**serve as a collection of multiple instances of RecordInterface objects**__, MUST IMPLEMENT)

* a **RecordsList** class (a collection-like data structure that stores only instances of **RecordInterface**. It's the only data-structure that the **CollectionInterface** constructor accepts for passing one or more records to a Collection object. It guarantees that only instances of **RecordInterface** are stored in instances of **CollectionInterface**.)

This API is intended to make it easy / trivial to swap out different implementations of each of the Main classes.
For example, an application may have been written to use a package that implements this API in the following classes:
**ModelYY**, **CollectionYY** (which implements **CollectionInterface**) and **RecordYY** (which implements **RecordInterface**).
In the future if another package implementing this API has a **ModelZZ** which performs some operations more
efficiently than **ModelYY**, you should be able to easily substitute **ModelYY** with **ModelZZ** if all your
data access code strictly adheres to the GDAO API (you would now end up using **ModelZZ**, **CollectionYY** 
and **RecordYY** in your updated code; which should all work nicely together).

This package isn't meant to perform database schema management tasks like creating/altering tables, etc. However, it exposes a PDO object (via **\GDAO\Model->getPDO()**) that can be used to perform such tasks and other data manipulation (DM) tasks that cannot be accomplished via this API.


## Assumptions and Conventions in this API. 

* Each database table has a single auto-incrementing numeric primary key column (composite primary keys are not supported; however a single primary key column that is non-numeric should work)

* Implementation(s) of this API must be powered by at least one PDO object. Vendor specific php database extensions (eg. mysqli, SQLite3 etc.) are not to be used in implementation(s) of this API.

* This API is architected with the intent of having Records and Collections created via the Model.
> Users of any implementation of this API should not be directly instantiating new Collections or Records via their constructors, instead they should create them by calling the appropriate implementation of \GDAO\Model::createCollection(..) or \GDAO\Model::createRecord(..).

## Definition of terms used in the phpdoc comment blocks:
 
 * **Consumer:** an individual developer or group of developers that use a package / library containing concrete implementation of the APIs specified in the abstract classes in this package.
 
 * **Implementer:** The developer that creates a package / library containing concrete implementation of the APIs specified in the abstract classes in this package.

## Running Tests

  ` ./vendor/bin/phpunit --coverage-text`

## Dev Notes

* Old versions have branches corresponding to their version numbers (e.g. 1.X) while the most current / actively being developed version is on the master branch
