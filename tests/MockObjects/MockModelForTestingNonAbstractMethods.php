<?php

/**
 * MockModel
 *
 * @author aadegbam
 */
class MockModelForTestingNonAbstractMethods extends \GDAO\Model
{

    public function __construct(
        $dsn = '', $uname = '', $pswd = '', $pdo_drv_opts = [], $ext_opts = []
    ) {
        if ($dsn || $uname || $pswd || $pdo_drv_opts || $ext_opts) {

            parent::__construct($dsn, $uname, $pswd, $pdo_drv_opts, $ext_opts);
        }
    }

    public function createRecord( 
        array $col_names_and_values = [], array $extra_opts = []
    ) {
        
    }

    public function deleteRecordsMatchingSpecifiedColsNValues(array $cols_n_vals) {
        
    }

    public function fetchAllAsArray(array $params = []) {
        
    }

    public function getPDO() {
        
    }

    public function validateWhereOrHavingParamsArray(array $array) {

        return $this->_validateWhereOrHavingParamsArray($array);
    }

    public function deleteSpecifiedRecord(\GDAO\Model\Record $record) {
        
    }

    public function fetchArray(array $params = []) {
        
    }

    public function fetchCol(array $params = []) {
        
    }

    public function fetchOne(array $params = []) {
        
    }

    public function fetchPairs(array $params = []) {
        
    }

    public function fetchValue(array $params = []) {
        
    }

    public function insert($col_names_n_vals = []) {
        
    }

    public function updateRecordsMatchingSpecifiedColsNValues(
        array $col_names_n_values_2_save = [],
        array $col_names_n_values_2_match = []
    ) {
        
    }

    public function updateSpecifiedRecord(\GDAO\Model\Record $record) {
        
    }
}