<?php
class Te_Coupon_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getDiscountAmount()
    {



        $discount = 0;
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach ($quote->getAllItems() as $item){
            $discount += $item->getDiscountAmount();
        }

		return $discount;
    }
}
