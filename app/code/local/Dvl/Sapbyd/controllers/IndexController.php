<?php

/**
 * Odura
 *
 * @category    Dvl
 * @package     Dvl_Sapbyd
 * @copyright   
 * @license     
 */

class Dvl_Sapbyd_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {   
        $etcPath = Mage::getConfig()->getModuleDir('etc', "Dvl_Sapbyd").DS;
        $mainConfig = new Zend_Config_Xml($etcPath . 'sapbyd_config.xml');
        $mail = Mage::getModel('core/email');
        $mail->setToName('MAGENTO');
        $mail->setToEmail($mainConfig->adminEmail);
        $mail->setBody('Test envoi email');
        $mail->setSubject('MAGENTO / SAP ByD Transaction Customer');
        $mail->setFromEmail($mainConfig->magentoEmail);
        $mail->setFromName($mainConfig->magentoName);
        $mail->setType('text');// YOu can use Html or text as Mail format
        $mail->send();
        die("email test sent by " . $mainConfig->magentoName . " (" . $mainConfig->magentoEmail . ") to " . $mainConfig->adminEmail);
    }
}