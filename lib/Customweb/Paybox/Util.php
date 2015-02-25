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

//require_once 'Customweb/Core/String.php';
//require_once 'Customweb/Core/Charset.php';


class Customweb_Paybox_Util {
	
	private static $encodingRegistered = false;
	
	private function __construct() {
		
	}
	
	/**
	 * Converts a UTF-8 string to the Paybox encoding. 
	 * 
	 * @param string $input
	 */
	public static function convert($input) {
		if (self::$encodingRegistered === false) {
			Customweb_Core_Charset::registerCustomEncoding('Paybox-Charset', 'Customweb_Paybox_Charset');
			self::$encodingRegistered = true;
		}
		return Customweb_Core_String::_($input)->convertTo('Paybox-Charset')->toString();
	}
	
}