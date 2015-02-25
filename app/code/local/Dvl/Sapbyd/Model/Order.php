<?php
class Dvl_Sapbyd_Model_Order extends Mage_Core_Model_Abstract
{
    /**
     * Resource model name that contains entities (names of tables)
     *
     * @var string
     */
    protected $_salesModelOrder;
    
    protected function _construct(){
        $this->_init('dvl_sapbyd/order');
    }
    
    public function saveOrder(){
        $etcPath = Mage::getConfig()->getModuleDir('etc', "Dvl_Sapbyd").DS;
        $mainConfig = new Zend_Config_Xml($etcPath . 'sapbyd_config.'.mage::helper("te_sapbydexport")->getEnvironment().'.xml');
        
        $mail = Mage::getModel('core/email');
        $mail->setToName('MAGENTO');
        $mail->setToEmail($mainConfig->adminEmail);
        $mail->setBody($this->getData('message'));
        $mail->setSubject('MAGENTO / SAP ByD Transaction Order');
        $mail->setFromEmail($mainConfig->magentoEmail);
        $mail->setFromName($mainConfig->magentoName);
        $mail->setType('text');// You can use Html or text as Mail format
        $mail->send();
        return $this->save();
    }
    
    public function setSalesModelOrder(Mage_Sales_Model_Order $salesOrder){
        $this->_salesModelOrder = $salesOrder;
        return $this;
    }
    
    public function getSalesModelOrder(){
        return $this->_salesModelOrder;
    }
    

}