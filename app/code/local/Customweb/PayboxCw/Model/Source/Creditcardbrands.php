<?php
class Customweb_PayboxCw_Model_Source_Creditcardbrands{
	public function toOptionArray(){
		$options = array(
			array('value'=>'visa', 'label'=>Mage::helper('adminhtml')->__("VISA")),
			array('value'=>'mastercard', 'label'=>Mage::helper('adminhtml')->__("MasterCard")),
			array('value'=>'americanexpress', 'label'=>Mage::helper('adminhtml')->__("American Express")),
			array('value'=>'diners', 'label'=>Mage::helper('adminhtml')->__("Diners Club")),
			array('value'=>'jcb', 'label'=>Mage::helper('adminhtml')->__("JCB"))
		);
		return $options;
	}
}
