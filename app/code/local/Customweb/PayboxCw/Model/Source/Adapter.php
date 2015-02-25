<?php
/**
 * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2013 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.customweb.ch/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.customweb.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category	Customweb
 * @package		Customweb_PayboxCw
 * @version		1.0.49
 */

class Customweb_PayboxCw_Model_Source_Adapter
{
	private $adapterOptions = null;

	public function toOptionArray()
	{
		return $this->getSupportedAuthorizationAdapters();
	}

	private function getSupportedAuthorizationAdapters()
	{
		if ($this->adapterOptions == null) {
			$this->adapterOptions = array();
			$adapterNames = array(
				Customweb_Payment_Authorization_Hidden_IAdapter::AUTHORIZATION_METHOD_NAME,
				Customweb_Payment_Authorization_PaymentPage_IAdapter::AUTHORIZATION_METHOD_NAME,
				Customweb_Payment_Authorization_Server_IAdapter::AUTHORIZATION_METHOD_NAME,
				Customweb_Payment_Authorization_Ajax_IAdapter::AUTHORIZATION_METHOD_NAME,
				Customweb_Payment_Authorization_Iframe_IAdapter::AUTHORIZATION_METHOD_NAME,
			);
			foreach ($adapterNames as $name) {
				$this->adapterOptions[] = array(
					'value' => $name,
					'label' => Mage::helper('adminhtml')->__($name)
				);
			}
		}

		return $this->adapterOptions;
	}
}
