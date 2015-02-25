<?php
Class Te_Ajaxcart_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getAjaxUrl()
	{
		return mage::getUrl("ajaxcart/cart/formPost");
	}

	public function getIndexAjaxUrl()
	{
		return mage::getUrl("ajaxcart/cart/index");
	}

	public function getApplyCouponAjaxUrl()
	{
		return mage::getUrl("ajaxcart/cart/couponPost");
	}

}
