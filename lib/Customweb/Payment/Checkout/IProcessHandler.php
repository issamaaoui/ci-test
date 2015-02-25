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
 * This interface allows the handling of the checkout process. 
 * During the checkout process the user input has to be stored. This interface
 * the methods to update the following information:
 * - Provider Customer ID
 * - Shipping and Billing Address
 * - Shipping Method
 * - Payment Method
 * 
 * After providing this information the checkout can be completed by
 * calling self::createOrder().
 * 
 * @author Thomas Hunziker
 *
 */
interface Customweb_Payment_Checkout_IProcessHandler {
	
	/**
	 * Loads the checkout context given by the checkout id.
	 * 
	 * @param string $checkoutId
	 * @return Customweb_Payment_Checkout_IContext
	 */
	public function loadContext($checkoutId);

	/**
	 * Updates the customer id as defined by the customer id.
	 *
	 * @param Customweb_Payment_Checkout_IContext $checkout
	 * @param Customweb_Payment_Authorization_OrderContext_IAddress $address
	 */
	public function updateProviderCustomerId(Customweb_Payment_Checkout_IContext $checkout, $customerId);
	
	/**
	 * Updates the shipping address. By changing the address the total
	 * amount and the shipping line items may be changed of the checkout
	 * context object.
	 * 
	 * @param Customweb_Payment_Checkout_IContext $checkout
	 * @param Customweb_Payment_Authorization_OrderContext_IAddress $address
	 * @return void
	 */
	public function updateShippingAddress(Customweb_Payment_Checkout_IContext $checkout, Customweb_Payment_Authorization_OrderContext_IAddress $address);
	
	/**
	 * Updates the billing address. By changing the address the total
	 * amount and the shipping line items may be changed of the checkout
	 * context object.
	 * 
	 * @param Customweb_Payment_Checkout_IContext $checkout
	 * @param Customweb_Payment_Authorization_OrderContext_IAddress $address
	 */
	public function updateBillingAddress(Customweb_Payment_Checkout_IContext $checkout, Customweb_Payment_Authorization_OrderContext_IAddress $address);
	
	/**
	 * Updates the shipping method. The shipping method should be one of possible
	 * shipping methods (see self::getPossibleShippingMethods()).
	 *
	 * The changing of the shipping method leads to a different order total.
	 *
	 * In case the cart contains only virtual products there is no need to set
	 * any shipping method. Each line item indicates if it is a virtual product
	 * or not.
	 *
	 * @param Customweb_Payment_Authorization_IShippingMethod $method
	 * @return Customweb_Payment_Checkout_IContext
	 */
	public function updateShippingMethod(Customweb_Payment_Checkout_IContext $checkout, Customweb_Payment_Authorization_IShippingMethod $method);

	/**
	 * Updates the payment method for this checkout. The payment method
	 * should be one of the possible payment methods (see self::getPossiblePaymentMethods()).
	 *
	 * By changing the payment method the order total may be changed.
	 *
	 * @param Customweb_Payment_Authorization_IPaymentMethod $method
	 * @return Customweb_Payment_Checkout_IContext
	 */
	public function updatePaymentMethod(Customweb_Payment_Checkout_IContext $checkout, Customweb_Payment_Authorization_IPaymentMethod $method);

	/**
	 * Returns a list of possible shipping methods. In case the method returns an empty list, no shipping
	 * method can be applied and hence the order can not be completed.
	 *
	 * Before calling this method a shipping address must be set.
	 *
	 * @param Customweb_Payment_Checkout_IContext $checkout
	 * @return Customweb_Payment_Authorization_IShippingMethod[] List of shipping methods
	 */
	public function getPossibleShippingMethods(Customweb_Payment_Checkout_IContext $checkout);
	
	/**
	 * Returns a list of possible payment methods. The set of the payment
	 * methods is restricted to the one which are provided by the provider.
	 *
	 * @param Customweb_Payment_Checkout_IContext $checkout
	 * @return Customweb_Payment_Authorization_IPaymentMethod[]
	*/
	public function getPossiblePaymentMethods(Customweb_Payment_Checkout_IContext $checkout);
	
	/**
	 * This method renders a review pane for the given checkout. The review pane should contain
	 * a list of products including the order total. This overview may be presented to the 
	 * user as the final review step.
	 * 
	 * In case the review pane should also contain the confirmation of GTC the flag is $includeGtc is 
	 * set to true.
	 * 
	 * The pane can contain other form fields.
	 * 
	 * @param Customweb_Payment_Checkout_IContext $checkout
	 * @param boolean $mayIncludeGtc In case
	 */
	public function renderReviewPane(Customweb_Payment_Checkout_IContext $checkout, $includeGtc);
	
	/**
	 * Validates the input from the customer. In case it is not valid the method should throw an excpetion.
	 * 
	 * @param Customweb_Payment_Checkout_IContext $checkout
	 * @throws Exception In case the input is invalid.
	 */
	public function validateReviewForm(Customweb_Payment_Checkout_IContext $checkout, Customweb_Core_Http_IRequest $request);
	
	/**
	 * Creates an order based on the checkout id. The method returns a
	 * transcation context. The implementor of the interface must
	 * use this transaction context to create a transaction and to
	 * authorize the transaction with the Customweb_Payment_Authorization_IService.
	 *
	 * @param Customweb_Payment_Checkout_IContext $checkoutId
	 * @return Customweb_Payment_Authorization_ITransactionContext
	 */
	public function createOrder(Customweb_Payment_Checkout_IContext $checkout);
	
}