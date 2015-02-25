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

/**
 * This interface defines a context of a checkout. During the checkout
 * this context is used to exchange the information between the provider
 * and the e-commerce solution.
 * 
 * 
 * @author Thomas Hunziker
 *
 */
class Customweb_Payment_Checkout_IContext {
	
	/**
	 * Returns a unique id of the checkout. The id is used to identify 
	 * the checkout in the remote system. The id is never changed
	 * during one checkout process.
	 * 
	 * @return string
	 */
	public function getCheckoutId();
	
	/**
	 * Returns a list of line items in the cart. The items may be modified during a
	 * checkout session according to changes done on the shipping method, the payment method, 
	 * the billing address or the shipping address.
	 * 
	 * @return Customweb_Payment_Authorization_IInvoiceItem[]
	 */
	public function getInvoiceItems();
	
	/**
	 * The order amount in decimal / float representation in the curreny
	 * given by the method self::getCurrencyCode(). 
	 *
	 * @return float
	 */
	public function getOrderAmountInDecimals();
	
	/**
	 * The currency code in ISO format.
	 *
	 * @return String ISO code of the currency used for the transactions.
	*/
	public function getCurrencyCode();
	
	/**
	 * This method returns the language spoken by the customer. This should
	 * be remain the same during the whole checkout.
	 *
	 * @return Customweb_Core_Language
	 */
	public function getLanguage();

	/**
	 * Returns the customer id as defined by the provider. This can
	 * be null at the initial state of the checkout. It may be filled
	 * when the user is logged in or explicitly set with the update method.
	 *
	 * @return string
	 */
	public function getProviderCustomerId();
	
	/**
	 * Returns the customer id as defined by the e-commerce solution. The 
	 * customer account is created during the creation of the order hence 
	 * this can be null until the order is created.
	 *
	 * @return string
	*/
	public function getCustomerId();
	
	/**
	 * Returns the transaction id. This method will return null until the order 
	 * is created.
	 * 
	 * @return string
	 */
	public function getTransactionId();
	
	/**
	 * Returns the shipping address. This method returns in the initial state 
	 * null. The address should be set with self::setShippingAddress()
	 * 
	 * @return Customweb_Payment_Authorization_OrderContext_IAddress
	 */
	public function getShippingAddress();
	
	/**
	 * Returns the billing address. This method returns in the initial state 
	 * null.
	 * 
	 * @return Customweb_Payment_Authorization_OrderContext_IAddress
	 */
	public function getBillingAddress();
	
	/**
	 * Returns the current set shipping method. At the initial state of
	 * the checkout this method will normally return null.
	 * 
	 * @return Customweb_Payment_Authorization_IShippingMethod
	 */
	public function getShippingMethod();
	
	/**
	 * Returns the payment method set for this checkout. At the initial state this 
	 * method will return null. 
	 * 
	 * @return Customweb_Payment_Authorization_IPaymentMethod
	 */
	public function getPaymentMethod();
	
}