<?php
namespace GDAO\Model;

/**
 * 
 * Contains a list of methods that Record classes must implement.
 *
 * @author aadegbam
 */
interface RecordInterface extends \ArrayAccess, \Countable, \IteratorAggregate 
{
    /**
     * 
     * @param array $data associative array of data to be loaded into this record.
     *                    [
     *                      'col_name1'=>'value_for_col1', 
     *                      .............................,
     *                      .............................,
     *                      'col_nameN'=>'value_for_colN'
     *                    ]
     * @param \GDAO\Model $model The model object that transfers data between the db and this record.
     * @param array $extra_opts an array that may be used to pass initialization 
     *                          value(s) for protected and / or private properties
     *                          of this class
     */
	public function __construct(
        array $data, \GDAO\Model $model, array $extra_opts=array()
    );
	
    /**
     * 
     * Delete the record from the db. 
     * 
     * If deletion was successful and the primary key column for the record's db
     * table is auto-incrementing, then unset the primary key field in the data 
     * contained in the record object.
     * 
     * NOTE: data contained in the record include $this->_data, $this->_related_data
     *       and $this->_initial_data.
     * 
     * @param bool $set_record_objects_data_to_empty_array true to reset the record object's data to an empty array if db deletion was successful, false to keep record object's data
     * 
     * @return bool true if record was successfully deleted from db or false if not
     * 
     */
    public function delete($set_record_objects_data_to_empty_array=false);
    
    /**
     * 
     * Get the data for this record.
     * Modifying the returned data will not affect the data inside this record.
     * 
     * @return array a copy of the current data for this record
     */
    public function getData();
    
    /**
     * 
     * Get a copy of the initial data loaded into this record.
     * Modifying the returned data will not affect the initial data inside this record.
     * 
     * @return array a copy of the initial data loaded into this record.
     */
    public function getInitialData();
    
    
    /**
     * 
     * Get all the related data loaded into this record.
     * Modifying the returned data will not affect the related data inside this record.
     * 
     * @return array a reference to all the related data loaded into this record.
     */
    public function getRelatedData();
    
    /**
     * 
     * Get a reference to the data for this record.
     * Modifying the returned data will affect the data inside this record.
     * 
     * @return array a reference to the current data for this record.
     */
    public function &getDataByRef();
    
    /**
     * 
     * Get a reference to the initial data loaded into this record.
     * Modifying the returned data will affect the initial data inside this record.
     * 
     * @return array a reference to the initial data loaded into this record.
     */
    public function &getInitialDataByRef();
    
    /**
     * 
     * Get a reference to all the related data loaded into this record.
     * Modifying the returned data will affect the related data inside this record.
     * 
     * @return array a reference to all the related data loaded into this record.
     */
    public function &getRelatedDataByRef();
    
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
    public function setRelatedData($key, $value);
    
    /**
     * 
     * Get the model object that saves and reads data to and from the db on 
     * behalf of this record
     * 
     * @return \GDAO\Model
     */
	public function getModel();
    
    /**
     * 
     * @return string name of the primary-key column of the db table this record belongs to
     */
	public function getPrimaryCol();
    
    /**
     * 
     * @return mixed the value stored in the primary-key column for this record.
     */
	public function getPrimaryVal();
    
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
	public function isChanged($col = null);
    
    /**
     * 
     * Is the record new? (I.e. its data has never been saved to the db)
     * 
     * @return bool
     */
	public function isNew();
    
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
	public function loadData($data_2_load, array $cols_2_load = array());
    
    /**
     * 
     * Set the _is_new attribute of this record to true (meaning that the data
     * for this record has never been saved to the db).
     * 
     */
    public function markAsNew();
    
    /**
     * 
     * Set the _is_new attribute of this record to false (meaning that the data
     * for this record has been saved to the db or was read from the db).
     * 
     */
    public function markAsNotNew();
    
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
    public function setStateToNew();

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
	public function save($data_2_save = null);
    
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
	public function saveInTransaction($data_2_save = null);
    
    /**
     * 
     * Set the \GDAO\Model object for this record
     * 
     * @param \GDAO\Model $model
     */
	public function setModel(\GDAO\Model $model);
    
    /**
     * 
     * Get all the data and property (name & value pairs) for this record.
     * 
     * @return array of all data & property (name & value pairs) for this record.
     * 
     */
    public function toArray();
    
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
    public function __get($key);

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
    public function __isset($key);

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
    public function __set($key, $val);

    /**
     * 
     * Get the string representation of all the data and property 
     * (name & value pairs) for this record.
     * 
     * @return string string representation of all the data & property 
     *                (name & value pairs) for this record.
     * 
     */
    public function __toString();

    /**
     * 
     * Removes a key and its value in the data.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __unset($key);
}

class RecordMustImplementMethodException extends \Exception{}
class RecordOperationNotSupportedException extends \Exception{}
class LoadingDataFromInvalidSourceIntoRecordException extends \Exception{}
class RecordRelationWithSameNameAsAnExistingDBTableColumnNameException extends \Exception{}