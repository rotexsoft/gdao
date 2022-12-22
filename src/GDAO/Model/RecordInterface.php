<?php
declare(strict_types=1);
namespace GDAO\Model;

/**
 * 
 * Contains a list of methods that Record classes must implement.
 *
 * @copyright (c) 2022, Rotexsoft
 */
interface RecordInterface extends \ArrayAccess, \Countable, \IteratorAggregate 
{
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////
    //// RECOMMENDATIONS
    //// * Data for the record ([to be saved to the DB] or [as read from the DB]) 
    ////   should be stored in a property of the record class implementing this
    ////   interface. It could be an array, ArrayObject or any other suitable 
    ////   data structure. Data from this property should be returned via 
    ////   $this->getData() & $this->getDataByRef(). Data contained in this 
    ////   property should be set via 
    ////   $this->loadData($data_2_load, array $cols_2_load = array()).
    ////   For documentation purposes this property will be refered to as
    ////   $this->data (with an assumption that it is an array).
    ////   
    //// * Another property of the same data type as the one described above should
    ////   be present in the class implementing this interface. This property should 
    ////   only be set the first time record data is fetched from the DB (it holds
    ////   a copy of the data initially loaded into the record from the DB). This 
    ////   property's values should be compared with the values of the property 
    ////   described above in order to be able to determine if the record's data 
    ////   has changed (this should be implemented in $this->isChanged($col=null)). 
    ////   Data from this property should be returned via $this->getInitialData() 
    ////   & $this->getInitialDataByRef(). Data contained in this property should 
    ////   be set ONCE via $this->loadData($data_2_load, array $cols_2_load = array())
    ////   the first time loadData is called on a record.
    ////   For documentation purposes this property will be refered to as
    ////   $this->initial_data (with an assumption that it is an array).
    ////   
    //// * Another property should be present in the class implementing this 
    ////   interface. This property should hold data related to a record (ie.
    ////   has-many, has-one, belongs-to and has-many-through relationship data).
    ////   Data from this property should be returned via $this->getRelatedData()
    ////   & $this->getRelatedDataByRef(). Data contained in this property should 
    ////   be set via $this->setRelatedData($key, $value).
    ////   For documentation purposes this property will be refered to as
    ////   $this->related_data (with an assumption that it is an array).
    ////   
    //// * Another property should be present in the class implementing this 
    ////   interface. This property should hold other data for a record instance 
    ////   (i.e. data not from any actual db column and not related data).
    ////   Data from this property should be returned via $this->getNonTableColAndNonRelatedData()
    ////   & $this->getNonTableColAndNonRelatedDataByRef().
    ////   For documentation purposes this property will be refered to as
    ////   $this->non_table_col_and_non_related_data (with an assumption that it is an array).
    ////   
    //// * A boolean property should be present in the class implementing this 
    ////   interface. This property should be set to true if a record is new
    ////   (ie. its data has never been saved to the DB), else false.
    ////   For documentation purposes this property will be refered to as
    ////   $this->is_new.
    ////   
    //// * A property of type \GDAO\Model should be present in a class implementing
    ////   this interface. This is the model object that will perform database 
    ////   operations on behalf of the record.
    ////   For documentation purposes this property will be refered to as
    ////   $this->model
    ////   
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////    
    
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
     */
    public function __construct(array $data, \GDAO\Model $model);
	
    /**
     * 
     * Delete the record from the db. 
     * 
     * If deletion was successful and the primary key column for the record's db
     * table is auto-incrementing, then unset the primary key field in the data 
     * contained in the record object.
     * 
     * NOTE: data contained in the record include $this->data, $this->related_data
     *       and $this->initial_data.
     * 
     * @param bool $set_record_objects_data_to_empty_array true to reset the record object's data to an empty array if db deletion was successful, false to keep record object's data
     * 
     * @return bool true if record was successfully deleted from db or false if not
     * 
     */
    public function delete($set_record_objects_data_to_empty_array=false): bool;
    
    /**
     * 
     * Get the data for this record.
     * Modifying the returned data will not affect the data inside this record.
     * 
     * @return array a copy of the current data for this record
     */
    public function getData(): array;
    
    /**
     * 
     * Get a copy of the initial data loaded into this record.
     * Modifying the returned data will not affect the initial data inside this record.
     * 
     * @return array a copy of the initial data loaded into this record.
     */
    public function getInitialData(): array;
    
    
    /**
     * 
     * Get all the related data loaded into this record.
     * Modifying the returned data will not affect the related data inside this record.
     * 
     * @return array a reference to all the related data loaded into this record.
     */
    public function getRelatedData(): array;
    
    /**
     * 
     * Get data for this record that does not belong to any of it's table columns and is not related data.
     * 
     * @return array Data for this record (not to be saved to the db i.e. not from any actual db column and not related data).
     */
    public function getNonTableColAndNonRelatedData(): array;
    
    /**
     * 
     * Get a reference to the data for this record.
     * Modifying the returned data will affect the data inside this record.
     * 
     * @return null|array a reference to the current data for this record.
     */
    public function &getDataByRef(): ?array;
    
    /**
     * 
     * Get a reference to the initial data loaded into this record.
     * Modifying the returned data will affect the initial data inside this record.
     * 
     * @return null|array a reference to the initial data loaded into this record.
     */
    public function &getInitialDataByRef(): ?array;
    
    /**
     * 
     * Get a reference to all the related data loaded into this record.
     * Modifying the returned data will affect the related data inside this record.
     * 
     * @return array a reference to all the related data loaded into this record.
     */
    public function &getRelatedDataByRef(): array;
    
    /**
     * 
     * Get data for this record that does not belong to any of it's table columns and is not related data.
     * 
     * @return array reference to the data for this record (not from any actual db column and not related data).
     */
    public function &getNonTableColAndNonRelatedDataByRef(): array;
    
    /**
     * 
     * Set relation data for this record.
     * 
     * @param string $key relation name
     * @param mixed $value an array or record or collection containing related data
     * 
     * @throws \GDAO\Model\RecordRelationWithSameNameAsAnExistingDBTableColumnNameException
     * 
     * @return $this
     */
    public function setRelatedData($key, $value): self;
    
    /**
     * 
     * Get the model object that saves and reads data to and from the db on 
     * behalf of this record
     */
    public function getModel(): \GDAO\Model;
    
    /**
     * 
     * @return string name of the primary-key column of the db table this record belongs to
     */
    public function getPrimaryCol(): string;
    
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
     * @return null|bool Returns null if the table-column name does not exist,
     * boolean true if the data is changed, boolean false if not changed.
     *  
     */
    public function isChanged($col = null): ?bool;
    
    /**
     * 
     * Is the record new? (I.e. its data has never been saved to the db)
     * 
     */
    public function isNew(): bool;
    
    /**
     * 
     * This method partially or completely overwrites pre-existing data for a 
     * record and replaces it with the new data (this does not include related
     * data). If no data has previously been loaded into the record, keep a copy
     * of the loaded data for comparison in $this->isChanged($col=null)).
     * 
     * Note if $cols_2_load === null all data should be replaced, else only
     * replace data for the cols in $cols_2_load.
     * 
     * If $data_2_load is an instance of \GDAO\Model\RecordInterface and if 
     * $data_2_load->getModel()->getTableName() !== $this->getModel()->getTableName(), 
     * then the exception below should be thrown:
     * 
     *      \GDAO\Model\LoadingDataFromInvalidSourceIntoRecordException
     * 
     * @param \GDAO\Model\RecordInterface|array $data_2_load
     * @param array $cols_2_load name of field to load from $data_2_load. 
     *                           If empty, load all fields in $data_2_load.
     * 
     * @throws \GDAO\Model\LoadingDataFromInvalidSourceIntoRecordException
     * 
     * @return $this
     */
    public function loadData($data_2_load, array $cols_2_load = []): self;
    
    /**
     * 
     * Set the _is_new attribute of this record to true (meaning that the data
     * for this record has never been saved to the db).
     * 
     * @return $this
     */
    public function markAsNew(): self;
    
    /**
     * 
     * Set the _is_new attribute of this record to false (meaning that the data
     * for this record has been saved to the db or was read from the db).
     * 
     * @return $this
     */
    public function markAsNotNew(): self;
    
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
     * 
     * @return $this
     */
    public function setStateToNew(): self;

    /**
     * 
     * Save the specified or already existing data for this record to the db.
     * Since this record can only talk to the db via its model property (_model)
     * the save operation will actually be done via $this->model.
     * 
     * @param \GDAO\Model\RecordInterface|array $data_2_save
     * 
     * @return null|bool true: successful save, false: failed save, null: no changed data to save
     * 
     */
    public function save($data_2_save = null): ?bool;
    
    /**
     * 
     * Save the specified or already existing data for this record to the db.
     * Since this record can only talk to the db via its model property (_model)
     * the save operation will actually be done via $this->model.
     * This save operation shoould be gaurded by the PDO transaction mechanism
     * if available or another transaction mechanism. If the save operation 
     * fails all changes should be rolled back. If there is not transaction
     * mechanism available an Exception must be thrown alerting the caller to
     * use the save method instead.
     * 
     * @param \GDAO\Model\RecordInterface|array $data_2_save
     * 
     * @return bool|null true for a successful save, false for failed save, null: no changed data to save
     * 
     */
    public function saveInTransaction($data_2_save = null): ?bool;
    
    /**
     * 
     * Set the \GDAO\Model object for this record
     * 
     * @param \GDAO\Model $model
     * 
     * @return $this
     */
    public function setModel(\GDAO\Model $model): self;
    
    /**
     * 
     * Get all the data and property (name & value pairs) for this record.
     * 
     * @return array of all data & property (name & value pairs) for this record.
     * 
     */
    public function toArray(): array;
    
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
     */
    public function __isset($key): bool;

    /**
     * 
     * Sets a key value.
     * 
     * @param string $key The requested data key.
     * 
     * @param mixed $val The value to set the data to.
     * 
     */
    public function __set($key, $val): void;

    /**
     * 
     * Get the string representation of all the data and property 
     * (name & value pairs) for this record.
     * 
     * @return string string representation of all the data & property 
     *                (name & value pairs) for this record.
     * 
     */
    public function __toString(): string;

    /**
     * 
     * Removes a key and its value in the data.
     * 
     * @param string $key The requested data key.
     * 
     */
    public function __unset($key): void;
}
