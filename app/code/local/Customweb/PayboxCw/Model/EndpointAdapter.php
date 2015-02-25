<?php
//require_once 'Customweb/PayboxCw/Model/ConfigurationAdapter.php';
//require_once 'Customweb/Payment/Endpoint/AbstractAdapter.php';


/**
 * @Bean
 */
class Customweb_PayboxCw_Model_EndpointAdapter extends Customweb_Payment_Endpoint_AbstractAdapter
{
	protected function getBaseUrl() {
		return Mage::getUrl('PayboxCw/endpoint/index', array('_store' => Customweb_PayboxCw_Model_ConfigurationAdapter::getStoreId()));
	}
	
	protected function getControllerQueryKey() {
		return 'c';
	}
	
	protected function getActionQueryKey() {
		return 'a';
	}
	
	public function getFormRenderer() {
		return Mage::getModel('payboxcw/formRenderer');
	}
}