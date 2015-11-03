<?php

namespace GDAO\Model;

/**
 * This class is a collection / list implementation that guarantees that all the 
 * items in this collection / list are instances of \GDAO\Model\RecordInterface 
 * or any of its sub-classes.
 * 
 * @author Rotimi Adegbamigbe
 * @copyright (c) 2015, Rotimi Adegbamigbe
 */
final class RecordsList implements \Countable, \IteratorAggregate
{
    /**
     *
     * @var array
     */
    protected $_data = array();

    /**
     * 
     * @param array $records an array of instances of \GDAO\Model\RecordInterface or 
     *                            any of its sub-classes
     */
    public function __construct(array $records = null) {
        
        foreach($records as $key=>$record) {
            
            if( !($record instanceof \GDAO\Model\RecordInterface) ) {
                
                $class_name = get_class($this);
                $type = is_object($record)? get_class($record) : gettype($record); 
                
                $msg = "ERROR: $class_name only stores instances of "
                     . "\\GDAO\\Model\\RecordInterface. Incompatible item of type"
                     . " '$type' found at the position with a key value of '$key'"
                     . " in: " . PHP_EOL . var_export($records, true) 
                     . PHP_EOL . " Error occured in "
                     . get_class($this).'::' . __FUNCTION__ .'(...).'. PHP_EOL;
                
                throw new \InvalidArgumentException($msg);
            }
        }
        
        //All is well, all records are of the right type.
        $this->_data = $records;
    }

    /**
     * 
     * Add a \GDAO\Model\RecordInterface to the collection
     *
     * @param \GDAO\Model\RecordInterface $item A \GDAO\Model\RecordInterface to be added
     */
    public function add(\GDAO\Model\RecordInterface $item) { 
        
        $this->_data[] = $item;
    }

    /**
     * 
     * An array of instances of \GDAO\Model\RecordInterface to add to the collection
     *
     * @param array $items An array of instances of \GDAO\Model\RecordInterface to be added
     */
    public function addRange(array $items) {
        
        foreach($items as $key => $item) {
            
            if( !($item instanceof \GDAO\Model\RecordInterface) ) {
                
                $class_name = get_class($this);
                $type = is_object($item)? get_class($item) : gettype($item); 
                
                $msg = "ERROR: $class_name only stores instances of "
                     . "\\GDAO\\Model\\RecordInterface. Incompatible item of type"
                     . " '$type' found at the position with a key value of '$key'"
                     . " in: " . PHP_EOL . var_export($items, true) 
                     . PHP_EOL . " Error occured in "
                     . get_class($this).'::' . __FUNCTION__ .'(...).'. PHP_EOL;
                
                throw new \InvalidArgumentException($msg);
                
            } else {
                
                $this->_data[] = $item;
            }
        }
    }

    /**
     * Empties all of the items in the array
     */
    public function clear()
    {
        $this->_data = array();
    }

    /**
     * 
     * Removes all occurences of the record in the list
     *
     * @param \GDAO\Model\RecordInterface $record The Record to remove.
     * @return bool Whether the Record was found and removed
     */
    public function removeAll(\GDAO\Model\RecordInterface $record) {
        
       $result = false;
       
       foreach( $this->_data as $key => $value ) {
            
            if( $record === $value ) {
                
                $result = true;
                unset($this->_data[$key]);
            }
        }
        
        return $result;
    }

    /**
     * 
     * Removes the first occurence of the record in the list
     *
     * @param \GDAO\Model\RecordInterface $record The Record to remove.
     * @return bool Whether the Record was found and removed
     */
    public function removeFirst(\GDAO\Model\RecordInterface $record) {
        
       $result = false;
       
       foreach( $this->_data as $key => $value ) {
            
            if( $record === $value ) {
                
                $result = true;
                unset($this->_data[$key]);
                break;
            }
        }
        
        return $result;
    }

    /**
     * 
     * Removes the last occurences of the record in the list
     *
     * @param \GDAO\Model\RecordInterface $record The Record to remove.
     * @return bool Whether the Record was found and removed
     */
    public function removeLast(\GDAO\Model\RecordInterface $record) {
        
       $result = false;
       $reversed = array_reverse($this->_data, true);
       
       foreach( $reversed as $key => $value ) {
            
            if( $record === $value ) {
                
                $result = true;
                unset($this->_data[$key]);
                break;
            }
        }
        
        return $result;
    }

    /**
     * Return the collection / list as an array
     *
     * Returns the array that is encapsulated by the collection.
     *
     * @return array
     */
    public function toArray() {
        
        return $this->_data;
    }
    
    //////////////////////
    // Interface Methods
    //////////////////////
    
    /**
     * 
     * Countable Interface: 
     * The number of items in a collection
     *
     * @return integer The number of items in the collection
     */
    public function count() {
        
        return count($this->_data);
    }
    
    /**
     * 
     * IteratorAggregate Interface:
     * Get Iterator to satisfy IteratorAggregate interface
     * 
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_data);
    }
}
