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

//require_once 'Customweb/Paybox/Method/DefaultMethod.php';
//require_once 'Customweb/Form/ElementFactory.php';
//require_once 'Customweb/Paybox/Authorization/Server/Adapter.php';


class Customweb_Paybox_Method_CreditMethod extends Customweb_Paybox_Method_DefaultMethod {

	public function getFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $authorizationMethod, $isMoto, $customerPaymentContext) {
		$elements = array();
		/* @var $transaction Customweb_Payment_Authorization_Method_CreditCard_ElementBuilder */
		
		if (Customweb_Paybox_Authorization_Server_Adapter::AUTHORIZATION_METHOD_NAME == $authorizationMethod) {
		
			$cardHolder = $orderContext->getBillingFirstName() . ' ' . $orderContext->getBillingLastName();
		
			$elements[] = Customweb_Form_ElementFactory::getAccountOwnerNameElement("account_owner", $cardHolder);
			$elements[] = Customweb_Form_ElementFactory::getIbanNumberElement("iban_number");
			$elements[] = Customweb_Form_ElementFactory::getBankNameElement('bank_name');
			$elements[] = Customweb_Form_ElementFactory::getBankLocationElement('bank_location');
		}
		return $elements;
	}

	public function getPaymentType() {
		return "CREDIT";
	}
}