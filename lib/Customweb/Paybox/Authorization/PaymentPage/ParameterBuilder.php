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
//require_once 'Customweb/Paybox/Util.php';
//require_once 'Customweb/Util/Currency.php';
//require_once 'Customweb/Paybox/Authorization/AbstractParameterBuilder.php';
//require_once 'Customweb/Paybox/Method/Factory.php';



/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Paybox_Authorization_PaymentPage_ParameterBuilder extends Customweb_Paybox_Authorization_AbstractParameterBuilder {

	public function __construct(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Paybox_Configuration $configuration){
		parent::__construct($transaction, $configuration);
	}

	public function buildStandardParameters(){
		$paymentMethodClass = Customweb_Paybox_Method_Factory::getMethod(
				$this->getTransaction()->getTransactionContext()->getOrderContext()->getPaymentMethod(), $this->getConfiguration());
		
		$parameters = array_merge($this->buildBaseParameters(), $this->buildTransactionParameters(), $this->buildRedirectionParameters(), 
				$paymentMethodClass->getAdditionalParameters($this->getTransaction()));
		$parameters['PBX_HMAC'] = $this->buildHmac($parameters);
		return $parameters;
	}
	
	// --------------------------------------------------------------------- HELPING FUNCTIONS --------------------------------------------------------------------- 
	protected function buildBaseParameters(){
		$baseParameters = array();
		$baseParameters = array(
			'PBX_SITE' => $this->getConfiguration()->getSiteNumber($this->getTransaction()),
			'PBX_RANG' => $this->getConfiguration()->getRangNumber($this->getTransaction()),
			'PBX_IDENTIFIANT' => $this->getConfiguration()->getPayboxIdentifier($this->getTransaction()),
			'PBX_CURRENCYDISPLAY' => 'NO_CURR',
			'PBX_HASH' => 'SHA512',
			'PBX_REFABONNE' => Customweb_Paybox_Util::convert($this->getTransactionAppliedSchema($this->getTransaction())) 
		)
		;
		
		if (!$this->getConfiguration()->isLiveMode()) {
			$baseParameters['PBX_PAYBOX'] = "https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi";
			$baseParameters['PBX_BACKUP1'] = "https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi";
			$baseParameters['PBX_BACKUP2'] = "https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi";
		}
		
		return $baseParameters;
	}

	protected function buildTransactionParameters(){
		$paymentMethodClass = Customweb_Paybox_Method_Factory::getMethod(
				$this->getTransaction()->getTransactionContext()->getOrderContext()->getPaymentMethod(), $this->getConfiguration());
		$amount = 100 * $this->getTransaction()->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals();
		
		return array(
			'PBX_DEVISE' => Customweb_Util_Currency::getNumericCode(
					$this->getTransaction()->getTransactionContext()->getOrderContext()->getCurrencyCode()),
			'PBX_TOTAL' => $amount,
			'PBX_CMD' => rand(),
			'PBX_PORTEUR' => $this->getTransaction()->getTransactionContext()->getOrderContext()->getBillingEMailAddress(),
			'PBX_TIME' => date("c"),
			'PBX_RETOUR' => "reference:R;error:E;transaction:S;appel:T;amount:M;cardtype:C;cardStart:N;cardEnd:J;aliasInfo:U;sign:K",
			'PBX_AUTOSEULE' => $this->getPaymentAction(),
			'PBX_TYPEPAIEMENT' => $paymentMethodClass->getPaymentType(),
			'PBX_TYPECARTE' => $paymentMethodClass->getPaymentMethodIdentifier($this->getTransaction()) 
		);
	}

	protected function buildRedirectionParameters(){
		return array(
			'PBX_ANNULE' => $this->getTransaction()->getFailedUrl(),
			'PBX_REFUSE' => $this->getTransaction()->getFailedUrl(),
			'PBX_REPONDRE_A' => $this->getTransaction()->getProcessAuthorizationUrl(),
			'PBX_EFFECTUE' => $this->getTransaction()->getSuccessUrl() 
		);
	}

	private function buildHmac(array $parameters){
		$key = $this->getConfiguration()->getHmacKey();
		$binkey = pack("H*", $key);
		$params = "";
		foreach ($parameters as $key => $value) {
			$params .= $key . "=" . $value . "&";
		}
		$params = substr($params, 0, -1);
		return strtoupper(hash_hmac('sha512', $params, $binkey));
	}
}






