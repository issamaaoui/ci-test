<?php
class Dvl_Sapbyd_Model_Customer extends Mage_Core_Model_Abstract
{
    protected function _construct(){
        $this->_init('dvl_sapbyd/customer');
    }
    
    public function saveCustomer(){
        $etcPath = Mage::getConfig()->getModuleDir('etc', "Dvl_Sapbyd").DS;
        $mainConfig = new Zend_Config_Xml($etcPath . 'sapbyd_config.'.mage::helper("te_sapbydexport")->getEnvironment().'.xml');
        
        $mail = Mage::getModel('core/email');
        $mail->setToName('MAGENTO');
        $mail->setToEmail($mainConfig->adminEmail);
        $mail->setBody($this->getData('message'));
        $mail->setSubject('MAGENTO / SAP ByD Transaction Customer');
        $mail->setFromEmail($mainConfig->magentoEmail);
        $mail->setFromName($mainConfig->magentoName);
        $mail->setType('text');// YOu can use Html or text as Mail format
        $mail->send();
        return $this->save();
    }

}