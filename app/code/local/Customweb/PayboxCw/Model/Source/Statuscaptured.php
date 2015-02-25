<?php
class Customweb_PayboxCw_Model_Source_Statuscaptured{
	public function toOptionArray(){
		$options = array(
			array('value'=>'no_status_change', 'label'=>Mage::helper('adminhtml')->__("Don't change order status"))
		);
		$statuses = Mage::getSingleton('sales/order_config')->getStatuses();
		foreach ($statuses as $code=>$label) {
			$options[] = array(
				'value' => $code,
				'label' => $label
			);
		}
		return $options;
	}
}
