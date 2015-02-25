<?php
class Customweb_PayboxCw_Model_Source_Statusauthorized{
	public function toOptionArray(){
		$options = array(

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
