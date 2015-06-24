<?php

namespace GDAO\Model;

/**
 * Description of Collection
 *
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
 */
abstract class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     *
     * @var \GDAO\Model
     */
    protected $_model;

    /**
     * 
     * @var array of \GDAO\Model\Record records
     */
    protected $_data = array();

    /**
     * 
     * \GDAO\Model\GDAORecordsList is only used to enforce strict typing.
     * I.e. all the records in the collection are of type \GDAO\Model\Record
     * or any of its sub-classes.
     * 
     * $this->_data should be assigned the value of 
     * \GDAO\Model\GDAORecordsList->toArray(). In this case $data->toArray().
     * 
     * @param \GDAO\Model\GDAORecordsList $data list of instances of \GDAO\Model\Record
     * @param array $extra_opts an array that may be used to pass initialization 
     *                          value(s) for protected and / or private properties
     *                          of this class
     * 
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
    
	public abstract function deleteAll();
    // Deletes each record in the collection one-by-one.
    
	public abstract function getColVals($col);
    // Returns an array of all values for a single column in the collection.
    
    /**
     * 
     * @return \GDAO\Model
     */
	public function getModel() {
        
        return $this->_model;
    }
    
	public abstract function isEmpty();
    // Are there any records in the collection?}
    
	public abstract function loadData(\GDAO\Model\GDAORecordsList $data_2_load, $cols_2_load = null);
    
	public abstract function removeAll();
    //Removes all records from the collection but **does not** delete them 
    //from the database.
    
	public abstract function save();
    //Saves all the records from this collection to the database one-by-one, 
    //inserting or updating as needed.
    
    /**
     * 
     * @param \GDAO\Model $model
     */
	public function setModel(\GDAO\Model $model) {
        
        $this->_model = $model;
    }
    
    public function toArray() {

        return get_object_vars($this);
    }
    
    //Interface Methods
    public function offsetExists($offset) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    public function offsetGet($offset) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    public function offsetSet($offset, $value) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    public function offsetUnset($offset) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    public function count() {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    public function getIterator() {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    //Magic Methods
    public function __get($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    public function __isset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    public function __set($key, $value) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }

    public function __toString() {
        
        return print_r($this->toArray(), true);
    }

    public function __unset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new CollectionMustImplementMethodException($msg);
    }
}

class CollectionMustImplementMethodException extends \Exception{}