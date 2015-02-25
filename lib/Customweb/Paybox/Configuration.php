<?php

/**
 *  * You are allowed to use this API in your web application.
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

//require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';
//require_once 'Customweb/I18n/Translation.php';



class Customweb_Paybox_Configuration {
	
	/**
	 *           	   	  	 	  
	 * 
	 * @var Customweb_Payment_IConfigurationAdapter
	 */
	private $configurationAdapter = null;
	private $version = "1.6";

	public function __construct(Customweb_Payment_IConfigurationAdapter $configurationAdapter){
		$this->configurationAdapter = $configurationAdapter;
	}
	private $handler;

	/**
	 * Returns whether the gateway is in test mode or in live mode.
	 *           	   	  	 	  
	 * 
	 * @return boolean True if the system is in test mode. Else return false.
	 */
	public function isLiveMode(){
		return $this->getConfigurationAdapter()->getConfigurationValue('operation_mode') == 'live';
	}

	public function getPayboxUrl(){
		if ($this->isLiveMode()) {
			return "https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi";
		}
		else {
			return "https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi";
		}
	}

	public function getPayboxServerUrl(){
		if ($this->isLiveMode()) {
			return "https://ppps.paybox.com/PPPS.php";
		}
		else {
			return "https://preprod-ppps.paybox.com/PPPS.php";
		}
	}

	public function getPayboxRemoteMpiUrl(){
		if ($this->isLiveMode()) {
			return "https://tpeweb.paybox.com/cgi/RemoteMPI.cgi";
		}
		else {
			return "https://preprod-tpeweb.paybox.com/cgi/RemoteMPI.cgi";
		}
	}

	public function getRangNumber(Customweb_Paybox_Authorization_Transaction $transaction){
		if ($this->isLiveMode()) {
			$rank = $this->getConfigurationAdapter()->getConfigurationValue('paybox_rang');
		}
		else {
			$parameters = $this->getTestParameters($transaction);
			$rank = $parameters['rang'];
		}
		
		$rank = trim($rank);
		
		if (empty($rank)) {
			throw new Exception(Customweb_I18n_Translation::__("The given rang is empty. It must be a nummeric value with length of two."));
		}
		
		if (!preg_match('/^[0-9]{2}$/', $rank)) {
			throw new Exception(Customweb_I18n_Translation::__("The given rang is invalid. It must be a nummeric value with length of two."));
		}
		
		return $rank;
	}

	public function getSiteNumber(Customweb_Paybox_Authorization_Transaction $transaction){
		if ($this->isLiveMode()) {
			return $this->getConfigurationAdapter()->getConfigurationValue('paybox_site');
		}
		else {
			$parameters = $this->getTestParameters($transaction);
			return $parameters['site'];
		}
	}

	public function getPayboxIdentifier(Customweb_Paybox_Authorization_Transaction $transaction){
		if ($this->isLiveMode()) {
			return $this->getConfigurationAdapter()->getConfigurationValue('paybox_identifier');
		}
		else {
			$parameters = $this->getTestParameters($transaction);
			return $parameters['identifier'];
		}
	}

	public function getOrderIdSchema(){
		return $this->getConfigurationAdapter()->getConfigurationValue('order_id_schema');
	}

	public function getPassword(Customweb_Paybox_Authorization_Transaction $transaction){
		if ($this->isLiveMode()) {
			return $this->getConfigurationAdapter()->getConfigurationValue('paybox_shop_password');
		}
		else {
			$parameters = $this->getTestParameters($transaction);
			return $parameters['password'];
		}
	}

	public function getHmacKey(){
		if ($this->isLiveMode()) {
			return $this->getConfigurationAdapter()->getConfigurationValue('paybox_hmac_key');
		}
		else {
			//return '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
			return 'BA60EE41E3D20955BE2D64F6F16DF8245A5D7462ACB5BAAE9F3995E7020184B7802B4C6DC3EB11E0924ABE17313EF75B4012E438A278C930CDA9960A36975308';
		}
	}

	/**
	 *
	 * @return Customweb_Payment_IConfigurationAdapter
	 */
	public function getConfigurationAdapter(){
		return $this->configurationAdapter;
	}

	public function getVersion(){
		return $this->version;
	}

	private function getTestParameters(Customweb_Paybox_Authorization_Transaction $transaction){
	    if ($transaction->getAuthorizationMethod() == Customweb_Payment_Authorization_PaymentPage_IAdapter::AUTHORIZATION_METHOD_NAME) {
	        return array(
	            'rang' => "01",
	            'site' => "4715994",
	            'identifier' => "412704692",
	            'login' => "471599401",
	            'password' => "124DEVph47"
	        );
	    }
	    else {
	        return array(
	            'rang' => "01",
	            'site' => "4715994",
	            'identifier' => "412704692",
	            'login' => "471599401",
	            'password' => "124DEVph47"
	        );
	    }
	    
	    /*
		if ($transaction->getAuthorizationMethod() == Customweb_Payment_Authorization_PaymentPage_IAdapter::AUTHORIZATION_METHOD_NAME) {
			return array(
				'rang' => "43",
				'site' => "1999888",
				'identifier' => "107975626",
				'login' => "199988843",
				'password' => "1999888I" 
			);
		}
		else {
			return array(
				'rang' => "63",
				'site' => "1999888",
				'identifier' => "109518543",
				'login' => "199988863",
				'password' => "1999888I" 
			);
		}
		*/
	}
}
