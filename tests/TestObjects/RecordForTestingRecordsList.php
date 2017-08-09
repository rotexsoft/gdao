<?php

/**
 * 
 * This is a dummy record class needed for Testing RecordsList.
 *
 * @author aadegbam
 */
class RecordForTestingRecordsList implements \GDAO\Model\RecordInterface
{
    public function __construct(
        array $data, \GDAO\Model $model, array $extra_opts = array()
    ) { }

    public function __get($key) { }

    public function __isset($key) { }

    public function __set($key, $val) { }

    public function __toString() { }

    public function __unset($key) { }

    public function count() { }

    public function delete($set_record_objects_data_to_empty_array = false) { }

    public function getData() { }

    public function &getDataByRef() { }

    public function getInitialData() { }

    public function &getInitialDataByRef() { }

    public function getIterator() { }

    public function getModel() { }

    public function getPrimaryCol() { }

    public function getPrimaryVal() { }

    public function getRelatedData() { }

    public function &getRelatedDataByRef() { }

    public function isChanged($col = null) { }

    public function isNew() { }

    public function loadData($data_2_load, array $cols_2_load = array()) { }

    public function markAsNew() { }

    public function markAsNotNew() { }

    public function offsetExists($key) { }

    public function offsetGet($key) { }

    public function offsetSet($key, $val) { }

    public function offsetUnset($key) { }

    public function save($data_2_save = null) { }

    public function saveInTransaction($data_2_save = null) { }

    public function setModel(\GDAO\Model $model) { }

    public function setRelatedData($key, $val) { }

    public function setStateToNew() { }

    public function toArray() { }
    
    public function getNonTableColAndNonRelatedData() { ; }
    
    public function &getNonTableColAndNonRelatedDataByRef() { ; }
}