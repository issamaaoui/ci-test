<?php
class Customweb_PayboxCw_Model_Source_Settlement{
	public function toOptionArray(){
		$options = array(
			array('value'=>'settlement_deferred', 'label'=>Mage::helper('adminhtml')->__("Deferred settlement")),
			array('value'=>'settlement_direct', 'label'=>Mage::helper('adminhtml')->__("Settlement after order"))
		);
		return $options;
	}
}
