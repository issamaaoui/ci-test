<?php
class PWS_ProductRegistration_Block_Customer_RegisteredProducts_History extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('pws_productregistration/customer/registeredproducts/history.phtml');

        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $product_id  = NULL;
        $registeredProductsCollection = Mage::getModel('pws_productregistration/productregistration')
            ->getCollection()->addFieldToFilter('customer_id', $customer_id)->joinCustomers()->joinProducts();


        $this->setRegisteredProducts($registeredProductsCollection);
		Mage::app()->getFrontController()->getAction()->getLayout()
            ->getBlock('root')
            ->setHeaderTitle(Mage::helper('pws_productregistration')->getRegisteredProductsTitle());

    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'productregistration.customer.registeredproducts.history.pager')
            ->setCollection($this->getRegisteredProducts());
        $this->setChild('pager', $pager);
        $this->getRegisteredProducts()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
