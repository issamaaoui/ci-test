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

class Customweb_PayboxCw_EndpointController extends Customweb_PayboxCw_Controller_Action
{
	public function indexAction()
	{
		$container = Mage::helper('PayboxCw')->createContainer();
		$packages = array(
			0 => 'Customweb_Paybox',
 			1 => 'Customweb_Payment_Authorization',
 		);
		$adapter = Mage::getModel('payboxcw/endpointAdapter');
		
		$dispatcher = new Customweb_Payment_Endpoint_Dispatcher($adapter, $container, $packages);
		$response = $dispatcher->dispatch(Customweb_Core_Http_ContextRequest::getInstance());
		$wrapper = new Customweb_Core_Http_Response($response);
		$wrapper->send();
		die();
	}
}