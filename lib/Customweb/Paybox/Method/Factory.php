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
 */

//require_once 'Customweb/Core/Util/Class.php';
//require_once 'Customweb/Paybox/Method/DefaultMethod.php';
//require_once 'Customweb/Paybox/Method/Util.php';



final class Customweb_Paybox_Method_Factory {
	
	
	private function __construct() {}
	
	/**
	 * 
	 * @return Customweb_Paybox_Method_DefaultMethod
	 */
	public static function getMethod(Customweb_Payment_Authorization_IPaymentMethod $method, Customweb_Paybox_Configuration $config) {
		$machineName = strtolower($method->getPaymentMethodName());
		
		$map = Customweb_Paybox_Method_Util::getPaymentMethodMap();
		
		if (!isset($map[$machineName])) {
			throw new Exception("The payment method with name '" . $machineName . "' could not be found.");
		}
		
		if (isset($map[$machineName]['method_class'])) {
			$className = $map[$machineName]['method_class'];
			Customweb_Core_Util_Class::loadLibraryClassByName($className);
			return new $className($method, $config);
		}
		else {
			return new Customweb_Paybox_Method_DefaultMethod($method, $config);
		}
	}
	
}