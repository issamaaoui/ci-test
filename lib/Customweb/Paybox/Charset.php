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

//require_once 'Customweb/Core/Charset/ISO88591.php';


class Customweb_Paybox_Charset extends Customweb_Core_Charset_ISO88591 {
	
	
	private static $notSupportedChars = array(
		"\xA0" => '1',
		"\xA2" => '1',
		"\xA3" => '1',
		"\xA4" => '1',
		"\xA5" => '1',
		"\xA6" => '1',
		"\xA7" => '1',
		"\xA8" => '1',
		"\xA9" => '1',
		"\xAA" => '1',
		"\xAC" => '1',
		"\xAD" => '1',
		"\xAE" => '1',
		"\xAF" => '1',
		"\xB0" => '1',
		"\xB1" => '1',
		"\xB2" => '1',
		"\xB3" => '1',
		"\xB4" => '1',
		"\xB5" => '1',
		"\xB6" => '1',
		"\xB7" => '1',
		"\xB8" => '1',
		"\xB9" => '1',
		"\xBA" => '1',
		"\xBC" => '1',
		"\xBD" => '1',
		"\xBE" => '1',
	);
	
	private static $effectiveConversionTable = null;

	protected function getConversionTable() {
		if (self::$effectiveConversionTable === null) {
			self::$effectiveConversionTable = array();
			foreach (parent::getConversionTable() as $isoChar => $utf8Char) {
				if (!isset(self::$notSupportedChars[$isoChar])) {
					self::$effectiveConversionTable[$isoChar] = $utf8Char;
				}
			}
		}
		return self::$effectiveConversionTable;
	}
	

	public function getName() {
		return 'Paybox-Charset';
	}
	
	public function getAliases() {
		return array();
	}
	
	
}