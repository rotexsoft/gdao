<?php

namespace GDAO\Model;

/**
 * 
 * Represents a collection of \GDAO\Model\RecordInterface objects.
 *
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
 * 
 */
interface CollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////
    //// RECOMMENDATIONS
    //// * Data for the collection should be stored in a property of the collection 
    ////   class implementing this interface. It could be an array, ArrayObject, 
    ////   SPLFixedArray or any other suitable data structure.
    ////   
    //// * A property of type \GDAO\Model should be present in a class implementing
    ////   this interface. This is the model object that will perform database 
    //     operations on behalf of the collection.
    ////   
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////    
    
    /**
     * 
     * \GDAO\Model\RecordsList is only used to enforce strict typing.
     * Ie. all the records in the collection are of type \GDAO\Model\RecordInterface
     * or any of its sub-classes.
     * 
     * Implementers of this API do not have to store the collection's data in
     * a \GDAO\Model\RecordsList. They can use an array and just call
     * \GDAO\Model\RecordsList->toArray() to get at the underlying array
     * \GDAO\Model\RecordsList uses to store items.
     * 
     * @param \GDAO\Model\RecordsList $data list of instances of \GDAO\Model\RecordInterface
     * @param \GDAO\Model $model The model object that transfers data between the db and this collection.
     * @param array $extra_opts an array that may be used to pass initialization 
     *                          value(s) for protected and / or private properties
     *                          of this class
     */
    public function __construct(
        RecordsList $data, \GDAO\Model $model, array $extra_opts=array()
    );
    
    /**
     * 
     * Deletes each record in the collection from the database, but leaves the
     * record objects with their data inside the collection object.
     * 
     * Call $this->removeAll() to empty the collection of the record objects.
     * 
     * @return bool|array true if all records were successfully deleted or an
     *                    array of keys in the collection for the records that 
     *                    couldn't be successfully deleted. It's most likely a 
     *                    PDOException would be thrown if the deletion failed.
     * 
     * @throws \PDOException 
     * 
     */
    public function deleteAll();
    
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
    public function getColVals($col);
    
    /**
     * 
     * Returns all the keys for this collection.
     * 
     * @return array
     * 
     */
    public function getKeys();
    
    /**
     * 
     * Returns the model from which the data originates.
     * 
     * @return \GDAO\Model The origin model object.
     * 
     */
    public function getModel();
    
    /**
     * 
     * Are there any records in the collection?
     * 
     * @return bool True if empty, false if not.
     * 
     */
    public function isEmpty();
    
    /**
     * 
     * Load the collection with a list of records.
     * 
     * \GDAO\Model\RecordsList is used instead of an array because
     * \GDAO\Model\RecordsList can only contain instances of \GDAO\Model\RecordInterface
     * or its descendants. We only ever want instances of \GDAO\Model\RecordInterface or
     * its descendants inside a collection.
     * 
     * @param \GDAO\Model\RecordsList $data_2_load
     * 
     * @return void
     * 
     */
    public function loadData(RecordsList $data_2_load);
    
    
    /**
     * 
     * Removes all records from the collection but **does not** delete them
     * from the database.
     * 
     * @return void
     * 
     */
    public function removeAll();

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
    public function saveAll($group_inserts_together=false);
    
    /**
     * 
     * Injects the model from which the data originates.
     * 
     * @param \GDAO\Model $model The origin model object.
     * 
     * @return void
     * 
     */
    public function setModel(\GDAO\Model $model);
    
    /**
     * 
     * Returns an array representation of an instance of this class.
     * 
     * @return array an array representation of an instance of this class.
     * 
     */
    public function toArray();
    
    /**
     * 
     * Returns a record from the collection based on its key value.
     * 
     * @param int|string $key The sequential or associative key value for the
     *                        record.
     * 
     * @return \GDAO\Model\RecordInterface
     * 
     */
    public function __get($key);

    /**
     * 
     * Does a certain key exist in the data?
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __isset($key);

    /**
     * 
     * Set a key value.
     * 
     * @param string $key The requested key.
     * @param \GDAO\Model\RecordInterface $val The value to set it to.
     * 
     * @return void
     * 
     * 
     */
    public function __set($key, \GDAO\Model\RecordInterface $val);

    /**
     * 
     * ArrayAccess: set a key value; appends to the array when using []
     * notation.
     * 
     * NOTE: Implementers of this class must make sure that $val is an instance 
     *       of \GDAO\Model\RecordInterface else throw a 
     *       \GDAO\Model\CollectionCanOnlyContainGDAORecordsException exception.
     * 
     * @param string $key The requested key.
     * 
     * @param \GDAO\Model\RecordInterface $val The value to set it to.
     * 
     * @return void
     * 
     * @throws \GDAO\Model\CollectionCanOnlyContainGDAORecordsException
     * 
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $val);
    
    /**
     * 
     * Returns a string representation of an instance of this class.
     * 
     * @return array a string representation of an instance of this class.
     * 
     */
    public function __toString();

    /**
     * 
     * Removes a record with the specified key from the collection.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __unset($key);
    
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
    public function _preDeleteAll();
    
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
    public function _postDeleteAll();
    
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
    public function _preSaveAll($group_inserts_together=false);
    
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
    public function _postSaveAll($save_all_result, $group_inserts_together=false);
}

