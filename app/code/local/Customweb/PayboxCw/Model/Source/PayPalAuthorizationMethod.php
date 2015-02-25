<?php
class Customweb_PayboxCw_Model_Source_PayPalAuthorizationMethod{
	public function toOptionArray(){
		$options = array(
			array('value'=>'PaymentPage', 'label'=>Mage::helper('adminhtml')->__("Payment Page (Payment Page)"))
		);
		return $options;
	}
}
