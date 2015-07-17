<?php

namespace GDAO\Model;

/**
 * 
 * Represents a collection of \GDAO\Model\Record objects.
 *
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
 * 
 */
abstract class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     *
     * @var \GDAO\Model
     * 
     */
    protected $_model;

    /**
     * 
     * @var array of \GDAO\Model\Record records
     * 
     */
    protected $_data = array();

    /**
     * 
     * \GDAO\Model\GDAORecordsList is only used to enforce strict typing.
     * Ie. all the records in the collection are of type \GDAO\Model\Record
     * or any of its sub-classes.
     * 
     * $this->_data should be assigned the value of 
     * \GDAO\Model\GDAORecordsList->toArray(). In this case $data->toArray().
     * 
     * @param \GDAO\Model\GDAORecordsList $data list of instances of \GDAO\Model\Record
     * @param array $extra_opts an array that may be used to pass initialization 
     *                          value(s) for protected and / or private properties
     *                          of this class
     */
	public function __construct(\GDAO\Model\GDAORecordsList $data, array $extra_opts=array()) {
        
        $this->_data = $data->toArray();
        
        if(count($extra_opts) > 0) {
            
            //set properties of this class specified in $extra_opts
            foreach($extra_opts as $e_opt_key => $e_opt_val) {
  
                if ( property_exists($this, $e_opt_key) ) {
                    
                    $this->$e_opt_key = $e_opt_val;

                } elseif ( property_exists($this, '_'.$e_opt_key) ) {

                    $this->{"_$e_opt_key"} = $e_opt_val;
                }
            }
        }
    }
    
    /**
     * 
     * Deletes each record in the collection from the database, but leaves the
     * record objects with their data inside the collection object.
     * 
     * Call $this->removeAll() to empty the collection of the records.
     * 
     * @return void
     * 
     */
	public abstract function deleteAll();
    
    /**
     * 
     * Returns an array of all values for a single column in the collection.
     *
     * @param string $col The column name to retrieve values for.
     *
     * @return array An array of key-value pairs where the key is the collection 
     *               element key, and the value is the column value for that
     *               element.
     * 
     */
	public abstract function getColVals($col);
    
    /**
     * 
     * Returns all the keys for this collection.
     * 
     * @return array
     * 
     */
    public abstract function getKeys();
    
    /**
     * 
     * Returns the model from which the data originates.
     * 
     * @return \GDAO\Model The origin model object.
     * 
     */
	public function getModel() {
        
        return $this->_model;
    }
    
    /**
     * 
     * Are there any records in the collection?
     * 
     * @return bool True if empty, false if not.
     * 
     */
	public abstract function isEmpty();
    
    /**
     * 
     * Load the collection with a list of records.
     * 
     * \GDAO\Model\GDAORecordsList is used instead of an array because
     * \GDAO\Model\GDAORecordsList can only contain instances of \GDAO\Model\Record
     * or its descendants. We only ever want instances of \GDAO\Model\Record or
     * its descendants inside a collection.
     * 
     * @param \GDAO\Model\GDAORecordsList $data_2_load
     * 
     * @return void
     * 
     */
	public abstract function loadData(\GDAO\Model\GDAORecordsList $data_2_load);
    
    /**
     * 
     * Removes all records from the collection but **does not** delete them
     * from the database.
     * 
     * @return void
     * 
     */
	public abstract function removeAll();

    /**
     * 
     * Saves all the records from this collection to the database one-by-one,
     * inserting or updating as needed. 
     * 
     * For better performance, it can gather all records for inserts together
     * and then perform a single insert of multiple rows with one sql operation.
     * 
     * Updates cannot be batched together (they must be performed one-by-one) 
     * because there seems to be no neat update equivalent for bulk inserts:
     * 
     * example bulk insert:
     * 
     *      INSERT INTO mytable
     *                 (id, title)
     *          VALUES ('1', 'Lord of the Rings'),
     *                 ('2', 'Harry Potter');
     * 
     * @param bool $group_inserts_together true to group all records to be 
     *                                     inserted together in order to perform 
     *                                     a single sql insert operation, false
     *                                     to perform one-by-one inserts.
     * 
     * @return bool|array true if all inserts and updates were successful or
     *                    return an array of keys in the collection for the 
     *                    records that couldn't be successfully inserted or
     *                    updated. It's most likely a PDOException would be
     *                    thrown if an insert or update fails.
     * 
     * @throws \PDOException
     * 
     */
	public abstract function save($group_inserts_together=false);
    
    /**
     * 
     * Injects the model from which the data originates.
     * 
     * @param \GDAO\Model $model The origin model object.
     * 
     * @return void
     * 
     */
	public function setModel(\GDAO\Model $model) {
        
        $this->_model = $model;
    }
    
    /**
     * 
     * Returns an array representation of an instance of this class.
     * 
     * @return array an array representation of an instance of this class.
     * 
     */
    public function toArray() {

        return get_object_vars($this);
    }
    
    /////////////////////
    // Interface Methods
    /////////////////////
    
    /**
     * 
     * ArrayAccess: does the requested key exist?
     * 
     * @param string $key The requested key.
     * 
     * @return bool
     * 
     */
    public function offsetExists($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /**
     * 
     * ArrayAccess: get a key value.
     * 
     * @param string $key The requested key.
     * 
     * @return mixed
     * 
     */
    public function offsetGet($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /**
     * 
     * ArrayAccess: set a key value; appends to the array when using []
     * notation.
     * 
     * NOTE: Implementers of this class must make sure that $val is an instance 
     *       of \GDAO\Model\Record else throw a 
     *       \GDAO\Model\CollectionCanOnlyContainGDAORecordsException exception.
     * 
     * @param string $key The requested key.
     * 
     * @param \GDAO\Model\Record $val The value to set it to.
     * 
     * @return void
     * 
     * @throws \GDAO\Model\CollectionCanOnlyContainGDAORecordsException
     * 
     */
    public function offsetSet($key, $val) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /**
     * 
     * ArrayAccess: unset a key. 
     * Removes a record with the specified key from the collection.
     * 
     * @param string $key The requested key.
     * 
     * @return void
     * 
     */
    public function offsetUnset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /**
     * 
     * Countable: how many keys are there?
     * 
     * @return int
     * 
     */
    public function count() {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /**
     * 
     * IteratorAggregate: returns an external iterator for this collection.
     * 
     * @return \Iterator an Iterator eg. an instance of \ArrayIterator
     * 
     */
    public function getIterator() {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /////////////////////
    // Magic Methods
    /////////////////////
    
    /**
     * 
     * Returns a record from the collection based on its key value.
     * 
     * @param int|string $key The sequential or associative key value for the
     *                        record.
     * 
     * @return \GDAO\Model\Record
     * 
     */
    public function __get($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /**
     * 
     * Does a certain key exist in the data?
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __isset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /**
     * 
     * Set a key value.
     * 
     * NOTE: Implementers of this class must make sure that $val is an instance 
     *       of \GDAO\Model\Record else throw a 
     *       \GDAO\Model\CollectionCanOnlyContainGDAORecordsException exception.
     * 
     * @param string $key The requested key.
     * 
     * @param \GDAO\Model\Record $val The value to set it to.
     * 
     * @return void
     * 
     * @throws \GDAO\Model\CollectionCanOnlyContainGDAORecordsException
     * 
     */
    public function __set($key, $val) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    /**
     * 
     * Returns a string representation of an instance of this class.
     * 
     * @return array a string representation of an instance of this class.
     * 
     */
    public function __toString() {
        
        return print_r($this->toArray(), true);
    }

    /**
     * 
     * Removes a record with the specified key from the collection.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __unset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }
    
    //Hooks
    
    /**
     * 
     * User-defined pre-delete logic.
     * 
     * Implementers of this class should add a call to this method as the 
     * first line of code in their implementation of $this->deleteAll()
     * 
     * @return void
     * 
     */
    protected function _preDeleteAll()
    {
    }
    
    /**
     * 
     * User-defined post-delete logic.
     * 
     * Implementers of this class should add a call to this method as the 
     * last line of code in their implementation of $this->deleteAll()
     * 
     * @return void
     * 
     */
    protected function _postDeleteAll()
    {
    }
    
    /**
     * 
     * User-defined pre-save logic for the collection.
     * 
     * Implementers of this class should add a call to this method as the 
     * first line of code in their implementation of $this->save(...)
     * 
     * @return void
     * 
     */
    protected function _preSave()
    {
    }
    
    /**
     * 
     * User-defined post-save logic for the collection.
     * 
     * Implementers of this class should add a call to this method as the 
     * last line of code in their implementation of $this->save(...)
     * 
     * @return void
     * 
     */
    protected function _postSave()
    {
    }
}

class CollectionMustImplementMethodException extends \Exception{}
class CollectionCanOnlyContainGDAORecordsException extends \Exception{}