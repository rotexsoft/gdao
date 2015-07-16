<?php

/**
 * MockModel
 *
 * @author aadegbam
 */
class MockModelForTestingNonAbstractMethods extends \GDAO\Model
{
    /**
     * 
     * A pdo object for connecting to the db.
     * 
     * @var \PDO a pdo object
     */
    private $_pdo;
    
    public function __construct(
        $dsn = '', $uname = '', $pswd = '', $pdo_drv_opts = [], $ext_opts = []
    ) {
        if ($dsn || $uname || $pswd || $pdo_drv_opts || $ext_opts) {

            parent::__construct($dsn, $uname, $pswd, $pdo_drv_opts, $ext_opts);
        }
    }

    public function createNewRecord( 
        array $col_names_and_values = [], array $extra_opts = []
    ) {
        
    }

    public function deleteMatchingDbTableRows(array $cols_n_vals) {
        
    }

    public function fetchRecordsIntoArray(array $params = []) {
        
    }

    public function getPDO() {
        
        return $this->_pdo;
    }
    
    public function setPDO(\PDO $pdo) {
        
        $this->_pdo = $pdo;
    }

    public function validateWhereOrHavingParamsArray(array $array) {

        return $this->_validateWhereOrHavingParamsArray($array);
    }
    
    
    public function getWhereOrHavingClauseWithParams(
        array &$array, $indent_level=0
    ) {
        return $this->_getWhereOrHavingClauseWithParams($array, $indent_level);
    }

    public function deleteSpecifiedRecord(\GDAO\Model\Record $record) {
        
    }

    public function fetchRowsIntoArray(array $params = []) {
        
    }

    public function fetchCol(array $params = []) {
        
    }

    public function fetchOneRecord(array $params = []) {
        
    }

    public function fetchPairs(array $params = []) {
        
    }

    public function fetchValue(array $params = []) {
        
    }

    public function insert($col_names_n_vals = []) {
        
    }

    public function insertMany($col_names_n_vals = array()) {
        
    }
    
    public function updateMatchingDbTableRows(
        array $col_names_n_values_2_save = [],
        array $col_names_n_values_2_match = []
    ) {
        
    }

    public function updateSpecifiedRecord(\GDAO\Model\Record $record) {
        
    }
}