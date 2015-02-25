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

//require_once 'Customweb/Payment/Authorization/Method/CreditCard/CardHandler.php';
//require_once 'Customweb/Payment/Authorization/Server/IAdapter.php';
//require_once 'Customweb/Form/Control/Select.php';
//require_once 'Customweb/Payment/Authorization/Method/CreditCard/CardInformation.php';
//require_once 'Customweb/Paybox/Method/DefaultMethod.php';
//require_once 'Customweb/Payment/Authorization/Method/CreditCard/ElementBuilder.php';
//require_once 'Customweb/Paybox/Authorization/Transaction.php';
//require_once 'Customweb/Form/Element.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';


/**
 *
 * @author Thomas Hunziker
 * @Method({'Visa', 'MasterCard', 'CreditCard' , 'AmericanExpress', 'CarteBancaire' , 'CarteBleue', 'Maestro', 'Diners', 'Jcb', 'Aurore', 'Bcmc'})
 */
class Customweb_Paybox_Method_CreditCardMethod extends Customweb_Paybox_Method_DefaultMethod {

	public function getFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $authorizationMethod, $isMoto, $customerPaymentContext) {
		$elements = array();
		
			
		/* @var $transaction Customweb_Payment_Authorization_Method_CreditCard_ElementBuilder */
		/* @var $aliasTransaction Customweb_Paybox_Authorization_Transaction */
		
		// TODO: Why is this check used? Credit card does only support server and payment page. The expression must evaluate always to TRUE
		if ($authorizationMethod == Customweb_Payment_Authorization_Server_IAdapter::AUTHORIZATION_METHOD_NAME ||
			$authorizationMethod == Customweb_Payment_Authorization_PaymentPage_IAdapter::AUTHORIZATION_METHOD_NAME) {
			$formBuilder = new Customweb_Payment_Authorization_Method_CreditCard_ElementBuilder();
				
			
			// Set field names
			$formBuilder
				->setBrandFieldName('paymentmethod')
			//	->setCardHolderFieldName('cardholder')
				->setCardNumberFieldName('PORTEUR')
				->setExpiryMonthFieldName('expm')
				->setExpiryYearFieldName('expy')
				->setExpiryYearNumberOfDigits(2)
				->setCvcFieldName('CVV');
				
			// Handle brand selection
			if (strtolower($this->getPaymentMethodName()) == 'creditcard') {
				$formBuilder
					->setCardHandlerByBrandInformationMap($this->getPaymentInformationMap(), $this->getPaymentMethodConfigurationValue('credit_card_brands'), 'PaymentMethod')
					->setAutoBrandSelectionActive(true);
			}
			else {
				$formBuilder
					->setCardHandlerByBrandInformationMap($this->getPaymentInformationMap(), $this->getPaymentMethodName(), 'PaymentMethod')
					->setSelectedBrand($this->getPaymentMethodName())
					->setFixedBrand(true)
				;
			}
			
		
			$formBuilder->setImageBrandSelectionActive(true);
			
			if($aliasTransaction instanceof Customweb_Paybox_Authorization_Transaction){
				$formBuilder->setSelectedExpiryMonth(substr($aliasTransaction->getDateVal(),0,2));
				$formBuilder->setSelectedExpiryYear( substr($aliasTransaction->getDateVal(),2));
				$formBuilder->setMaskedCreditCardNumber($aliasTransaction->getAliasForDisplay());
				$formBuilder->setCardHolderName($aliasTransaction->getTransactionContext()->getOrderContext()->getBillingLastName());
				if ($aliasTransaction->getBrandName() !== null && strtolower($this->getPaymentMethodName()) == 'creditcard') {
					$map = array(
						'amex' => 'AmericanExpress',
						'visa' => 'Visa',
						'mastercard' => 'MasterCard',
						'cb' => 'CarteBleue',
						'jcb' => 'Jcb',
						'diners' => 'Diners',
					);
					$brand = strtolower($aliasTransaction->getBrandName());
					if (isset($map[$brand])) {
						$brand = $map[$brand];
					}
					$formBuilder->setSelectedBrand($brand);
				}
			}
				
			return $formBuilder->build();
		}
		else {
			if (strtolower($this->getPaymentMethodName()) == 'creditcard') {
				$options = array();
				foreach ($this->getPaymentMethodConfigurationValue('credit_card_brands') as $brand) {
					$info = $this->getPaymentInformationByBrand($brand);
					if (isset($info['parameters']['PaymentMethod'])) {
						$options[$info['parameters']['PaymentMethod']] = $info['method_name'];
					}
				}
				
				$control = new Customweb_Form_Control_Select('credit_card_brand', $options);
				$selectElement = new Customweb_Form_Element(
					Customweb_I18n_Translation::__('Select Card Type'),
					$control,
					Customweb_I18n_Translation::__('Please select the brand of your card.')
				);
				$elements[] = $selectElement;
			}
		}
		
		return $elements;
	}
	
	public function getCardHandler() {
		if (strtolower($this->getPaymentMethodName()) == 'creditcard') {
			$informationMap = Customweb_Payment_Authorization_Method_CreditCard_CardInformation::getCardInformationObjects($this->getPaymentInformationMap(), $this->getPaymentMethodConfigurationValue('credit_card_brands'), 'PaymentMethod');
		}
		else {
			$informationMap = Customweb_Payment_Authorization_Method_CreditCard_CardInformation::getCardInformationObjects($this->getPaymentInformationMap(), $this->getPaymentMethodName(), 'PaymentMethod');
		}
		return new Customweb_Payment_Authorization_Method_CreditCard_CardHandler($informationMap);
	}

	/**
	 * This method maps a given brand to pmethod.
	 *
	 * @param string $brand
	 * @return null|array
	 */
	public function getPaymentInformationByBrand($brand) {
		$map = $this->getPaymentInformationMap();
		$brand = strtolower($brand);
		if (isset($map[$brand])) {
			return $map[$brand];
		}
		else {
			return null;
		}
	}
	
	public function getPaymentType() {
		return "CARTE";
	}
}