<?php
class Dvl_Sapbyd_Model_Soap extends SoapClient
{
    /**
     * Model name for request
     *
     * @var string
     */
    var $modelName = null;

    /**
     * Model type for request
     *
     * @var string
     */
    var $modelType = null; 
      
    /**
     * Model Config xml
     *
     * @var Zend_Config_Xml
     */
    public $modelConfig;

    /**
     * Model Data xml
     *
     * @var Zend_Config_Xml
     */
    public $modelData;  

    /**
     * Model Response xml
     *
     * @var Zend_Config_Xml
     */
    public $response;

    /**
     * Model Response Conditions xml
     *
     * @var Zend_Config_Xml
     */
    public $responseConditions;    
    
    /**
     * Model xml
     *
     * @var Zend_Config_Xml
     */
    public $mainConfig;    
    
    var $location;
    
    var $action;

    
    /**
     * Constructor
     *
     * @param none
     */
    public function __construct(){
        $modelName = $this->modelName;
        $modelType = $this->modelType;
        $etcPath = Mage::getConfig()->getModuleDir('etc', "Dvl_Sapbyd").DS;
        
        /* Webservice Config */
        // location
        $this->mainConfig = new Zend_Config_Xml($etcPath . 'sapbyd_config.'.mage::helper("te_sapbydexport")->getEnvironment().'.xml');
        $this->modelConfig = $this->mainConfig->models->$modelName->$modelType;
        $this->location = $this->mainConfig->location->url . $this->modelConfig->url . "?sap_vhost=" . $this->mainConfig->location->params->sap_vhost;
        
        // action
        $this->action = $this->modelConfig->action;
        
        //options
        $options = array(
            'login'         => $this->mainConfig->login,
            'password'      => $this->mainConfig->password,
            'location'      => $this->location,
            'uri'           => $this->action,
            'encoding'      => 'UTF-8',
            'use'           => SOAP_LITERAL,
            'style'         => SOAP_DOCUMENT
        );
        
        // Model Config
        $modelFile = $etcPath . 'models' . DS . $this->modelName . '_' . $this->modelType . '.xml';
        $this->modelData = new Zend_Config_Xml($modelFile, null , array('allowModifications' => true));

        parent::__construct(null, $options);
    }
    
    public function request(){
        $this->beforeRequest();
        $request = $this->renderRequest();
        $resultRequest = $this->__doRequest($request, $this->location, $this->action, SOAP_1_1);
       
        $document = new SimpleXMLElement($resultRequest);
        $result = $document->xpath('//' . $this->modelConfig->responsedom);
        if(empty($result)){
            $result[0] = '<errorsoap>' . print_r($resultRequest, true) . '</errorsoap>';
        }

        if($result[0] instanceof SimpleXMLElement){
            $response = $result[0];
            $this->response = new Zend_Config_Xml('<?xml version="1.0"?>' . $response->asXML());
        }else{
            $this->response = $result[0];
        }
        $this->response = $this->afterRequest($this->response, $request);
        return $this->response;
    }
    
    public function renderRequest(){
        
        $this->beforeRenderRequest();
        
        $xml         = new SimpleXMLElement('<glob_request></glob_request>');
        $extends     = $this->modelData->getExtends();
        $sectionName = 'glob_inner';
        
        if (is_string($sectionName)) {
            $child = $xml->addChild($sectionName);
            $this->_addBranch($this->modelData, $child, $xml);
        } else {
            foreach ($this->modelData as $sectionName => $data) {
                if (!($data instanceof Zend_Config)) {
                    $xml->addChild($sectionName, (string) $data);
                } else {
                    $child = $xml->addChild($sectionName);
                    $this->_addBranch($data, $child, $xml);
                }
            }
        }
        
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        
        $xmlString = $dom->saveXML();
        $xmlString = str_replace('<?xml version="1.0"?>','',$xmlString);
        $xmlString = str_replace('<glob_request>','',$xmlString);
        $xmlString = str_replace('</glob_request>','',$xmlString);
        $xmlString = str_replace('<glob_inner>','',$xmlString);
        $xmlString = trim(str_replace('</glob_inner>','',$xmlString));
        $xmlRequest = trim($this->renderRequestSoap($xmlString));
        
        $xmlRequest = $this->afterRenderRequest($xmlRequest);

        return $xmlRequest;
    }

    public function beforeRequest(){
    
    }
    
    public function afterRequest($response, $request = null){
        return $response;
    }
    
    public function beforeRenderRequest(){
        
    }
    
    public function afterRenderRequest($xmlRequest){
        return $xmlRequest;
    }
    
    public function renderRequestSoap($xmlString = ""){
        $modelName = $this->modelName;
        $modelType = $this->modelType;
        $envelope = $this->mainConfig->models->$modelName->$modelType->envelope;
        $xmlResult = "<soapenv:Envelope " . $envelope . ">\n";
        $xmlResult .= "<soapenv:Header/>\n";
        $xmlResult .= "<soapenv:Body>\n";
        $xmlResult .= "<glob:" . $this->mainConfig->models->$modelName->$modelType->glob . ">\n";
        $xmlResult .= $xmlString;
        $xmlResult .= "</glob:" . $this->mainConfig->models->$modelName->$modelType->glob . ">\n";
        $xmlResult .= "</soapenv:Body>\n";
        $xmlResult .= "</soapenv:Envelope>\n";
        return $xmlResult;
    }
    
    protected function _addBranch(Zend_Config $config, SimpleXMLElement $xml, SimpleXMLElement $parent)
    {
        $branchType = null;
    
        foreach ($config as $key => $value) {
            if ($branchType === null) {
                if (is_numeric($key)) {
                    $branchType = 'numeric';
                    $branchName = $xml->getName();
                    $xml        = $parent;
    
                    unset($parent->{$branchName});
                } else {
                    $branchType = 'string';
                }
            } else if ($branchType !== (is_numeric($key) ? 'numeric' : 'string')) {
                #require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception('Mixing of string and numeric keys is not allowed');
            }
    
            if ($branchType === 'numeric') {
                if ($value instanceof Zend_Config) {
                    $child = $parent->addChild($branchName);
    
                    $this->_addBranch($value, $child, $parent);
                } else {
                    $parent->addChild($branchName, (string) $value);
                }
            } else {
                if ($value instanceof Zend_Config) {
                    $child = $xml->addChild($key);
    
                    $this->_addBranch($value, $child, $xml);
                } else {
                    $xml->addChild($key, (string) $value);
                }
            }
        }
    }
}