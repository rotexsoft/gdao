<?php
declare(strict_types=1);
namespace GDAO\Model;

/**
 * @psalm-suppress MissingTemplateParam
 * 
 * Represents a collection of \GDAO\Model\RecordInterface objects.
 *
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2023, Rotexsoft
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
     * @param \GDAO\Model $model The model object that transfers data between the db and this collection.
     * @param \GDAO\Model\RecordInterface[] $data instances of \GDAO\Model\RecordInterface
     */
    public function __construct(
        \GDAO\Model $model, \GDAO\Model\RecordInterface ...$data
    );
    
    /**
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
     */
    public function deleteAll(): bool|array;
    
    /**
     * Returns an array of all values for a single column in the collection.
     *
     * @param string $col The column name to retrieve values for.
     *
     * @return array An array of key-value pairs where the key is the collection 
     *               element key, and the value is the column value for that
     *               element.
     */
    public function getColVals($col): array;
    
    /**
     * Returns all the keys for this collection.
     * 
     * @return array<string|int, string|int>
     */
    public function getKeys(): array;
    
    /**
     * Returns the model from which the data originates.
     * 
     * @return \GDAO\Model The origin model object.
     */
    public function getModel(): \GDAO\Model;
    
    /**
     * Are there any records in the collection?
     * 
     * @return bool True if empty, false if not.
     */
    public function isEmpty(): bool;
    
    /**
     * Load the collection with one or more records.
     * 
     * @param \GDAO\Model\RecordInterface[] $data_2_load
     */
    public function loadData(\GDAO\Model\RecordInterface ...$data_2_load): static;
    
    /**
     * Removes all records from the collection but **does not** delete them
     * from the database.
     */
    public function removeAll(): static;

    /**
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
     *          VALUES ('1', 'Lord of the Rings'), ('2', 'Harry Potter');
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
     */
    public function saveAll($group_inserts_together=false): bool|array;
    
    /**
     * Injects the model from which the data originates.
     * 
     * @param \GDAO\Model $model The origin model object.
     */
    public function setModel(\GDAO\Model $model): static;
    
    /**
     * Returns an array representation of an instance of this class.
     * 
     * @return array an array representation of an instance of this class.
     */
    public function toArray(): array;
    
    /**
     * Returns a record from the collection based on its key value.
     * 
     * @param int|string $key The sequential or associative key value for the record.
     */
    public function __get($key): \GDAO\Model\RecordInterface;

    /**
     * Does a certain key exist in the data?
     * 
     * @param string $key The requested data key.
     */
    public function __isset($key): bool;

    /**
     * Set a key value.
     * 
     * @param string $key The requested key.
     * @param \GDAO\Model\RecordInterface $val The value to set it to.
     */
    public function __set($key, \GDAO\Model\RecordInterface $val): void;

    /**
     * ArrayAccess: set a key value; appends to the array when using [] notation.
     * 
     * NOTE: Implementers of this class must make sure that $val is an instance 
     *       of \GDAO\Model\RecordInterface else throw a 
     *       \GDAO\Model\CollectionCanOnlyContainGDAORecordsException exception.
     * 
     * @param string $key The requested key.
     * 
     * @param \GDAO\Model\RecordInterface $val The value to set it to.
     * 
     * @throws \GDAO\Model\CollectionCanOnlyContainGDAORecordsException
     */
    public function offsetSet($key, \GDAO\Model\RecordInterface $val): void;
    
    /**
     * Returns a string representation of an instance of this class.
     * 
     * @return string a string representation of an instance of this class.
     */
    public function __toString(): string;

    /**
     * Removes a record with the specified key from the collection.
     * 
     * @param string $key The requested data key.
     */
    public function __unset($key): void;
    
    /**
     * User-defined pre-delete logic.
     * 
     * Implementers of this class should add a call to this method as the first 
     * line of code in their implementation of $this->deleteAll()
     */
    public function preDeleteAll(): void;
    
    /**
     * User-defined post-delete logic.
     * 
     * Implementers of this class should add a call to this method as the last 
     * line of code in their implementation of $this->deleteAll()
     */
    public function postDeleteAll(): void;
    
    /**
     * User-defined pre-save logic for the collection.
     * 
     * Implementers of this class should add a call to this method as the first 
     * line of code in their implementation of $this->save(...)
     */
    public function preSaveAll(bool $group_inserts_together=false): void;
    
    /**
     * User-defined post-save logic for the collection.
     * 
     * Implementers of this class should add a call to this method as the 
     * last line of code in their implementation of $this->save(...)
     * 
     * @param bool|array $save_all_result result returned from $this->saveAll(..)
     * @param bool $group_inserts_together exact value passed to $this->saveAll($group_inserts_together)
     */
    public function postSaveAll(bool|array $save_all_result, bool $group_inserts_together=false): void;
}
