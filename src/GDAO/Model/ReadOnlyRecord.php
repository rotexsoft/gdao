<?php
namespace GDAO\Model;

/**
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
 */
abstract class ReadOnlyRecord implements \ArrayAccess, \Countable, \IteratorAggregate
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
     * Holds relationship data retreieved based on definitions in the array below.
     * \GDAO\Model::$_relations
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
     * Get a copy of the initial data loaded into this record.
     * Modifying the returned data will not affect the initial data inside this record.
     * 
     * @return array a copy of the initial data loaded into this record.
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
     * Get a reference to the initial data loaded into this record.
     * Modifying the returned data will affect the initial data inside this record.
     * 
     * @return array a reference to the initial data loaded into this record.
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
    public final function isChanged($col = null) {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * 
     * Is the record new? (I.e. its data has never been saved to the db)
     * 
     * @return bool
     */
    public final function isNew() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * \GDAO\Model\Record::$_initial_data should be set here only if it has the 
     * initial value of -1.
     * 
     * This method partially or completely overwrites pre-existing data and 
     * replaces it with the new data. Related data should also be loaded if 
     * $data_2_load is an instance of \GDAO\Model\Record. 
     * 
     * Note if $cols_2_load === null all data should be replaced, else only
     * replace data for the cols in $cols_2_load.
     * 
     * If $data_2_load is an instance of \GDAO\Model\Record and is also an instance 
     * of a sub-class of the Record class in a package that implements this API and
     * if $data_2_load->getModel()->getTableName() !== $this->getModel()->getTableName(), 
     * then the exception below should be thrown:
     * 
     *      \GDAO\Model\LoadingDataFromInvalidSourceIntoRecordException
     * 
     * @param \GDAO\Model\Record|array $data_2_load
     * @param array $cols_2_load name of field to load from $data_2_load. If null, 
     *                           load all fields in $data_2_load.
     * 
     * @throws \GDAO\Model\LoadingDataFromInvalidSourceIntoRecordException
     * 
     */
    public abstract function loadData($data_2_load, array $cols_2_load = array());
    
    /**
     * 
     * Set the _is_new attribute of this record to true (meaning that the data
     * for this record has never been saved to the db).
     * 
     */
    public final function markAsNew() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
    /**
     * 
     * Set the _is_new attribute of this record to false (meaning that the data
     * for this record has been saved to the db or was read from the db).
     * 
     */
    public final function markAsNotNew() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
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
    public final function setStateToNew() {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }

    /**
     * 
     * Save the specified or already existing data for this record to the db.
     * Since this record can only talk to the db via its model property (_model)
     * the save operation will actually be done via $this->_model.
     * 
     * @param \GDAO\Model\Record|array $data_2_save
     * 
     * @return null|bool true: successful save, false: failed save, null: no changed data to save
     * 
     */
    public final function save($data_2_save = null) {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
    
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

    public final function __set($key, $value) {
        
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

    public final function __unset($key) {
        
        $this->_throwNotSupportedException(__FUNCTION__);
    }
}

class RecordOperationNotSupportedException extends \Exception{}
