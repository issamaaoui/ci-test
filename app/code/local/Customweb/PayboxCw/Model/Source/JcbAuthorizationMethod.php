<?php
class Customweb_PayboxCw_Model_Source_JcbAuthorizationMethod{
	public function toOptionArray(){
		$options = array(
			array('value'=>'PaymentPage', 'label'=>Mage::helper('adminhtml')->__("Payment Page (Payment Page)")),
			array('value'=>'ServerAuthorization', 'label'=>Mage::helper('adminhtml')->__("Server Authorization (Server Authorisation)"))
		);
		return $options;
	}
}
