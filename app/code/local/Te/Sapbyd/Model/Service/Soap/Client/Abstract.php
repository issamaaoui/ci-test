<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 4 févr. 2015
 *
**/

abstract class Te_Sapbyd_Model_Service_Soap_Client_Abstract extends Zend_Soap_Client
{
    protected $_mainNode;

    /**
     * Constructor
     *
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl = null)
    {
        if (!extension_loaded('soap')) {
            throw new Zend_Soap_Client_Exception('SOAP extension is not loaded.');
        }

        if (is_array($wsdl) && isset($wsdl['wsdl'])) {
            $wsdl = $wsdl['wsdl'];
        } elseif (!is_string($wsdl)) {
            $wsdl = $this->_getDefaultWsdl();
        }

        if (!file_exists($wsdl)) {
            throw new Zend_Soap_Client_Exception("File '{$wsdl}' cannot be found.");
        }

        $this->setWsdl($wsdl);
        $this->setOptions(array(
            'login'     => Mage::helper('te_sapbyd/soap')->getLogin(),
            'password'  => Mage::helper('te_sapbyd/soap')->getPassword(),
        ));
    }

    /**
     *
     * @return string
     */
    protected function _getDefaultWsdl()
    {
        $path = Mage::helper('te_sapbyd/soap')->getWsdlPath();
        return $path . DS . substr(strrchr(get_class($this), '_'), 1) . '.wsdl';
    }

    /**
     * Perform result pre-processing
     *
     * @param array $result
     */
    final protected function _preProcessResult($result)
    {
        if (Mage::helper('te_sapbyd/soap')->isLogEnabled()) {
            $dir = Mage::getBaseDir('log'). '/soap/';
            if (Mage::getConfig()->getOptions()->createDirIfNotExists($dir)) {
                simplexml_load_string($this->getLastRequest())
                    ->saveXML($dir . substr(strrchr(get_class($this), '_'), 1) . '_request.xml');
                simplexml_load_string($this->getLastResponse())
                    ->saveXML($dir . substr(strrchr(get_class($this), '_'), 1) . '_response.xml');
            }
        }

        $result = Mage::getModel('te_sapbyd/service_soap_response', $result);
        if ($this->_mainNode && property_exists($result, $this->_mainNode)) {
            return $this->_getFormattedResponse($result->{$this->_mainNode});
        }

        return $this->_getFormattedResponse($result);
    }

    /**
     * Formatting result
     *
     * @param array $result
     */
    protected function _getFormattedResponse($result)
    {
        return $result;
    }
}