<?php
class Customweb_PayboxCw_Model_Source_OneyCurrency{
	public function toOptionArray(){
		$options = array(
			array('value'=>'EUR', 'label'=>Mage::helper('adminhtml')->__("Euro (EUR)")),
			array('value'=>'GBP', 'label'=>Mage::helper('adminhtml')->__("Pound sterling (GBP)")),
			array('value'=>'USD', 'label'=>Mage::helper('adminhtml')->__("United States dollar (USD)")),
			array('value'=>'CHF', 'label'=>Mage::helper('adminhtml')->__("Swiss franc (CHF)")),
			array('value'=>'CAD', 'label'=>Mage::helper('adminhtml')->__("Canadian dollar (CAD)")),
			array('value'=>'NZD', 'label'=>Mage::helper('adminhtml')->__("New Zealand dollar (NZD)"))
		);
		return $options;
	}
}
