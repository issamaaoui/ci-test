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


final class  Customweb_Util_Address {
	
	private function  __construct(){}

	public static function splitStreet($street, $countryIsoCode, $zipCode){
		// TODO Correctly splitting the street from the street number is quite elaborate
		// we just assume that the street number is the last part of the street, this is
		// certainly not true for many countries.
		// i.e. US addresses "222314, 32 Rd",
		// combined numbers "Samplestreet A 12" , ....
		$result = array();
		if(preg_match("/(.*)\s([^\s]+)$/", trim($street), $match)){
			$result['street'] = $match[1];
			$result['street-number'] = $match[2];
		}
		else{
			$result['street'] = $street;
			$result['street-number'] = '';
		}
		return $result;
	}
	
	/**
	 * This method takes the two order contexts and compare their shipping addresses. The 
	 * method returns true, when they equal.
	 * 
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext1
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext2
	 * @return boolean
	 */
	public static function compareShippingAddresses(Customweb_Payment_Authorization_IOrderContext $orderContext1, Customweb_Payment_Authorization_IOrderContext $orderContext2) {
		
		if ($orderContext1->getShippingCity() != $orderContext2->getShippingCity()) {
			return false;
		}
		if ($orderContext1->getShippingCompanyName() != $orderContext2->getShippingCompanyName()) {
			return false;
		}
		if ($orderContext1->getShippingCountryIsoCode() != $orderContext2->getShippingCountryIsoCode()) {
			return false;
		}
		if ($orderContext1->getShippingFirstName() != $orderContext2->getShippingFirstName()) {
			return false;
		}
		if ($orderContext1->getShippingLastName() != $orderContext2->getShippingLastName()) {
			return false;
		}
		if ($orderContext1->getShippingPostCode() != $orderContext2->getShippingPostCode()) {
			return false;
		}
		if ($orderContext1->getShippingStreet() != $orderContext2->getShippingStreet()) {
			return false;
		}
		
		return true;
	}

}