<?php
class Te_Coupon_Model_Observer
{
	/**
     * Observes core_block_abstract_to_html_before
     *
     * Adds a new tab to customer edit view and register product button
     *
     * @param   Varien_Event_Observer $observer array('block' => $this)
     * @return  PWS_ProductRegistration_Model_Observer
     */
    public function applyCoupon($observer)
    {
		
        $session = Mage::getSingleton('customer/session');
       // die($session->getCouponPromo());
        $params = Mage::app()->getRequest()->getParams();
       
       if(isset($params["coupon_promo"]) && strlen(trim($params["coupon_promo"])) >0 )
       {
       		$coupon_promo = trim($params["coupon_promo"]);
       		$websiteId = mage::app()->getStore()->getWebsite()->getId();


       		$rules = Mage::getResourceModel("salesrule/rule_collection")
       		 ->setValidationFilter($websiteId, $session->getCustomerGroupId(), $coupon_promo);
       		if($rules->getSize()>0)
       		{
       			$session->setCouponPromo($coupon_promo);
	       		$successMsg = mage::helper("checkout")->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($coupon_promo));
	       		mage::getSingleton("core/session")->addSuccess($successMsg);
       		}else{
       			$errorMsg = mage::helper("checkout")->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($coupon_promo));
       			mage::getSingleton("core/session")->addError($errorMsg);
       		}

       		
       }
     
       if($session->getCouponPromo() && Mage::getSingleton('checkout/session')->getQuote()->getItemsCount() >0)
       {

       		$quote = Mage::getSingleton('checkout/session')->getQuote();
       		//die($session->getCouponPromo()." - ".$quote->getCouponCode());
       		$quote->getShippingAddress()->setCollectShippingRates(true);
       		$quote->setCouponCode($session->getCouponPromo())->collectTotals()->save();
       		
       		
       		
       		$session->unsetData("coupon_promo");

       }
    }


}