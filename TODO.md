* Add Unit Tests (using a mock implementation with mock objects)

* Look into the possibility of refactoring the Record class to have connected and disconnected records
> Connected records will contain a reference to the Model object that created them while disconnected records will have nor reference to the model that created them.

* Re-arrange composer dependencies (only composer package needed in GDAO is danielgsims/php-collections )

* Re-implement \Collections\Collection used inside GDAORecordsList in order to sever dependency on danielgsims/php-collections