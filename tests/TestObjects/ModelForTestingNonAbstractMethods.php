<?php
declare(strict_types=1);
/**
 * 
 * A Model Class for testing non-abstract methods in the abstract class (\GDAO\Model).
 * 
 * @author aadegbam
 */
class ModelForTestingNonAbstractMethods extends \GDAO\Model
{
    protected string $_primary_col = '';
    
    /**
     * 
     * A pdo object for connecting to the db.
     */
    private ?\PDO $_pdo = null;
    
    public function __construct(
        string $dsn = '', string $uname = '', string $pswd = '', array $pdo_drv_opts = [], array $ext_opts = []
    ) {
        if ($dsn || $uname || $pswd || $pdo_drv_opts || $ext_opts) {

            parent::__construct($dsn, $uname, $pswd, $pdo_drv_opts, $ext_opts);
        }
    }

    public function createNewRecord( 
        array $col_names_and_values = [], array $extra_opts = []
    ): \GDAO\Model\RecordInterface { }

    public function deleteMatchingDbTableRows(array $cols_n_vals=[]): ?int { }

    public function fetchRecordsIntoArray(array $params = []): array { }

    public function getPDO(): \PDO {
        
        return $this->_pdo;
    }
    
    public function setPDO(\PDO $pdo): void {
        
        $this->_pdo = $pdo;
    }

    public function deleteSpecifiedRecord(\GDAO\Model\RecordInterface $record): ?bool { return null; }

    /**
     * @return mixed[]
     */
    public function fetchRowsIntoArray(array $params = []): array { return []; }

    /**
     * @return mixed[]
     */
    public function fetchCol(array $params = []): array { return []; }

    /**
     * @noRector
     * @return \GDAO\Model\RecordInterface|bool
     */
    public function fetchOneRecord(array $params = []) { return false; }

    /**
     * @return mixed[]
     */
    public function fetchPairs(array $params = []): array { return []; }

    
    /**
     * 
     * @noRector
     * @return mixed
     */
    public function fetchValue(array $params = []) { return 1; }

    /**
     * @noRector 
     * @return bool|array
     */
    public function insert(array $col_names_n_vals = []) { return []; }
    
    /**
     * @noRector
     * @return bool|array
     */
    public function insertMany(array $col_names_n_vals = []) { return []; }
    
    /**
     * @noRector 
     * @return bool|array
     */
    public function updateMatchingDbTableRows(
        array $col_names_n_values_2_save = [],
        array $col_names_n_values_2_match = []
    ) { }

    public function updateSpecifiedRecord(\GDAO\Model\RecordInterface $record): bool { return true; }
}
