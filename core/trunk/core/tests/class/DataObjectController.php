<?php

// Schema
require_once 'core/tests/class/Schema.php';

// Document & objects
require_once 'core/tests/class/DataObject.php';

// Data access services
require_once 'core/tests/class/DataAccessService_Database.php';
require_once 'core/tests/class/DataAccessService_XML.php';

// Messages
require_once 'core/tests/class/MessageController.php';
require_once 'core/tests/class/Message.php';
require_once 'core/tests/class/Exception.php';

class DataObjectController 
    extends DOMXPath
{

    public $dataObjectDocuments = array();
    protected $dataAccessServices = array();
    protected $XRefs = array();
    protected $messageController;
    public $inTransaction;
    public $transactionControl;
       
    //*************************************************************************
    // CONSTRUCTOR
    //*************************************************************************
    public function DataObjectController() 
    {
        //$this->XRefs = new SchemaXRefs();
        $this->messageController = new MessageController();
        $this->messageController->loadMessageFile(
            $_SESSION['config']['corepath'] 
                . 'core/xml/DataObjectController_Messages.xml'
        );
    }
    
    // Load schema from master Xsd file
    public function loadXSD($XSDFile)
    {
        // Construct Schema and load XSD with includes
        $schema = new Schema();
        $schema->loadXSD($XSDFile);
        
        // Construct SchemaController
        parent::__construct($schema);
        $this->schema = $this->document;
    }
    
    // Include XSD file content to master schema
    public function includeXSD($XSDFile)
    {
        $this->schema->includeXSD(
            $this->schema, 
            $XSDFile, 
            $this->schema
        );
    }
    
    // Load schema from Schema object
    public function loadSchema($schemaXML)
    {
        // Construct SchemaController
        parent::__construct($schemaXML);
        $this->schema = $this->document;

    }
    
    //*************************************************************************
    // PUBLIC SCHEMA INFO
    //*************************************************************************
    public function getKeyProperties($objectName)
    {
        $objectElement = $this->getElementByName($objectName);
        return explode(' ', $objectElement->getAttribute('das:key'));
    }
    
    public function getFilterProperties($objectName)
    {
        $objectElement = $this->getElementByName($objectName);
        $filters = explode(' ', $objectElement->getFilter());
        $return = array();
        for($i=0; $i<count($filters); $i++) {
            $filter = $filters[$i];
            $propertyName = str_replace(
                "@", 
                "", 
                $filter->getAttribute('xpath')
            );
            $return[] = $propertyName;  
        }
        return $return;
    }
    
    public function getObjectProperties($objectName)
    {
        $objectProperties = array();
        $objectElement = $this->getElementByName($objectName);
        $objectContents = $this->getObjectContents($objectElement);
        $l = count($objectContents);
        for($i=0; $i<$l; $i++) {
            $contentNode = $objectContents[$i];
            if($contentNode->hasDatasource()) continue;
            $objectProperties[] = $contentNode;
        }
        return $objectProperties;
    }
    
    public function getTypeEnumeration(
        $typeName
    ) {
        $typeEnumeration = new DataObjectList();
        
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        
        $simpleType = $this->getTypeByName($typeName);
        $enumerations = $this->query('.//xsd:enumeration', $simpleType);
        $l = $enumerations->length;
        for($i=0; $i<$l; $i++) {
            $enumeration = $enumerations->item($i);
            $dataObject = $dataObjectDocument->importNode($enumeration, true);
            $dataObjectDocument->appendChild($dataObject);
            $typeEnumeration[] = $dataObject;
        }
        return $typeEnumeration;
    }
     
    //*************************************************************************
    // PUBLIC OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    public function create(
        $objectName, 
        $parentDataObject=false
    ) {
        if($parentDataObject) {
            $dataObjectDocument = $parentDataObject->ownerDocument; 
        } else {
            $dataObjectDocument = new DataObjectDocument();
            $this->dataObjectDocuments[] = $dataObjectDocument;
        }
        $schemaElement = $this->getElementByName($objectName);
        
        if(!$schemaElement->isCreatable()) 
            throw new maarch\Exception("Object $objectName can not be created");

        $dataObject = 
            $this->createDataObject(
                $schemaElement, 
                $dataObjectDocument,
                $includeChildren = true
            );
        $dataObject->logCreate();
        $dataObjectDocument[] = $dataObject;
        
        return $dataObject;
    }
    
    public function enumerate(
        $objectName, 
        $filter=false, 
        $sort=false, 
        $order=false,
        $limit=false
        ) 
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        
        $rootElement = $this->query(
            '/xsd:schema/xsd:element[@das:module != ""]'
            )->item(0);

        $rootDataObject = 
            $this->createDataObject(
                $rootElement, 
                $dataObjectDocument
            );
        $dataObjectDocument->appendChild($rootDataObject);

        $schemaElement = $this->getElementByName($objectName);
        if(!$schemaElement->isListable()) 
            throw new maarch\Exception("Object $objectName can not be listed");
        
        $refElement = $this->getRefNode($schemaElement);
        
        $this->listDataObject(
            $refElement,
            $rootDataObject,
            $dataObjectDocument,
            $key,
            $filter,
            $sort, 
            $order,
            $limit
        );
        
        $dataObjectsNodeList = $rootDataObject->query("./" . $objectName);
        
        $dataObjectList = new DataObjectList($dataObjectsNodeList);
        
        return $dataObjectList;
    }
    
    public function read(
        $objectName, 
        $key,
        $readChildren=true
    ) {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        
        $objectElement = $this->getElementByName($objectName);
        
        if(!$objectElement->isReadable()) throw new maarch\Exception("Object $objectName can not be read");
        
        $this->readDataObject(
            $objectElement, 
            $dataObjectDocument, 
            $dataObjectDocument,
            $key,
            $readChildren
        );
        return $dataObjectDocument->documentElement;
    }
    
    public function save(
        $dataObject, 
        $saveChildren = true
    ) {
        $objectElement = $this->getElementByName($dataObject->tagName);
        $dataObjectDocument = $dataObject->ownerDocument;
        if(!$this->inTransaction) {
            $this->startTransaction('controller');
        }
        try {
            $key = $this->saveDataObject(
                $objectElement, 
                $dataObject,
                $saveChildren
            );
            if($this->transactionControl == 'controller') {
                $this->commit();
            }
            return $key;
        } catch (maarch\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    public function readChildren(
        $dataObject
    ) {
        $objectName = $dataObject->getName();
        $objectElement = $this->getElementByName($objectName);
        $this->readChildDataObjects(
            $objectElement,
            $dataObject,
            $dataObject->ownerDocument
        );
        return $dataObject;
    }
    
    public function loadXMLArray($XMLArray)
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        
        $rootNode = $dataObjectDocument->createElement('root');
        $dataObjectDocument->appendChild($rootNode);
        
        $dataObjectList = new DataObjectList();
        for($i=0; $i<count($XMLArray); $i++) {
            $dataObject = $this->loadXML($XMLArray[$i]);
            $rootNode->appendChild($dataObjectDocument->importNode($dataObject,true));
            $dataObjectList[] = $dataObject;
        }
        return $dataObjectList;
    }
    
    
    public function loadXML($xml)
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        $dataObjectDocument->loadXML($xml);
        $dataObject = $dataObjectDocument->documentElement;
        return $dataObject;
    }
    
    public function load($XmlFile) 
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        $dataObjectDocument->load($XmlFile);
        $dataObject = $dataObjectDocument->documentElement;
        return $dataObject;
    }
  
    public function delete($dataObject)
    {
        $objectElement = $this->getElementByName($dataObject->tagName);
        if(!$objectElement->isDeletable()) throw new maarch\Exception("Object $objectName can not be deleted");
        $this->deleteDataObject($objectElement, $dataObject);
    }
    
    public function copy(
        $dataObject, 
        $newName=false,
        $parentDataObject=false
    ){
        if($parentDataObject) {
            $dataObjectDocument = $parentDataObject->ownerDocument; 
        } else {
            $dataObjectDocument = new DataObjectDocument();
            $this->dataObjectDocuments[] = $dataObjectDocument;
        }
        
        if(!$newName) $newName = $dataObject->nodeName;
        $newObject = $dataObjectDocument->createElement($newName);
        if ($dataObject->attributes->length) {
            foreach ($dataObject->attributes as $attribute) {
                $newObject->setAttribute($attribute->nodeName, $attribute->nodeValue);
            }
        }
        $childNodes = $dataObject->childNodes;
        $childNodesLength = $childNodes->length;
        for ($i=0; $i<$childNodesLength; $i++) {
            $childNode = $childNodes->item($i);
            $newNode = $dataObjectDocument->importNode($childNode, true); 
            $newObject->appendChild($newNode);
        }
        $newObject->clearLogs();
        $newObject->logCreate();
        $dataObjectDocument[] = $newObject;
        return $newObject;
    }
    
    public function validate($dataObject) 
    {
        $messageController = new MessageController();
        $messageController->loadMessageFile(
            $_SESSION['config']['corepath'] 
                . '/core/xml/DataObjectController_Messages.xml'
        );
        
        // Validate with specific business script
        //*********************************************************************
        /*$objectElement = $this->getElementByName($dataObject->tagName);
        if($objectElement->hasAttribute('das:validation')) {
            include_once $objectElement->getAttribute('das:validation');
        }*/
        
        // Validate against schema
        //*********************************************************************
        $XsdString = $this->schema->saveXML();
        libxml_use_internal_errors(true);
        $dataObjectDocument = $dataObject->ownerDocument;
        if(!$dataObjectDocument->schemaValidateSource($XsdString)) {
            $blockingErrors = 0;
            $libXMLErrors = libxml_get_errors();
            foreach ($libXMLErrors as $libXMLError) {
                switch ($libXMLError->level) {
                    case LIBXML_ERR_ERROR:
                    case LIBXML_ERR_FATAL:
                        $blockingErrors++;
                        break;
                }
                $dataObject->logValidate(
                    $libXMLError->code,
                    $libXMLError->message,
                    $libXMLError->level);
            }
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            if($blockingErrors > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            $dataObject->logValidate('0000', DataObjectLog::INFO, 'Valid');
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            return true;
        }

    }
        
    //*************************************************************************
    // protected OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    protected function createDataObject(
        $objectElement, 
        $dataObjectDocument,
        $includeChildren = false
    ) {
        $objectName = $objectElement->getName();
        
        $dataObject = $dataObjectDocument->createElement($objectName);
        
        // Add children and attributes
        $objectContents = $this->getObjectContents($objectElement);
        $l = count($objectContents);
        for($i=0; $i<$l; $i++) {
            $contentNode = $objectContents[$i];
            $contentName = $contentNode->getName();
            $required = $contentNode->isRequired();
            $refNode = $this->getRefNode($contentNode);
            $contentValue = $this->getValue($refNode);
            
            switch($refNode->tagName) {
            case 'xsd:attribute':
                if($contentValue != '') {
                    $dataObject->setAttribute(
                        $contentName, 
                        $contentValue
                    );
                }
                break;
            case 'xsd:element':
                if($refNode->hasDatasource()) {
                    // Comment DataObject
                    $childObject = 
                            $dataObjectDocument->createDataObject(
                                $contentName
                            );
                    $dataObject->appendChild($childObject);
                    // Instance if required and included
                    if(!$contentNode->isCreatable()) continue;
                    if($required && $includeChildren) {
                        $childObject = $this->createDataObject(
                            $refNode,
                            $dataObjectDocument,
                            true
                        );
                        $dataObject->appendChild($childObject);
                        $childObject->logCreate();
                    }
                } else {
                    // Property Element
                    if($required) {
                        $property = 
                            $dataObjectDocument->createElement(
                                $contentName, 
                                $contentValue
                            );
                    } else {
                        $property = 
                            $dataObjectDocument->createProperty(
                                $contentName
                            );
                    }
                    $dataObject->appendChild($property);
                }
                break;
            }
        }     
        
        return $dataObject;
    }

    protected function listDataObject(
        $objectElement,
        $parentObject,
        $dataObjectDocument,
        $key=false,
        $filter=false,
        $sort=false,
        $order='ascending',
        $limit=99999999
    ) {
        try {
            if($dataAccessService = 
                $this->getDataAccessService($objectElement)
            ) {
                $dataAccessService->loadData(
                    $objectElement,
                    $parentObject,
                    $dataObjectDocument,
                    $key,
                    $filter,
                    $sort,
                    $order,
                    $limit
                    );
            } else {
                $dataObject = $this->createDataObject(
                    $objectElement, 
                    $dataObjectDocument
                );
                $parentObject->appendChild($dataObject);
                $dataObject->logRead();
            }
        } catch (maarch\Exception $e) {   
            throw $e;
        }
    }
    
    protected function readDataObject(
        $objectElement,
        $parentObject,
        $dataObjectDocument,
        $key=false,
        $readChildren=true
    ) {      
        try {
            if($dataAccessService = 
                $this->getDataAccessService($objectElement)
            ) {
                $dataAccessService->loadData(
                    $objectElement,
                    $parentObject,
                    $dataObjectDocument,
                    $key,
                    $filter,
                    $sort,
                    $order,
                    $limit
                    );
            } else {
                $dataObject = $this->createDataObject(
                    $objectElement, 
                    $dataObjectDocument
                );
                $parentObject->appendChild($dataObject);
                $dataObject->logRead();
            }
            
            if($readChildren) {
                // Process newly created child objects
                $newObjects = 
                    $parentObject->getChildNodesByTagName(
                        $objectElement->getName()
                    );
                $m = $newObjects->length;
                for($j=0; $j<$m; $j++) {
                    $newObject = $newObjects->item($j);
                    $this->readChildDataObjects(
                        $objectElement,
                        $newObject, 
                        $dataObjectDocument
                    );
                }
            }

        } catch (maarch\Exception $e) {   
            throw $e;
        }
    }
    
    public function readChildDataObjects( 
        $objectElement,
        $dataObject,
        $dataObjectDocument
    ) {
        // Get list of childElements
        $objectContents = $this->getObjectContents($objectElement);
        $l = count($objectContents);
        for($i=0; $i<$l; $i++) {
            $objectNode = $objectContents[$i];
            if(!$objectNode->isReadable()) continue;
            $refNode = $this->getRefNode($objectNode);
            if(!$refNode->hasDatasource()) continue;
            //echo "<br/>Read child of " . $objectElement->getName() . " named " . $refNode->getName();
            $this->readDataObject(
                $refNode, 
                $dataObject, 
                $dataObjectDocument
            );
            
        }
    }
    
    protected function saveDataObject(
        $objectElement, 
        $dataObject,
        $saveChildren=true
    ) {
        try { 
            $refElement = $this->getRefNode($objectElement);
            if($dataAccessService = 
                $this->getDataAccessService($refElement)
            ) {
                if($dataObject->isCreated() 
                    && !$dataObject->isDeleted()
                ) {
                    $key = $dataAccessService->insertData(
                            $refElement, 
                            $dataObject
                        );
                    $this->setDataObjectKey(
                        $refElement, 
                        $dataObject,
                        $key
                    );
                    if($saveChildren) {
                        $this->saveChildDataObjects(
                            $objectElement,
                            $dataObject
                        );
                    }
                } elseif ($dataObject->isRead()
                    && !$dataObject->isDeleted()
                ) {
                    if($objectElement->isUpdatable()) {
                        if(count($dataObject->getUpdatedProperties()) > 0) {
                            $key = $dataAccessService->updateData(
                                $refElement, 
                                $dataObject
                            );
                        } 
                    }
                    $key = $dataObject;
                    if($saveChildren) {
                        $this->saveChildDataObjects(
                            $objectElement,
                            $dataObject
                        );
                    }
                } elseif ($dataObject->isRead() 
                    && $dataObject->isDeleted()) {
                    if($objectElement->isDeletable()) {
                        $this->saveChildDataObjects(
                            $objectElement,
                            $dataObject
                        );
                    }
                    $key = $dataAccessService->deleteData(
                            $refElement, 
                            $dataObject
                        );
                } elseif (
                    $dataObject->isCreated() 
                    && $dataObject->isDeleted()
                ) {
                    // NOTHING
                }
            } 
            $dataObject->clearLogs();
            $dataObject->logRead();
            return $key;
        } catch (maarch\Exception $e) {   
            throw $e;
        }
    }
    
    protected function setDataObjectKey(
        $objectElement, 
        $dataObject, 
        $returnKey = false
    ) {
        $key = $objectElement->getAttribute('das:key');
        if(!$key) return false;
        $keyFields = explode(' ', $key);
        $l = count($keyFields);
        for($i=0; $i<$l; $i++) {
            $keyField = $keyFields[$i];
            if(!isset($dataObject->$keyField) 
                || mb_strlen(trim($dataObject->$keyField)) == 0
            ) {
                $dataObject->$keyField = $returnKey->$keyField;
            }
        }
    }
    
    protected function saveChildDataObjects(
        $objectElement, 
        $dataObject
    ) {
        $objectContents = $this->getObjectContents($objectElement);
        $l = count($objectContents);
        for($i=0; $i<$l; $i++) {
            $objectNode = $objectContents[$i];
            
            $refNode = $this->getRefNode($objectNode);
            if(!$refNode->hasDatasource()) continue;
            
            // Get relation between dataObject and child
            //echo "<br/>Relation between child " . $refNode->getName() . " and parent " . $dataObject->getName();
            $relation = $this->getRelation($refNode, $dataObject);
            if(!$relation) {
                throw new maarch\Exception(
                    "No relation defined in schema between " . $refNode->getName() 
                    . " and " . $dataObject->nodeName 
                    . "! Rolling back transaction"
                );
            }
            $fkeys = $this->query('./das:foreign-key', $relation);
            $n = $fkeys->length;
            
            // List children elements of given type
            $childObjects = 
                $dataObject->getChildNodesByTagName(
                    $objectNode->getName()
                );
            $m = $childObjects->length;
            for($j=0; $j<$m; $j++) {
                $childObject = $childObjects->item($j);
                
                // Assign key values to child if needed
                for($k=0; $k<$n; $k++) {
                    $fkey = $fkeys->item($k);
                    $parentKey = $fkey->getAttribute('parent-key');
                    $childKey = $fkey->getAttribute('child-key');
                    //echo "<br/>parentkey is $parentKey, childkey is $childKey";
                    $childObject->$childKey = $dataObject->$parentKey;
                    //echo " --> childkey set to :" . $childObject->$childKey;
                }
                
                $this->saveDataObject(
                    $objectNode, 
                    $childObject
                );
            }
        }
    
    }
    
    protected function deleteDataObject(
        $objectElement,
        $dataObject
    ) {
        
        $dataObject->logDelete();
        
        // Process children
        $objectContents = $this->getObjectContents($objectElement);
        $l = count($objectContents);
        for($i=0; $i<$l; $i++) {
            $objectNode = $objectContents[$i];
            if(!$objectNode->isDeletable()) continue;
            $refNode = $this->getRefNode($objectNode);
            if(!$refNode->hasDatasource()) continue;
         
            // List children elements of given type
            $childObjects = 
                $dataObject->getChildNodesByTagName(
                    $refNode->getName()
                );
            $m = $childObjects->length;
            
            for($j=0; $j<$m; $j++) {
                $childObject = $childObjects->item($j);              
                $this->deleteDataObject(
                    $refNode, 
                    $childObject
                );
            }
        }
    }
    
    //*************************************************************************
    // DATA OBJECTS QUERY FUNCTIONS
    //*************************************************************************
    protected function getDataObjectElement($dataObject)
    {
        $objectName = $dataObject->tagName;
        return $this->getElementByName($objectName);
    }
    
    protected function getDataObjectDocument($dataObject)
    {
        return $dataObject->documentElement();
    }
    
    //*************************************************************************
    // DATA ACCESS SERVICE FUNCTIONS
    //*************************************************************************
    protected function createDataAccessServices()
    {
        $das = $this->query('//das:source');
        for($i=0; $i<$das->length; $i++) {
            $dasNode = $das->item($i);
            $parentElement = $dasNode->parentNode->parentNode->parentNode;
            // xsd:?????/xsd:annotation/xsd:appinfo
            if ($parentElement == $this->schema->documentElement) {
                $dasName = $dasNode->getAttribute('name');
            } else {
                $dasName = $parentElement->getName();
            }
            $this->createDataAccessService(
                $dasName, $dasNode
            );
        }
    }
    
    protected function getDataAccessService($node)
    {
        if($node->hasAttribute('das:source')) {
            $sourceName = $node->getAttribute('das:source');
            if(!isset($this->dataAccessServices[$sourceName])) {
                $sourceNode = $this->query(
                    '//das:source[@name="'
                    . $sourceName
                    . '"]'
                )->item(0);
                $this->createDataAccessService($sourceName, $sourceNode);
            }
        } elseif($sourceNode = $this->query(
                './xsd:annotation/xsd:appinfo/das:source', 
                $node
            )->item(0)
        ) {
            $sourceName = $node->getAttribute('name');
            if(!isset($this->dataAccessServices[$sourceName])) {
                $this->createDataAccessService($sourceName, $sourceNode);
            }
        }
        return $this->dataAccessServices[$sourceName];

    }
    
    protected function createDataAccessService($sourceName, $sourceNode) 
    {
        switch($sourceNode->getAttribute('type')) {
        case 'database':
            $this->dataAccessServices[$sourceName] = 
                new DataAccessService_Database();
            $this->dataAccessServices[$sourceName]->loadSchema($this->schema);
            $this->dataAccessServices[$sourceName]->connect($sourceNode);
            $this->dataAccessServices[$sourceName]->XRefs = &$this->XRefs;
            break;
        case 'xml':
            break;
        } 
    }
    
    public function startTransaction($transactionControl='caller')
    {
        //echo "<br/>Starting transaction for control " . $transactionControl;
        $this->transactionControl = $transactionControl;
        $this->createDataAccessServices();
        foreach($this->dataAccessServices as $dasName => $dataAccessService) {
            //echo "<br/>Start transaction for ". $dasName;
            if(method_exists($dataAccessService, 'startTransaction')) {
                $dataAccessService->startTransaction();
            }
        }
        $this->inTransaction = true;
    }
    
    public function commit()
    {
        //echo "<br/>Commit transaction for control " . $this->transactionControl;
        foreach($this->dataAccessServices as $dataAccessService) {
            if(method_exists($dataAccessService, 'commit')) {
                $dataAccessService->commit();
            }
        }
    }
    
    public function rollback()
    {
        //echo "<br/>Rollbak transaction for control " . $this->transactionControl;
        foreach($this->dataAccessServices as $dataAccessService) {
            if(method_exists($dataAccessService, 'rollback')) {
                $dataAccessService->rollback();
            }
        }
    }
    
    //*************************************************************************
    // SCHEMA QUERY FUNCTIONS
    //*************************************************************************
    protected function getElementByName($name)
    {
        $element = $this->query(
            '/xsd:schema/xsd:element[@name = "'
            . $name
            . '"]'
        )->item(0);
        if(!$element) {
            throw new maarch\Exception("Element $name is not defined");
        }
        return $element;
    }
    
    protected function getAttributeByName($name)
    {
        $attribute = $this->query(
            '/xsd:schema/xsd:attribute[@name = "'
            . $name
            . '"]'
        )->item(0);
        if(!$attribute) throw new maarch\Exception("Attribute $name is not defined");
        return $attribute;
    }
    
        
    protected function getTypeByName($typeName)
    {
        $type = $this->query(
            '/xsd:schema/xsd:simpleType[@name = "'
            . $typeName
            . '"]'
            . ' | '
            . '/xsd:schema/xsd:complexType[@name = "'
            . $typeName
            . '"]'
        )->item(0);
        if(!$type) throw new maarch\Exception("Type $typeName is not defined");
        return $type;
    }
       
    protected function hasDatasource($node)
    {
        if($node->hasDatasource() 
            || $this->query(
                    './xsd:annotation/xsd:appinfo/das:source', 
                    $node 
                )->length > 0
        ){
            return true;
        }
    
    }
    
    protected function getContentByName($objectElement, $name)
    {
        $objectContents = $this->getObjectContents($objectElement);
        $l = count($objectContents);
        for($i=0; $i<$l; $i++) {
            $contentNode = $objectContents[$i];
            if($contentNode->getName() == $name) {
                return $contentNode;
            }
        }
        
        throw new maarch\Exception("Content $name of object name " . $objectElement->getName() . " is not defined");
    }
    
    protected function getRefNode($node)
    {
        if($node->hasAttribute('ref')) {
            $refNode = $this->query(
                '//'.$node->tagName.'[@name = "'
                . $node->getAttribute('ref')
                . '"]'
            )->item(0);
            if(!$refNode) 
                throw new maarch\Exception("Referenced node " 
                    . $node->getAttribute('ref') 
                    . " is not defined"
                );
            return $refNode;
        } else {
            return $node;
        }
    }
    
    protected function getValue($node)
    {
        if($node->hasAttribute('fixed')) {
            return $node->getAttribute('fixed');
        } 
        if($node->hasAttribute('default')) {
            return $node->getAttribute('default');
        }
    }
    
    protected function getType($node)
    {
        //echo "<br/>Get type of $node->tagName " . $node->getAttribute('name');
        //if(!$type = $this->getXRefs($node, 'type')) {
            if($node->hasAttribute('type')) { 
                $typeName = $node->getAttribute('type');
                if(substr($typeName, 0, 3) == 'xsd') {
                    $this->addBuiltInType($typeName);
                }
                $types = $this->query(
                    '//xsd:complexType[@name="'
                    . $typeName
                    . '"] | //xsd:simpleType[@name="'
                    . $typeName
                    . '"]'
                );
            } else {
                $typeName = "defined online";
                $types = $this->query(
                    './xsd:complexType | ./xsd:simpleType',
                    $node
                );
            }
            if($types->length == 0) throw new maarch\Exception("Type $typeName not found for " . $node->tagName . " " . $node->getAttribute('name'));
            $type = $types->item(0);
        //    $this->addXRefs($node, 'type', $type);
        //} 
        
        return $type;
    }
    
    protected function addBuiltInType($typeName)
    {
        $builtInType = $this->schema->createElement('xsd:simpleType');
        $builtInType->setAttribute('name', $typeName);
        
        // Define if enclosed or not
        $nonEnclosedTypes = array(
            'xsd:float',
            'xsd:double', 
            'xsd:decimal',
                'xsd:integer',
                    'xsd:nonPositiveInteger',
                        'xsd:negativeInteger',
                    'xsd:long',
                        'xsd:int', 
                            'xsd:short', 
                                'xsd:byte',
                    'xsd:nonNegativeInteger',
                        'xsd:positiveInteger',
                        'xsd:unsignedLong',
                            'xsd:unsignedInt',
                                'xsd:unsignedShort',
                                    'xsd:unsignedByte',
            'xsd:boolean',
        );
        $enclosedTypes = array(
            'xsd:string',
                'xsd:normalizedString',
                    'xsd:token',
                        'xsd:language',
                        'xsd:name',
                            'xsd:NCName',
                                'xsd:ID',
                                'xsd:IDREF',
                                'xsd:ENTITY',
                        'xsd:NMTOKEN',
            'xsd:QNAME',
            'xsd:NOTATION',
            'xsd:date',
            'xsd:time',
            'xsd:datetime',
            'xsd:gYear',
            'xsd:gYearMonth',
            'xsd:gMonth',
            'xsd:gMonthDay',
            'xsd:gDay',
            'xsd:duration',
            'xsd:base64binary',
            'xsd:hexBinary',
            'xsd:anyURI',
        );
        
        if(!in_array($typeName, $nonEnclosedTypes)) {
            $builtInType->setAttribute('das:enclosed', 'true');
        }
        $this->schema->appendChild($builtInType);
    }
    
    protected function getView($element)
    {
        if($element->hasAttribute('das:view')) {
            $viewName = $element->getAttribute('das:view');
            $views = 
                $this->query('//xsd:annotation/xsd:appinfo/das:view[@name="'
                    . $viewName
                    . '"]');
            if($views->length == 0) throw new maarch\Exception("Referenced view $viewName not found");
            return $views->item(0);
        } elseif($views = $this->query('./xsd:annotation/xsd:appinfo/das:view', $element)) {
            return $views->item(0);
        }
    }
    
    protected function getRelation(
        $objectElement, 
        $dataObject
    ) {
        $relations = $this->query(
            '//xsd:annotation/xsd:appinfo/das:relation['
            . '@parent="' . $dataObject->tagName
            . '" and @child="' . $objectElement->getName()
            . '"]'
        );
        if($relations->length == 0) return false;
        return $relations->item(0);
    }
    
    protected function getAttributes($node)
    {
        $attributes = $this->query('./xsd:attribute', $node);
        if($attributes->length == 0) return false;
        return $attributes;
    }
    
    protected function getGroup($node) 
    {
        $groups = $this->query('./xsd:group', $node);
        if($groups->length == 0) return false;
        return $groups->item(0);
    }
    
    protected function getSequence($node)
    {
        $sequences = $this->query('./xsd:sequence', $node);
        if($sequences->length == 0) return false;
        return $sequences->item(0);
    }
    
    protected function getAll($node)
    {
        $alls = $this->query('./xsd:all', $node);
        if($alls->length == 0) return false;
        return $alls->item(0);
    }
    
    protected function getAny($node)
    {
        $anys = $this->query('./xsd:any', $node);
        if($anys->length == 0) return false;
        return $anys->item(0);
    }
    
    protected function getElements($node)
    {
        $elements = $this->query('./xsd:element', $node);
        return $elements;
    }
    
    protected function getQuery($node) 
    {
        if($node->hasAttribute('das:query')) {
            $queryName = $node->getAttribute('das:query');
            $queryNode = $this->query(         
                '//das:query[@name="'
                . $queryName
                . '"]'
            )->item(0);
            return $queryNode;
        } elseif(
            $queryNode = $this->query(
                './xsd:annotation/xsd:appinfo/das:query'
                , $node
            )->item(0)
        ) {
            return $queryNode;
        }
    }
    
    
    //*************************************************************************
    // PARSE TYPE
    //*************************************************************************
    public function getObjectContents($objectElement)
    {
        if(!$objectContents = 
            $this->getXRefs($objectElement, 'contents')
        ) {
            $objectContents = array();
            $objectElement = $this->getRefNode($objectElement);
            $objectType = $this->getType($objectElement);
            $objectContents = $this->getTypeContents($objectType);
            $this->addXRefs(
                $objectElement,
                'contents',
                $objectContents
            );
        } 
        return $objectContents;
    }
    
    public function getTypeContents($type)
    {
        $typeContents = array();
        $typeChildren = $type->childNodes;
        $l = $typeChildren->length;
        for($i=0; $i<$l; $i++) {
            $typeChild = $typeChildren->item($i);   
            switch($typeChild->tagName) {
                // complexTypes
                case 'xsd:group':
                    break;
                    
                case 'xsd:all':
                    break;
                    
                case 'xsd:choice':
                    break;
                    
                case 'xsd:sequence':
                    $sequenceElements = 
                        $this->getSequenceElements(
                            $typeChild
                        );
                    
                    $typeContents = 
                        array_merge(
                            $typeContents, 
                            $sequenceElements
                        );
                    break;
                    
                case 'xsd:attribute':
                    $typeContents[] = $typeChild;
                    break;
                    
                case 'xsd:attributeGroup':
                    break;
                    
                case 'xsd:anyAttribute':
                    break;
            }
        }
        return $typeContents;
    }
    
    protected function getSequenceElements($sequence)
    {
        $sequenceElements = array();
        //any, choice, element, group, sequence
        $sequenceChildren = $sequence->childNodes;
        $l = $sequenceChildren->length;
        for($i=0; $i<$l; $i++) {
            $sequenceChild = $sequenceChildren->item($i);
            switch($sequenceChild->tagName) {
                case 'xsd:element':
                    $sequenceElements[] = $sequenceChild;
                    break;
                    
                case 'xsd:any':
                    break;
                
                case 'xsd:choice':
                        break;
                        
                case 'xsd:sequence':
                    $sequenceSequenceElements = 
                        $this->getSequenceElements(
                            $sequenceChild
                        );
                    $sequenceElements = 
                        array_merge(
                            $sequenceElements, 
                            $sequenceSequenceElements
                        );       
                    break;
            }
        }
        return $sequenceElements;
    }
        
    //*************************************************************************
    // CROSSED REFERENCES
    //*************************************************************************
    protected function getXRefs($element, $queryTag) 
    {
        //echo "<br/>get XRefs[" . $element->tagName . "][".$element->getName()."][$queryTag] => ";
        $XRefs = $this->XRefs[$element->tagName][$element->getName()][$queryTag];
        //echo count($XRefs); 
        return $XRefs;
    }
    
    protected function addXRefs($element, $queryTag, $XRefs) 
    {
        //echo "<br/>add XRefs[" . $element->tagName . "][".$element->getName()."][$queryTag] => " . count($XRefs);
        $this->XRefs[$element->tagName][$element->getName()][$queryTag] = $XRefs;
    }
   

}