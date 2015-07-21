<?php

namespace GDAO\Model;

/**
 * This class is an implementation that guarantees that all the items in this
 * list are instances of \GDAO\Model\RecordInterface or any of its sub-classes.
 * 
 * It depends on https://github.com/danielgsims/php-collections / 
 * https://packagist.org/packages/danielgsims/php-collections
 *
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
 */
final class GDAORecordsList implements \Countable, \IteratorAggregate
{
    /**
     *
     * @var \Collections\Collection
     */
    protected $_data;

    /**
     * 
     * @param array $gdao_records an array of instances of \GDAO\Model\RecordInterface or 
     *                            any of its sub-classes
     */
    public function __construct(array $gdao_records = null) {

        $this->_data = new \Collections\Collection('\\GDAO\\Model\\RecordInterface');

        if (is_array($gdao_records) && count($gdao_records) > 0) {

            $this->_data->addRange($gdao_records);
        }
    }

    /**
     * 
     * Get the actual collection object powering this class
     * 
     * @return \Collections\Collection
     */
    public function &getData() {

        return $this->_data;
    }

    /**
     * 
     * Add a \GDAO\Model\RecordInterface to the collection
     *
     * @param \GDAO\Model\RecordInterface $item A \GDAO\Model\RecordInterface to be added
     */
    public function add(\GDAO\Model\RecordInterface $item) {

        $this->_data->add($item);
    }

    /**
     * 
     * An array of instances of \GDAO\Model\RecordInterface to add to the collection
     *
     * @param array $items An array of instances of \GDAO\Model\RecordInterface to be added
     */
    public function addRange(array $items) {

        $this->_data->addRange($items);
    }

    /**
     * 
     * Fetches the \GDAO\Model\RecordInterface at the specified index
     *
     * @param integer $index The index of the \GDAO\Model\RecordInterface to fetch
     * @throws InvalidArgumentException
     * @throws OutOfRangeException
     * @return mixed The \GDAO\Model\RecordInterface at the specified index
     */
    public function at($index) {

        return $this->_data->at($index);
    }

    /**
     * 
     * Determines whether the specified \GDAO\Model\RecordInterface is in the Collection
     *
     * @param \GDAO\Model\RecordInterface $needle The record to search for in the collection
     * @return bool Whether the specified \GDAO\Model\RecordInterface was in the array or not
     */
    public function contains(\GDAO\Model\RecordInterface $needle) {
        
        return $this->_data->contains($needle);
    }

    /**
     * 
     * The number of items in a collection
     *
     * @return integer The number of items in the collection
     */
    public function count() {
        
        return $this->_data->count();
    }

    /**
     * 
     * Check to see if an item in the collection exists that satisfies the provided callback
     *
     * @param callback $condition The condition criteria to test each item, requires one argument that represents the Collection item during an iteration.
     * @return bool Whether an item exists that satisfied the condition
     */
    public function exists(callable $condition) {
        
        return $this->_data->exists($condition);
    }

    /**
     * Finds and returns the first item in the collection that satisfies the callback.
     *
     * @param callback $condition The condition critera to test each item, requires one argument that represents the Collection item during iteration.
     * @return mixed|bool The first item that satisfied the condition or false if no object was found
     */
    public function find(callable $condition) {
        
        return $this->_data->find($condition);
    }

    /**
     * Returns a collection of all items that satisfy the callback function. If nothing is found, returns an empty
     * Collection
     *
     * @param calback $condition The condition critera to test each item, requires one argument that represents the Collection item during iteration.
     * @return Collectiona A collection of all of the items that satisfied the condition
     */
    public function findAll(callable $condition) {
        
        return $this->_data->findAll($condition);
    }

    /**
     * Finds the index of the first item that returns true from the callback,
     * returns -1 if no item is found
     *
     * @param callback $condition The condition critera to test each item, requires one toargument that represents the Collection item during iteration.
     * @return integer The index of the first item satisfying the callback or -1 if no item was found
     */
    public function findIndex(callable $condition) {
        
        return $this->_data->findIndex($condition);
    }

    /**
     * Finds and returns the last item in the collection that satisfies the callback.
     *
     * @param callback $condition The condition criteria to test each item, requires one argument that represents the Collection item during an iteration.
     * @return mixed|bool The last item that matched condition or -1 if no item was found matching the condition.
     */
    public function findLast(callable $condition) {
        
        return $this->_data->findLast($condition);
    }

    /**
     * Finds the index of the last item that returns true from the callback,
     * returns -1 if no item is found
     *
     * @param callback $condition The condition criteria to test each item, requires one argument that represents the Collection item during an iteration.
     * @return integer The index of the last item  to match that matches the condition, returns -1 if no item was found
     */
    public function findLastIndex(callable $condition) {
        
        return $this->_data->findLastIndex($condition);
    }

    /**
     * Insert the \GDAO\Model\RecordInterface at index
     *
     * @throws InvalidArgumentException
     * @param integer $index The index where to insert the item
     * @param \GDAO\Model\RecordInterface $item The \GDAO\Model\RecordInterface to insert
     */
    public function insert($index, \GDAO\Model\RecordInterface $item) {
        
        $this->_data->insert($index, $item);
    }

    /**
     * Inset a range at the index
     *
     * @param integer $index Index where to insert the range
     * @param array items An array of instances of \GDAO\Model\RecordInterface to insert
     */
    public function insertRange($index, array $items) {
        
        $this->_data->insertRange($index, $items);
    }

    /**
     * Removes the first item that satisfies the condition callback
     *
     * @param callback $condition The condition critera to test each item, requires one argument that represents the Collection item during iteration.
     * @return bool Whether the item was found
     */
    public function remove(callable $condition) {
        
        return $this->_data->remove($condition);
    }

    /**
     * Removes all items that satisfy the condition callback
     *
     * @param callback @condition The condition criteria to test each item, requires on argument that represents the Collection item during interation.
     * @return int the number of items found
     */
    public function removeAll(callable $condition) {
        
        return $this->_data->removeAll($condition);
    }

    /**
     * Removes the item at the specified index
     *
     * @param integer $index The index where the object should be removed
     */
    public function removeAt($index) {
        
        $this->_data->removeAt($index);
    }

    /**
     * Removes the last item to satisfy the condition callback
     *
     * @param callback $condition The condition criteria to test each item, requires one argument that represents the Collection item during an iteration.
     * @return bool Whether the item was removed or not
     */
    public function removeLast(callable $condition) {
        
        return $this->_data->removeLast($condition);
    }

    /**
     * Reverses the Collection
     */
    public function reverse() {
        
        $this->_data->reverse();
    }

    /**
     * Sorts the collection with a usort
     */
    public function sort(callable $callback) {
        
        return $this->_data->sort($callback);
    }

    /**
     * Return the collection as an array
     *
     * Returns the array that is encapsulated by the collection.
     *
     * @return array
     */
    public function toArray() {
        
        return $this->_data->toArray();
    }
    
    /**
     * Get Iterator to satisfy IteratorAggregate interface
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->_data->getIterator();
    }
}
