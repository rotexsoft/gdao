* Strive for 100% Unit Test Coverage

* Look into the possibility of refactoring the Record class to have connected and disconnected records
> Connected records will contain a reference to the Model object that created them while disconnected records will have no reference to the model that created them 
> (they can be used separately (by supplying an array of data to their constructor), no need for a Model or Collection class).

* Re-implement \Collections\Collection used inside GDAORecordsList in order to sever dependency on danielgsims/php-collections

* Write an alternative implementation of \GDAO\Model\Collection using SplFixedArray instead of a plain old php array (SplFixedArray seems to be more memory efficient than php arrays). 
> in loadData(..) and __construct(..) add this line   
> $this->_data = \SplFixedArray::fromArray( $data->toArray() );   
> where $data is an instance of \GDAO\Model\GDAORecordsList expected as the first parameter to loadData(..) and __construct(..) 

* Define the $params array structure for each of the fetch*() methods in the Model class.

* Make API usable with tables that do not have a primary key column defined.
> Remove the phrase **Working on supporting tables that do not have any primary key column defined** from **README.md** once the above task is completed   
> Remove a similar variant of the earlier mentioned phrase from the doc-block of **\GDAO\Model->_primary_col**   
> Change the default value of **\GDAO\Model->_primary_col** to null in its declaration      
> Also change the phrase **This is a REQUIRED field & must be properly set by 
> consumers of this class** in the doc-block of **\GDAO\Model->_primary_col** to
> ***This is an OPTIONAL field & must be properly set by consumers of this class**

