<?php
namespace GDAO\Model;

/**
 * 
 * This a record class that is lighter weight than \GDAO\Model\Record.
 * It does not have an _initial_data array for tracking changes made to a record
 * and it does not allow changing the values of a record's fields once the data
 * has been retreived from the database. It is intended for scenarios where you
 * are only reading data from the database for display purposes and do not plan
 * to update or delete the data. You cannot save new records to the database via
 * instances of this class.
 *
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
 * 
 */
abstract class ReadOnlyRecord implements RecordInterface
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
     * Holds relationship data retreieved based on definitions in the array below.
     * \GDAO\Model::$_relations
     *
     * @var array 
     */
    protected $_related_data = array();

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
	public function __construct(
        array $data, \GDAO\Model $model, array $extra_opts=array()
    ) {
        $this->setModel($model);
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
    
    protected function _throwNotSupportedException($function_name) {
        
        //Error trying to add a relation whose name collides with an actual
        //name of a column in the db table associated with this record's model.
        $msg = "ERROR: ". get_class($this) . '::' . $function_name . '(...)' 
             . " is not supported in a ReadOnly Model. ";

        throw new RecordOperationNotSupportedException($msg);
    }
    
    /**
     * 
     * Not Supported, not overridable.
     * 
     */
    public final function delete($set_record_objects_data_to_empty_array=false){
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
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
     * Not Supported, not overridable.
     * 
     */
    public final function getInitialData() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    
    /**
     * 
     * Get all the related data loaded into this record.
     * Modifying the returned data will not affect the related data inside this record.
     * 
     * @return array a reference to all the related data loaded into this record.
     */
    public function getRelatedData() {
        
        return $this->_related_data;
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
     * Not Supported, not overridable.
     * 
     */
    public final function &getInitialDataByRef() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * 
     * Get a reference to all the related data loaded into this record.
     * Modifying the returned data will affect the related data inside this record.
     * 
     * @return array a reference to all the related data loaded into this record.
     */
    public function &getRelatedDataByRef() {
        
        return $this->_related_data;
    }
    
    /**
     * 
     * Set relation data for this record.
     * 
     * @param string $key relation name
     * @param mixed $value an array or record or collection containing related data
     * 
     * @throws \GDAO\Model\RecordRelationWithSameNameAsAnExistingDBTableColumnNameException
     * 
     */
    public function setRelatedData($key, $value) {
        
        $my_model = $this->getModel();
        $table_cols = $my_model->getTableColNames();
        
        if( in_array($key, $table_cols) ) {
            
            //Error trying to add a relation whose name collides with an actual
            //name of a column in the db table associated with this record's model.
            $msg = "ERROR: You cannont add a relationship with the name '$key' "
                 . " to the record (".get_class($this)."). The database table "
                 . " '{$my_model->getTableName()}' associated with the "
                 . " record's model (".get_class($my_model).") already contains"
                 . " a column with the same name."
                 . PHP_EOL . get_class($this) . '::' . __FUNCTION__ . '(...).' 
                 . PHP_EOL;
                 
            throw new RecordRelationWithSameNameAsAnExistingDBTableColumnNameException($msg);
        }
        
        //We're safe, set the related data.
        $this->_related_data[$key] = $value;
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
    public function getPrimaryCol() {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }
    
    /**
     * 
     * @return mixed the value stored in the primary-key column for this record.
     */
    public function getPrimaryVal() {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }
    
    /**
     * 
     * Not Supported, not overridable.
     *  
     */
    public final function isChanged($col = null) {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * 
     * Not Supported, not overridable.
     * 
     */
    public final function isNew() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * 
     * This method partially or completely overwrites pre-existing data and 
     * replaces it with the new data.
     * 
     * Note if $cols_2_load === null all data should be replaced, else only
     * replace data for the cols in $cols_2_load.
     * 
     * If $data_2_load is an instance of \GDAO\Model\RecordInterface and is also an instance 
     * of a sub-class of the Record class in a package that implements this API and
     * if $data_2_load->getModel()->getTableName() !== $this->getModel()->getTableName(), 
     * then the exception below should be thrown:
     * 
     *      \GDAO\Model\LoadingDataFromInvalidSourceIntoRecordException
     * 
     * @param array $data_2_load
     * @param array $cols_2_load name of field to load from $data_2_load. If null, 
     *                           load all fields in $data_2_load.
     * 
     * @throws \GDAO\Model\LoadingDataFromInvalidSourceIntoRecordException
     * 
     */
    public function loadData(array $data_2_load, array $cols_2_load = array()) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }
    
    /**
     * 
     * Not Supported, not overridable.
     * 
     */
    public final function markAsNew() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * 
     * Not Supported, not overridable.
     * 
     */
    public final function markAsNotNew() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * 
     * Not Supported, not overridable.
     * 
     */
    public final function setStateToNew() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }

    /**
     * 
     * Not Supported, not overridable.
     * 
     */
    public final function save($data_2_save = null) {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * 
     * Not Supported, not overridable.
     * 
     */
    public final function saveInTransaction($data_2_save = null) {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
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
     * ArrayAccess: does the requested key exist?
     * 
     * @param string $key The requested key.
     * 
     * @return bool
     * 
     */
    public function offsetExists($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
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
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * ArrayAccess: set a key value.
     * 
     * @param string $key The requested key.
     * 
     * @param string $val The value to set it to.
     * 
     * @return void
     * 
     */
    public function offsetSet($key, $val) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * ArrayAccess: unset a key.
     * 
     * @param string $key The requested key.
     * 
     * @return void
     * 
     */
    public function offsetUnset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * Countable: how many keys are there?
     * 
     * @return int
     * 
     */
    public function count(){
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * 
     * @return \ArrayIterator
     * 
     */
    public function getIterator(){
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }
    
    //Magic Methods
    
    /**
     * 
     * Gets a data value.
     * 
     * @param string $key The requested data key.
     * 
     * @return mixed The data value.
     * 
     */
    public function __get($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * Does a certain key exist in the data?
     * 
     * Note that this is slightly different from normal PHP isset(); it will
     * say the key is set, even if the key value is null or otherwise empty.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __isset($key) {
        
        $msg = 'Must Implement '.get_class($this).'::'.__FUNCTION__.'(...)';
        throw new RecordMustImplementMethodException($msg);
    }

    /**
     * 
     * Sets a key value.
     * 
     * @param string $key The requested data key.
     * 
     * @param mixed $val The value to set the data to.
     * 
     * @return void
     * 
     */
    public final function __set($key, $val) {
        
        $this->_throwNotSupportedException(__FUNCTION__);
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

    /**
     * 
     * Removes a key and its value in the data.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public final function __unset($key) {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
}
