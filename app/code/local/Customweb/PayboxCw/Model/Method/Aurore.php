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
 * @category	Local
 * @package		Customweb_PayboxCw
 * @link		http://www.customweb.ch
 */

class Customweb_PayboxCw_Model_Method_Aurore extends Customweb_PayboxCw_Model_Method
implements Mage_Payment_Model_Recurring_Profile_MethodInterface
{
	protected $_code = 'payboxcw_aurore';
	protected $paymentMethodName = 'aurore';
	
	protected function getMultiSelectKeys(){
		$multiSelectKeys = array(
			'specificcountry' => 'specificcountry',
 			'Currency' => 'Currency',
 		);;
		return $multiSelectKeys;
	}
	
	protected function getFileKeys(){
		$fileKeys = array(
		);;
		return $fileKeys;
	}

	
	public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile){
		Mage::log("validateRecurringProfile : " . print_r($profile,true));
		return true;
	}
	
	
	/**
	 * Submit to the gateway
	 *
	 * @param Mage_Payment_Model_Recurring_Profile $profile
	 * @param Mage_Payment_Model_Info $paymentInfo
	*/
	public function submitRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile, Mage_Payment_Model_Info $paymentInfo){
		Mage::log("submitRecurringProfile" . print_r($profile, true) . print_r($paymentInfo,true));
		return true;
	}
	
	/**
	 * Fetch details
	 *
	 * @param string $referenceId
	 * @param Varien_Object $result
	*/
	public function getRecurringProfileDetails($referenceId, Varien_Object $result){
		return $this;
	}
	
	/**
	 * Check whether can get recurring profile details
	 *
	 * @return bool
	*/
	public function canGetRecurringProfileDetails(){
		return false;
	}
	
	/**
	 * Update data
	 *
	 * @param Mage_Payment_Model_Recurring_Profile $profile
	*/
	public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile){
		Mage::log("updateRecurringProfile" . print_r($profile, true));
	}
	
	/**
	 * Manage status
	 *
	 * @param Mage_Payment_Model_Recurring_Profile $profile
	*/
	public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile){
		Mage::log("updateRecurringProfileStatus" . print_r($profile,true));
	}
	
}
