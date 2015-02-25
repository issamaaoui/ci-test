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

//require_once 'Customweb/Paybox/Configuration.php';
//require_once 'Customweb/Http/Response.php';
//require_once 'Customweb/Payment/Util.php';
//require_once 'Customweb/Http/Request.php';
//require_once 'Customweb/Paybox/AbstractParameterBuilder.php';


class Customweb_Paybox_AbstractAdapter
{
	/**
	 * Configuration object.
	 *
	 * @var Customweb_Paybox_Configuration
	 */
	private $configuration;
	private $container;
	
	public function __construct(Customweb_Payment_IConfigurationAdapter $configuration, Customweb_DependencyInjection_IContainer $container) {
		$this->configuration = new Customweb_Paybox_Configuration($configuration);
		$this->container = $container;
	}
	
	public function getConfiguration() {
		return $this->configuration;
	}
	
	public function getContainer() {
		return $this->container;
	}
	
	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext,
			Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){}
	
	public function validate(Customweb_Payment_Authorization_IOrderContext $orderContext,
			Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext, array $formData){}
	
	
	
	
	protected function convertResult($parameters) {
		$result = array();
		$parameters = explode('&',$parameters);
		foreach($parameters as $key => $value) {
			$temp = explode('=',$value);
			$result[$temp[0]] = $temp[1];
		}
		return $result;
	}
	
	
	protected function validateCustomParameters(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters){
		$customParametersBefore = $transaction->getTransactionContext()->getCustomParameters();
		foreach($customParametersBefore as $key => $value){
			if(!isset($parameters[$key])){
				return false;
			}
			if($parameters[$key] != $value){
				return false;
			}
		}
		return true;
	}
	
	public function setConfiguration(Customweb_Paybox_Configuration $configuration) {
		$this->configuration = $configuration;
	}
	
	/**
	 * @return string
	 */
	public final function getTransactionAppliedSchema(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Paybox_Configuration $configuration)
	{
		$schema = $configuration->getOrderIdSchema();
		$id = $transaction->getExternalTransactionId();
	
		return Customweb_Payment_Util::applyOrderSchema($schema, $id, 64);
	}
	
	/**
	 *
	 * Performs an HTTP Query
	 *
	 * @param String $url
	 * @param array $body
	 * @return Body
	 */
	public function sendRequest($url, array $body) {
		$request = new Customweb_Http_Request($url);
		$request->setBody($body);
		$request->setMethod("POST");
		$request->appendCustomHeaders(array(
			'Content-Type' => "text/html"
		));
		$handler = new Customweb_Http_Response();
		$handler = $request->send();
		return $handler->getBody();
	}
	
	public function buildAlias(Customweb_Paybox_Authorization_Transaction $transaction , Customweb_Paybox_Configuration $configuration, $parameters) {
		$parameterBuilder = new Customweb_Paybox_AbstractParameterBuilder($transaction, $configuration);
		$results = $this->convertResult($this->sendRequest($this->getConfiguration()->getPayboxServerUrl(), $parameterBuilder->buildAliasParameters($parameters)));
		if(isset($results['PORTEUR'])) {
			$transaction->setAliasToken($results['PORTEUR']);
		}
		$transaction->setNumParameters($results);
		$parameterBuilder->saveAliasForDisplay($parameters);
	}
}