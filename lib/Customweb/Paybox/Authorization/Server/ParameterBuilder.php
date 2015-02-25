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

//require_once 'Customweb/Paybox/Authorization/AbstractParameterBuilder.php';


/**
 *
 * @author Thomas Brenner
 *
 */
class Customweb_Paybox_Authorization_Server_ParameterBuilder extends Customweb_Paybox_Authorization_AbstractParameterBuilder
{
	public function __construct(Customweb_Paybox_Authorization_Transaction $transaction, Customweb_Paybox_Configuration $configuration) {
		parent::__construct($transaction, $configuration);
	}
	
	
	public function build3DRequestParameters(array $originalParameters) {
		$parameters = array_merge(
				$this->buildPaymentParametersDecrypted($originalParameters),
				$this->buildBaseParameters(),
				$this->buildTransactionParameters(),
				$this->getPaymentType()
		);
		$parameters['ID3D'] = $this->getTransaction()->getID3D();
		return $parameters;
	}
	
	public function buildNo3DRequestParameters(array $originalParameters) {
		$parameters = array_merge(
			$this->buildPaymentParametersDecrypted($originalParameters),
			$this->buildBaseParameters(),
			$this->buildTransactionParameters(),
			$this->getPaymentType()
		);
		return $parameters;
	}
	
	public function build3DSecureRequest(array $originalParameters) {
		$parameters = array_merge(
			$this->buildNotificationParameters($originalParameters),
			$this->build3DParameters($originalParameters)
		);
		return $parameters;
	}
}