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

//require_once 'Customweb/Payment/Authorization/Server/IAdapter.php';
//require_once 'Customweb/Core/Http/Response.php';
//require_once 'Customweb/Paybox/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Paybox/Authorization/Server/ParameterBuilder.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Paybox/Authorization/Transaction.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Paybox/Method/Factory.php';


/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Paybox_Authorization_Server_Adapter extends Customweb_Paybox_Authorization_AbstractAdapter implements Customweb_Payment_Authorization_Server_IAdapter {
	
	public function getAuthorizationMethodName() {
		return self::AUTHORIZATION_METHOD_NAME;
	}
	
	
	public function getAdapterPriority() {
		return 200;
	}
	
	public function createTransaction(Customweb_Payment_Authorization_Server_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Paybox_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->createRoutingUrls($this->getContainer());
		$transaction->setLiveTransaction($this->getConfiguration()->isLiveMode());
		$transaction->setPaymentId($transaction->getExternalTransactionId());
		return $transaction;
	}
	
	public function isPaymentMethodSupportingRecurring(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod) {
		return true;
	}
	
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $customerPaymentContext){
		return Customweb_Paybox_Method_Factory::getMethod($orderContext->getPaymentMethod(), $this->getConfiguration())
		->getFormFields($orderContext, $aliasTransaction, $failedTransaction, self::AUTHORIZATION_METHOD_NAME, false, $customerPaymentContext);
	}

	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return true;
	}

	public function processAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, array $parameters) {
		if($transaction->getTransactionContext()->getAlias() == "new") {
			return $this->handleAuthorization($transaction, $parameters, true);
		} else if ($transaction->getTransactionContext()->getAlias() != null) {
			return $this->performAliasTransaction($transaction, $parameters);
		} else {
			return $this->handleAuthorization($transaction, $parameters, false);
		}
	}
	
	public function finalRedirection(Customweb_Paybox_Authorization_Transaction $transaction) {
		$response = new Customweb_Core_Http_Response();
		if($transaction->isAuthorizationFailed()) {
			return $response->appendHeader("Location: " . $transaction->getFailedUrl());
		} else {
			return $response->appendHeader("Location: " . $transaction->getSuccessUrl());
		}
	}

	
	/**
	 * Function that deals with the different possibilities to perform a server to server transaction (3D-secure or not)
	 * 
	 * @param Customweb_Paybox_Authorization_Transaction $transaction
	 * @param array $parameters
	 * @param boolean $alias
	 * @throws Exception
	 */
	private function handleAuthorization(Customweb_Paybox_Authorization_Transaction $transaction, array $parameters, $alias) {
		$response = new Customweb_Core_Http_Response();
		$parameterBuilder = new Customweb_Paybox_Authorization_Server_ParameterBuilder($transaction, $this->getConfiguration());
		if(isset($parameters['data'])) {
			$paymentParameters = $parameterBuilder->buildPaymentParametersDecrypted($parameters);
			$transaction->setDateVal($paymentParameters['DATEVAL']);
			$method = Customweb_Paybox_Method_Factory::getMethod($transaction->getPaymentMethod(), $this->getConfiguration());
			if (method_exists($method, 'getCardHandler')) {
				$brandKey = $method->getCardHandler()->getBrandKeyByCardNumber($paymentParameters['PORTEUR']);
				$transaction->setBrandName($brandKey);
			}
			// TODO: Find out the card brand and set it on the transaction object.
		}
		
		try {
			$parameters['StatusPBX'] = "Commerce non 3DSecure";
			if(isset($parameters['StatusPBX'])) {
				if(isset($parameters['3DENROLLED']) && $parameters['3DENROLLED'] == "Y") { 
					$this->verify3DParameters($parameters);
					$transaction->save3DParameters($parameters);
					if (isset($parameters['ID3D'])) {
						$transaction->setID3D($parameters['ID3D']);
					}
					$results = $this->convertResult($this->sendRequest($this->getConfiguration()->getPayboxServerUrl(), $parameterBuilder->build3DRequestParameters($parameters)));
					$this->performAuthorization($transaction, $results, $alias, $parameters);
					return $this->finalRedirection($transaction);
				} elseif ($parameters['StatusPBX'] == "Commerce non 3DSecure") {
					// @TODO : odura - en attendant la montée de version du module & la correction savePayment dans checkout/onepage (param vide ?) 
					
					// initialisation des paramètres
					$amount = 100 * $transaction->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals();
					$devise = Customweb_Util_Currency::getNumericCode($transaction->getTransactionContext()->getOrderContext()->getCurrencyCode());
					$newParams = array(
						'VERSION'     => '00104',
						'TYPE'        => '00003',
						'SITE'        => $this->getConfiguration()->getSiteNumber($transaction),
						'RANG'        => $this->getConfiguration()->getRangNumber($transaction),
						'CLE'         => $this->getConfiguration()->getPassword($transaction),

						'NUMQUESTION' => rand(0,1000000),
						'MONTANT'     => $amount,
						'DEVISE'      => $devise,
						'REFERENCE'   => $transaction->getTransactionContext()->getOrderId() . "-" . rand(0,1000000),

						'PORTEUR'     => $parameters['PORTEUR'],
						'DATEVAL'     => $parameters['expm'] . $parameters['expy'],
						'CVV'         => $parameters['CVV'],

						'DATEQ'       => date('Ymdhis'),
						'ACTIVITE'    => '024'
					);

					// initialisation de la session https
					$curl = curl_init($this->getConfiguration()->getPayboxServerUrl());
					
					// Précise que la réponse est souhaitée
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					
					// Présise que le session est nouvelle
					curl_setopt($curl, CURLOPT_COOKIESESSION, true);

					// Crée la chaine url encodée selon la RFC1738 à partir du tableau de paramètres séparés par le caractère &
					$trame = http_build_query($newParams, '', '&');

					// Présise le type de requête HTTP : POST
					curl_setopt($curl, CURLOPT_POST, true);
					
					// Présise le Content-Type
					curl_setopt($curl,CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
					
					// Ajoute les paramètres
					curl_setopt($curl, CURLOPT_POSTFIELDS, $trame);
					
					// Envoi de la requête et obtention de la réponse
					$response = curl_exec($curl);

					$results = $this->convertResult($response);
					if($results['CODEREPONSE'] != "00000") {
						$message = $results['COMMENTAIRE'];
						header("Location: " . $transaction->getFailedUrl());
						die();
					}else{
						$this->performAuthorization($transaction, $results, $alias, $parameters);
						return $this->finalRedirection($transaction);
					}
					//$results = $this->convertResult($this->sendRequest($this->getConfiguration()->getPayboxServerUrl(), $parameterBuilder->buildNo3DRequestParameters($parameters)));
				} else {
					return $response->appendBody(" ");
				}
			} else {
				return $response->appendHeader("Location: ". Customweb_Util_Url::appendParameters($this->getConfiguration()->getPayboxRemoteMpiUrl(), $parameterBuilder->build3DsecureRequest($parameters)));
			}
		}
		catch(Customweb_Paybox_Exception_PaymentErrorException $e) {
			$transaction->setAuthorizationFailed($e->getErrorMessage());
		}
		catch(Exception $e) {Customweb_Util_Url::appendParameters($this->getConfiguration()->getPayboxRemoteMpiUrl(), $parameterBuilder->build3DsecureRequest($parameters));
			$transaction->setAuthorizationFailed($e->getMessage());
		}
		
	}
	
	private function performAuthorization(Customweb_Paybox_Authorization_Transaction $transaction, array $results, $alias, $originalParameters) {
		
		$parameterBuilder = new Customweb_Paybox_Authorization_Server_ParameterBuilder($transaction, $this->getConfiguration());
		$transaction->setNumParameters($results);
		if($results['CODEREPONSE'] != "00000") {
			throw new Exception(Customweb_I18n_Translation::__("The authorization was not successful"));
		} else {
			$transaction->authorize();
			$paymentType = $parameterBuilder->getPaymentType();
			if($paymentType['TYPE'] == "00003") {
				$transaction->capture();
			}
		}
		
		//If true, create alias
		if($alias || $transaction->getTransactionContext()->createRecurringAlias()) {
			$this->buildAlias($transaction, $this->getConfiguration(), $originalParameters);
		}
	}
	
	private function verify3DParameters(array $parameters) {
		if($parameters['3DSIGNVAL'] != "Y") {
			throw new Exception(Customweb_I18n_Translation::__("The parameters \"3DSIGNVAL\" was false."));
		}
	}
	
	
}
