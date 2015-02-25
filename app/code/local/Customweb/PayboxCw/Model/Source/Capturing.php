<?php
class Customweb_PayboxCw_Model_Source_Capturing{
	public function toOptionArray(){
		$options = array(
			array('value'=>'sale', 'label'=>Mage::helper('adminhtml')->__("Directly after order (sale)")),
			array('value'=>'authorization', 'label'=>Mage::helper('adminhtml')->__("Deferred (authorization)")),
			array('value'=>'order', 'label'=>Mage::helper('adminhtml')->__("Do only check the payment information (order)
						"))
		);
		return $options;
	}
}
