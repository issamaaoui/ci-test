<?php
class Customweb_PayboxCw_Model_Source_Brandselection{
	public function toOptionArray(){
		$options = array(
			array('value'=>'active', 'label'=>Mage::helper('adminhtml')->__("Yes, use images for the brand selection.
						")),
			array('value'=>'inactive', 'label'=>Mage::helper('adminhtml')->__("No, use the dropdown."))
		);
		return $options;
	}
}
