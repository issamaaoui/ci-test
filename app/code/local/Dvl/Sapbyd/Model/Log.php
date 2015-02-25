<?php
class Dvl_Sapbyd_Model_Log extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('dvl_sapbyd/log');
    }

    public function saveLog(){
        $etcPath = Mage::getConfig()->getModuleDir('etc', "Dvl_Sapbyd").DS;
        $mainConfig = new Zend_Config_Xml($etcPath . 'sapbyd_config.'.mage::helper("te_sapbydexport")->getEnvironment().'.xml');
        $recipients = mage::helper("te_sapbydexport")->getEmailRecipients();
        $addTo = explode(";", $recipients);
          
        $mail = new Zend_Mail();
        $mail->addTo($addTo[0],'MAGENTO');
        for($i=1;$i<count($addTo);$i++)
        {
            $mail->addBcc($addTo[$i]);
        }

        $mail->setBodyText($this->getData('message') . chr(13) . chr(13) . '--------------' . chr(13) . chr(13) . $this->getData('response'));
        $mail->setSubject('LOG : MAGENTO / SAP ByD Transaction');
        $mail->setFrom($mainConfig->magentoEmail,$mainConfig->magentoName);
        //$mail->setType(Zend_Mime::TYPE_TEXT);// Html or text
        $mail->send();
        return $this->save();
    }
}