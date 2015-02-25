<?php
class Customweb_PayboxCw_Model_Source_Operationmode{
	public function toOptionArray(){
		$options = array(
			array('value'=>'live', 'label'=>Mage::helper('adminhtml')->__("Live Mode")),
			array('value'=>'test', 'label'=>Mage::helper('adminhtml')->__("Test Mode "))
		);
		return $options;
	}
}
