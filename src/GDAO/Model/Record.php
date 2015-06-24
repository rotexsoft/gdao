<?php
namespace GDAO\Model;

/**
 * Description of Record
 *
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
 */
abstract class Record implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * 
     * Data for this record ([to be saved to the db] or [as read from the db]).
     *
     * @var array 
     */
    protected $_data = array();
    
    /**
     *
     * Copy of the initial data loaded into this record.
     * 
     * @var array 
     */
    protected $_initial_data = -1;
    
    /**
     * 
     * Holds relationship data retreieved based on definitions in the arrays below.
     * \GDAO\Model::$_has_one_relationships
     * \GDAO\Model::$_has_many_relationships
     * \GDAO\Model::$_belongs_to_relationships
     * \GDAO\Model::$_has_many_through_relationships
     *
     * @var array 
     */
    protected $_related_data = array();
    
    /**
     * 
     * Tracks if *this record* is new (i.e., not in the database yet).
     *
     * @var bool 
     */
    protected $_is_new = true;

    /**
     *
     * The model object that saves and reads data to and from the db on behalf 
     * of this record
     * 
     * @var \GDAO\Model
     */
    protected $_model;


    /**
     * 
     * @param array $data associative array of data to be loaded into this record.
     *                    [
     *                      'col_name1'=>'value_for_col1', 
     *                      .............................,
     *                      .............................,
     *                      'col_nameN'=>'value_for_colN'
     *                    ]
     * 
     * @param array $extra_opts an array that may be used to pass initialization 
     *                          value(s) for protected and / or private properties
     *                          of this class
     */
	public function __construct(array $data=array(), array $extra_opts=array()) {
        
        $this->loadData($data);
        
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
     * delete the record from the db
     * 
     */
    public abstract function delete();
    
    /**
     * 
     * Get the data for this record.
     * Modifying the returned data will not affect the data inside this record.
     * 
     * @return array a copy of the current data for this record
     */
    public function getData() {
        
        return $this->_data;
    }
    
    /**
     * 
     * Get a copy of the initial data loaded into this record.
     * Modifying the returned data will not affect the initial data inside this record.
     * 
     * @return array a copy of the initial data loaded into this record.
     */
    public function getInitialData() {
        
        return $this->_initial_data;
    }
    
    /**
     * 
     * Get a reference to the data for this record.
     * Modifying the returned data will affect the data inside this record.
     * 
     * @return array a reference to the current data for this record.
     */
    public function &getDataByRef() {
        
        return $this->_data;
    }
    
    /**
     * 
     * Get a reference to the initial data loaded into this record.
     * Modifying the returned data will affect the initial data inside this record.
     * 
     * @return array a reference to the initial data loaded into this record.
     */
    public function &getInitialDataByRef() {
        
        return $this->_initial_data;
    }
    
    /**
     * 
     * Get the model object that saves and reads data to and from the db on 
     * behalf of this record
     * 
     * @return \GDAO\Model
     */
	public function getModel() {
        
        return $this->_model;
    }
    
    /**
     * 
     * @return string name of the primary-key column of the db table this record belongs to
     */
	public abstract function getPrimaryCol();
    
    /**
     * 
     * @return mixed the value stored in the primary-key column for this record.
     */
	public abstract function getPrimaryVal();
    
    /**
     * 
     * Tells if the record, or a particular table-column in the record, has 
     * changed from its initial value.
     * 
     * @param string $col The table-column name.
     * 
     * @return void|bool Returns null if the table-column name does not exist,
     * boolean true if the data is changed, boolean false if not changed.
     *  
     */
	public abstract function isChanged($col = null);
    
    /**
     * 
     * Is the record new? (I.e. its data has never been saved to the db)
     * 
     * @return bool
     */
	public function isNew() {
        
        return (bool) $this->_is_new;
    }
    
    /**
     * \GDAO\Model\Record::$_initial_data should be set here only if it has the 
     * initial value of -1.
     * 
     * This method wipes out pre-existing data and replaces it with the new data.
     * 
     * @param \GDAO\Model\Record|array $data_2_load
     * @param array $cols_2_load
     */
	public abstract function loadData($data_2_load, $cols_2_load = null);
    
    /**
     * 
     * Set the _is_new attribute of this record to true (meaning that the data
     * for this record has never been saved to the db).
     * 
     */
    public function markAsNew() {
        
        $this->_is_new = true;
    }
    
    /**
     * 
     * Set the _is_new attribute of this record to false (meaning that the data
     * for this record has been saved to the db or was read from the db).
     * 
     */
    public function markAsNotNew() {
        
        $this->_is_new = false;
    }
    
    /**
     * Set all properties of this record to the state they should be in for a new record.
     * For example:
     *  - unset its primary key value via unset($this[$this->getPrimaryCol()]);
     *  - call $this->markAsNew()
     *  - etc.
     * 
     * The _data & _initial_data properties can be updated as needed by the 
     * implementing sub-class. 
     * For example:
     *  - they could be left as is 
     *  - or the value of _data could be copied to _initial_data
     *  - or the value of _initial_data could be copied to _data
     *  - etc.
     */
    public abstract function setStateToNew();

        /**
     * 
     * Save the specified or already existing data for this record to the db.
     * Since this record can only talk to the db via its model property (_model)
     * the save operation will actually be done via $this->_model.
     * 
     * @param \GDAO\Model\Record|array $data_2_save
     * 
     * @return void|bool true: successful save, false: failed save, null: no changed data to save
     * 
     */
	public abstract function save($data_2_save = null);
    
    /**
     * 
     * Save the specified or already existing data for this record to the db.
     * Since this record can only talk to the db via its model property (_model)
     * the save operation will actually be done via $this->_model.
     * This save operation shoould be gaurded by the PDO transaction mechanism
     * if available or another transaction mechanism. If the save operation 
     * fails all changes should be rolled back. If there is not transaction
     * mechanism available an Exception must be thrown alerting the caller to
     * use the save method instead.
     * 
     * @param \GDAO\Model\Record|array $data_2_save
     * 
     * @return bool true for a successful save, false for failed save, null: no changed data to save
     * 
     */
	public abstract function saveInTransaction($data_2_save = null);
    
    /**
     * 
     * Set the \GDAO\Model object for this record
     * 
     * @param \GDAO\Model $model
     */
	public function setModel(\GDAO\Model $model){
        
        $this->_model = $model;
    }
    
    /**
     * 
     * Get all the data and property (name & value pairs) for this record.
     * 
     * @return array of all data & property (name & value pairs) for this record.
     * 
     */
    public function toArray() {

        return get_object_vars($this);
    }
    
    //Interface Methods
    
    /**
     * 
     * {@inheritDoc}
     * 
     */
    public function offsetExists($offset) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * {@inheritDoc}
     * 
     */
    public function offsetGet($offset) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * {@inheritDoc}
     * 
     */
    public function offsetSet($offset, $value) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * {@inheritDoc}
     * 
     */
    public function offsetUnset($offset) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * {@inheritDoc}
     * 
     */
    public function count(){
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * {@inheritDoc}
     * 
     */
    public function getIterator(){
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }
    
    //Magic Methods
    
    public function __get($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    public function __isset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    public function __set($key, $value) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * Get the string representation of all the data and property 
     * (name & value pairs) for this record.
     * 
     * @return string string representation of all the data & property 
     *                (name & value pairs) for this record.
     * 
     */
    public function __toString() {
        
        return print_r($this->toArray(), true);
    }

    public function __unset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }
}

class RecordMustImplementMethodException extends \Exception{}