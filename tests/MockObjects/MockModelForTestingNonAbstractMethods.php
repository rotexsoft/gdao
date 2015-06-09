<?php
/**
 * MockModel
 *
 * @author aadegbam
 */
class MockModelForTestingNonAbstractMethods extends \GDAO\Model
{
    public function createRecord(
        array $col_names_and_values = array(), array $extra_opts = array()
    ) {
        
    }

    public function deleteRecordsMatchingSpecifiedColsNValues(array $cols_n_vals) {
        
    }

    public function deleteSpecifiedRecord(\GDAO\Model\Record $record) {
        
    }

    public function fetchAllAsArray(array $params = array()) {
        
    }

    public function fetchArray(array $params = array()) {
        
    }

    public function fetchCol(array $params = array()) {
        
    }

    public function fetchOne(array $params = array()) {
        
    }

    public function fetchPairs(array $params = array()) {
        
    }

    public function fetchValue(array $params = array()) {
        
    }

    public function getPDO() {
        
    }

    public function insert($col_names_n_vals = array()) {
        
    }

    public function updateRecordsMatchingSpecifiedColsNValues(
        array $col_names_n_values_2_save = array(), 
        array $col_names_n_values_2_match = array()
    ) {    
    }

    public function updateSpecifiedRecord(\GDAO\Model\Record $record) {
        
    }
}