<?php

namespace GDAO {

    class ModelRequiresPdoInstanceException extends \Exception{}
    class ModelMustImplementMethodException extends \Exception{}
    class ModelInvalidInsertValueSuppliedException extends \Exception{}
    class ModelInvalidUpdateValueSuppliedException extends \Exception{}
    class ModelBadWhereOrHavingParamSuppliedException extends \Exception{}
    class ModelTableNameNotSetDuringConstructionException extends \Exception {}
    class ModelPrimaryColNameNotSetDuringConstructionException extends \Exception {}
    class ModelPrimaryColValueNotRetrievableAfterInsertException extends \Exception {}
}

namespace GDAO\Model {
    
    class ItemNotFoundInCollectionException extends \Exception {}
    class CollectionMustImplementMethodException extends \Exception{}
    class CollectionCanOnlyContainGDAORecordsException extends \Exception{}

    class RecordMustImplementMethodException extends \Exception{}
    class RecordOperationNotSupportedException extends \Exception{}
    class LoadingDataFromInvalidSourceIntoRecordException extends \Exception{}
    class RecordRelationWithSameNameAsAnExistingDBTableColumnNameException extends \Exception{}
}
