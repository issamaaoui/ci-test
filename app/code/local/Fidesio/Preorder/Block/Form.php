<?php
class Fidesio_Preorder_Block_Form extends Mage_Core_Block_Template
{
    /**
     * Initialize block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('preorder/form.phtml');
    }

    public function getCodeAction()
    {
        return Mage::getUrl('preorder/index/codePost', array('_secure'=>true));
    }
    public function getEmailAction()
    {
        return Mage::getUrl('preorder/index/emailPost', array('_secure'=>true));
    }

    /**
     * @return mixed
     */
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Retrieve username for form field
     * @return string
     */
    public function getUsername()
    {
        return Mage::getSingleton('customer/session')->getUsername(true);
    }
}