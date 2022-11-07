<?php
declare(strict_types=1);
namespace GDAO {
    
    class ModelMustImplementMethodException extends \Exception{}
    class ModelInvalidInsertValueSuppliedException extends \Exception{}
    class ModelInvalidUpdateValueSuppliedException extends \Exception{}
    class ModelTableNameNotSetDuringConstructionException extends \Exception {}
    class ModelPrimaryColNameNotSetDuringConstructionException extends \Exception {}
    class ModelPrimaryColValueNotRetrievableAfterInsertException extends \Exception {}
}

namespace GDAO\Model {
    
    class ItemNotFoundInCollectionException extends \Exception {}
    class CollectionCanOnlyContainGDAORecordsException extends \Exception{}

    class RecordOperationNotSupportedException extends \Exception{}
    class LoadingDataFromInvalidSourceIntoRecordException extends \Exception{}
    class RecordRelationWithSameNameAsAnExistingDBTableColumnNameException extends \Exception{}
}
