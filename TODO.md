* Add Unit Tests (using a mock implementation with mock objects)

* Look into the possibility of refactoring the Record class to have connected and disconnected records
> Connected records will contain a reference to the Model object that created them while disconnected records will have nor reference to the model that created them.

* Re-arrange composer dependencies (only composer package needed in GDAO is danielgsims/php-collections )

* Re-implement \Collections\Collection used inside GDAORecordsList in order to sever dependency on danielgsims/php-collections

* Add Model class name as part of the entries to be supplied in the relationship definition arrays

* Make API usable with tables that do not have a primary key column defined.
> Remove the phrase **Working on supporting tables that do not have any primary key column defined** from **README.md** once the above task is completed

> Also remove the earlier mentioned phrase from the doc-block of \GDAO\Model->_primary_col
