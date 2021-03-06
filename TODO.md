* ~~Strive for 100% Unit Test Coverage~~ (Accomplished!)

* Make API usable with tables that do not have a primary key column defined.
> Remove the phrase **Working on supporting tables that do not have any primary key column defined** from **README.md** once the above task is completed   
> Remove a similar variant of the earlier mentioned phrase from the doc-block of **\GDAO\Model->_primary_col**   
> Change the default value of **\GDAO\Model->_primary_col** to null in its declaration      
> Also change the phrase **This is a REQUIRED field & must be properly set by 
> consumers of this class** in the doc-block of **\GDAO\Model->_primary_col** to
> ***This is an OPTIONAL field & must be properly set by consumers of this class**

* Relax requirement that PDO be used to power implementation(s) of this API and allow for usage of vendor specific php database extensions (eg. mysqli, SQLite3 etc.) in the implementation(s) of this API.
> These vendor specific php database extensions may be more performant than their PDO counterparts. (UPDATE THE **Assumptions and Conventions in this API** SECTION IN **README.md** WHEN THIS IS DONE).

* Lower testing requirement to allow for PHP 5.3 (the only downside is the loss of the convenient use of the short array syntax)
> This is to make sure this package really works for PHP 5.3, since the short array syntax is only used in test files and not in the actual source (src) files

* Find a way using git hooks or something to update the year in the license during a commit, push or something.

* The concept of a **DisconnectedRecordInterface** and a **DisconnectedCollectionInterface** that will be similar to the existing **RecordInterface** and a **CollectionInterface** but without the need / reference to a **Model** object (these new interfaces will not contain methods that perform database operations such as **save**, **delete**, etc.). Instances of **DisconnectedRecordInterface** and **DisconnectedCollectionInterface** will exist without any reference to a **Model** object. They could be used with non-sql data sources.

  > A good approach will be to define methods common to  **DisconnectedRecordInterface** and  **RecordInterface** inside of  **DisconnectedRecordInterface** and make **RecordInterface** extend **DisconnectedRecordInterface** (in this case **RecordInterface** will only contain additional method signatures that require a **Model** object and / or perform operations on the database)