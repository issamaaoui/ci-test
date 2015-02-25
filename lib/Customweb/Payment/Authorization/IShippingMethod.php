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


/**
 * Represents a shipping method. A shipping method indicates how a 
 * item is shipped.
 * 
 * @author Thomas Hunziker
 *
 */
interface Customweb_Payment_Authorization_IShippingMethod {
	
	
	/**
	 * Returns the name of the shipping name. The name is visible for 
	 * the user. It must be translated into the language of the customer.
	 * 
	 * @return string
	 */
	public function getShippingName();
	
	/**
	 * Returns the machine name of the shipping method. The machine name 
	 * should only consists of ASCII chars. It is used to identify the shipping
	 * method.
	 * 
	 * @return string
	 */
	public function getShippingMachineName();
	
	/**
	 * Returns the amount charged additioanlly for this shipping method.
	 *
	 * @return float Shipping fees (in order currency)
	 */
	public function getShippingFees();
	
	
}