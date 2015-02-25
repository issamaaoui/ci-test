<?php
class Customweb_PayboxCw_Model_Source_Aliasmanager{
	public function toOptionArray(){
		$options = array(
			array('value'=>'active', 'label'=>Mage::helper('adminhtml')->__("Active")),
			array('value'=>'inactive', 'label'=>Mage::helper('adminhtml')->__("Inactive"))
		);
		return $options;
	}
}
