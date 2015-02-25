<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 11 févr. 2015
 *
**/

class Te_Sapbyd_Helper_Soap extends Mage_Core_Helper_Abstract
{
    const XML_SAPBYD_SOAP_DEBUG = 'te_sapbyd/soap/debug';
    const XML_SAPBYD_SOAP_LOGIN = 'te_sapbyd/soap/login';
    const XML_SAPBYD_SOAP_PASSWORD = 'te_sapbyd/soap/password';
    const XML_SAPBYD_SOAP_WSDL_DIR = 'te_sapbyd/soap/wsdl_dir';


    public function getLogin()
    {
        return Mage::getStoreConfig(self::XML_SAPBYD_SOAP_LOGIN);
    }

    public function getPassword()
    {
        return Mage::getStoreConfig(self::XML_SAPBYD_SOAP_PASSWORD);
    }

    public function getWsdlPath()
    {
        return Mage::getStoreConfig(self::XML_SAPBYD_SOAP_WSDL_DIR);
    }

    public function isLogEnabled()
    {
        return Mage::getStoreConfig(self::XML_SAPBYD_SOAP_DEBUG);
    }
}
