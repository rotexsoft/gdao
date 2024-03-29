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
    protected string $primary_col = '';
    
    /**
     * 
     * A pdo object for connecting to the db.
     */
    private ?\PDO $_pdo = null;
    
    public function __construct(
        string $dsn = '', 
        string $uname = '', 
        string $pswd = '', 
        array $pdo_drv_opts = [],
        string $primary_col_name='',
        string $table_name=''
    ) {
        if ($dsn || $uname || $pswd || $pdo_drv_opts || $primary_col_name || $table_name) {

            parent::__construct($dsn, $uname, $pswd, $pdo_drv_opts, $primary_col_name, $table_name);
        }
    }

    public function createNewRecord( 
        array $col_names_and_values = []
    ): \GDAO\Model\RecordInterface { }

    public function deleteMatchingDbTableRows(array $cols_n_vals): int { }

    public function fetchRecordsIntoArray(?object $query=null, array $relations_to_include=[]): array { }

    public function getPDO(): \PDO {
        
        return $this->pdo;
    }
    
    public function setPDO(\PDO $pdo): void {
        
        $this->pdo = $pdo;
    }

    public function deleteSpecifiedRecord(\GDAO\Model\RecordInterface $record): ?bool { return null; }

    /**
     * @return mixed[]
     */
    public function fetchRowsIntoArray(?object $query=null, array $relations_to_include=[]): array { return []; }

    /**
     * @return mixed[]
     */
    public function fetchCol(?object $query=null): array { return []; }

    /**
     * @noRector
     * @return \GDAO\Model\RecordInterface|null
     */
    public function fetchOneRecord(?object $query=null, array $relations_to_include=[]): ?\GDAO\Model\RecordInterface { return null; }

    /**
     * @return mixed[]
     */
    public function fetchPairs(?object $query=null): array { return []; }

    
    /**
     * 
     * @noRector
     * @return mixed
     */
    public function fetchValue(?object $query=null): mixed { return 1; }

    /**
     * @noRector 
     * @return bool|array
     */
    public function insert(array $col_names_n_vals = []): bool|array { return []; }
    
    public function insertMany(array $col_names_n_vals = []): bool { return []; }
    
    /**
     * @noRector 
     * @return $this
     */
    public function updateMatchingDbTableRows(
        array $col_names_n_values_2_save = [],
        array $col_names_n_values_2_match = []
    ): static { return $this; }

    public function updateSpecifiedRecord(\GDAO\Model\RecordInterface $record): static { return $this; }
}
