<?php
declare(strict_types = 1);

namespace GDAO\Model;

/**
 * 
 * Represents a collection of \GDAO\Model\RecordInterface objects.
 *
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2018, Rotimi Adegbamigbe
 * 
 */
interface CollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate {
    
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
     * @param \GDAO\Model\RecordInterface $records one or more record objects to be stored in the collection.
     */
    public function __construct(\GDAO\Model\RecordInterface ...$records);

    /**
     * 
     * Deletes each record in the collection from the database, but leaves the
     * record objects with their data inside the collection object.
     * 
     * Call $this->removeAll() to empty the collection of the record objects.
     * 
     * Records that could not be deleted, should be stored so that they can be returned 
     * by $this->getRecordsNotDeletedByLastDeleteAll(...).
     * If all records were successfully deleted the store should contain an empty
     * array so that $this->getRecordsNotDeletedByLastDeleteAll(...) returns an
     * empty collection.
     * 
     * @return bool|array true if all records were successfully deleted or false if not.
     * 
     * @see \GDAO\Model\CollectionInterface::getRecordsNotDeletedByLastDeleteAll($purge_records_for_next_call)
     * 
     * @throws \PDOException 
     * 
     */
    public function deleteAll(): bool;

    /**
     * 
     * Returns a collection of records that were not successfully deleted by the last call to $this->deleteAll()
     * 
     * @param bool $purge_records_for_next_call true if returned records should not be returned on subsequent calls to
     *                                          this method until $this->deleteAll() is called again which may lead to
     *                                          another batch of records that were not deleted via $this->deleteAll(),
     *                                          false otherwise (meaning this method will keep returning a collection
     *                                          of records that could not be deleted via the last call to $this->deleteAll()
     *                                          until $this->deleteAll() is called again).
     * 
     * @return \GDAO\Model\CollectionInterface a collection of records that could not be deleted by the last call to
     *                                         $this->deleteAll(). If last call to $this->deleteAll() did not fail,
     *                                         then an empty collection should be returned.
     * 
     * @see \GDAO\Model\CollectionInterface::deleteAll()
     */
    public function getRecordsNotDeletedByLastDeleteAll(bool $purge_records_for_next_call=false): \GDAO\Model\CollectionInterface;

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
    public function getColVals($col): array;

    /**
     * 
     * Returns all the keys for this collection.
     * 
     * @return array
     * 
     */
    public function getKeys(): array;

    /**
     * 
     * Returns the model from which the data originates.
     * 
     * @return \GDAO\Model The origin model object.
     * 
     */
    public function getModel(): \GDAO\Model;

    /**
     * 
     * Are there any records in the collection?
     * 
     * @return bool true if empty, false if not.
     * 
     */
    public function isEmpty(): bool;

    /**
     * 
     * Load the collection with record objects that will replace the records (if any ) that were in the collection. 
     * 
     * If an array of records is to be injected into this method, it must be done
     * via the argument unpacking technique.
     * 
     * 
     * @param \GDAO\Model\RecordInterface $records one or more record objects to be added to the collection
     * 
     * @return $this the collection records were added to.
     * 
     */
    public function loadData(\GDAO\Model\RecordInterface ...$records): self;

    /**
     * 
     * Removes all records from the collection but **does not** delete them
     * from the database.
     * 
     * @return $this the collection object all records were just removed from
     * 
     */
    public function removeAll(): self;

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
     * Records that could not be saved, should be stored so that they can be 
     * returned by $this->getRecordsNotSavedByLastSaveAll(...).
     * If all records were successfully saved the store should contain an empty
     * array so that $this->getRecordsNotSavedByLastSaveAll(...) returns an
     * empty collection.
     * 
     * @param bool $group_inserts_together true to group all records to be 
     *                                     inserted together in order to perform 
     *                                     a single sql insert operation, false
     *                                     to perform one-by-one inserts.
     * 
     * @return bool true if all inserts and updates were successful or false
     *              if one or more record(s) couldn't be successfully inserted 
     *              or updated. It's most likely a PDOException would be thrown 
     *              if an insert or update fails.
     * 
     * @see \GDAO\Model\CollectionInterface::getRecordsNotSavedByLastSaveAll($purge_records_for_next_call)
     * 
     * @throws \PDOException
     * 
     */
    public function saveAll($group_inserts_together = false);

    /**
     * 
     * Returns a collection of records that were not successfully saved by the last call to $this->saveAll()
     * 
     * @param bool $purge_records_for_next_call true if returned records should not be returned on subsequent calls to
     *                                          this method until $this->saveAll() is called again which may lead to
     *                                          another batch of records that were not saved via $this->saveAll(),
     *                                          false otherwise (meaning this method will keep returning a collection
     *                                          of records that could not be saved via the last call to $this->saveAll()
     *                                          until $this->saveAll() is called again).
     * 
     * @return \GDAO\Model\CollectionInterface a collection of records that could not be saved by the last call to
     *                                         $this->saveAll(). If last call to $this->saveAll() did not fail,
     *                                         then an empty collection should be returned.
     * 
     * @see \GDAO\Model\CollectionInterface::saveAll($group_inserts_together = false)
     */
    public function getRecordsNotSavedByLastSaveAll(bool $purge_records_for_next_call=false): \GDAO\Model\CollectionInterface;
    /**
     * 
     * Injects the model from which the data originates.
     * 
     * @param \GDAO\Model $model The origin model object.
     * 
     * @return $this collection object that model was just set on
     * 
     */
    public function setModel(\GDAO\Model $model): self;

    /**
     * 
     * Returns an array representation of an instance of this class.
     * 
     * @return array an array representation of an instance of this class.
     * 
     */
    public function toArray(): array;

    /**
     * 
     * Returns a record from the collection based on its key value.
     * 
     * A \GDAO\Model\ItemNotFoundInCollectionException must be thrown if no
     * record exists with the specified $key
     * 
     * @param int|string $key The sequential or associative key value for the
     *                        record.
     * 
     * @return \GDAO\Model\RecordInterface
     * 
     * @throws \GDAO\Model\ItemNotFoundInCollectionException
     * 
     */
    public function __get($key);

    /**
     * 
     * Does a certain key exist in the data?
     * 
     * @param int|string $key The requested data key.
     * 
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
     */
    public function __set($key, $val): void;

    /**
     * 
     * Returns a string representation of an instance of this class.
     * 
     * @return string a string representation of an instance of this class.
     * 
     */
    public function __toString(): string;

    /**
     * 
     * Removes a record with the specified key from the collection, if present.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __unset($key): void;

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
    public function _preDeleteAll(): void;

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
    public function _postDeleteAll(): void;

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
    public function _preSaveAll($group_inserts_together = false): void;

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
    public function _postSaveAll($save_all_result, $group_inserts_together = false): void;
}
