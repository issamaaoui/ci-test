<?php 


/**
 * Defines a checkout object. 
 * 
 * @author hunziker
 *
 */
interface Customweb_Payment_ICheckout {
	
	/**
	 * Return a machine name of the checkout.
	 * The name should only consists of ASCII chars. The should
	 * be unique per provider.
	 * 
	 * @return string Machine Name.
	 */
	public function getMachineName();
	
	/**
	 * Returns the sort order of the checkout. A higher number
	 * indicates a higher position in the list of checkouts. This number 
	 * defines the order globally for all providers.
	 * 
	 * @return int
	 */
	public function getSortOrder();
	
	/**
	 * Returns the name of the checkout. This name may be shown to
	 * the user.
	 *
	 * @return Customweb_I18n_ILocalizableString Name of the checkout
	 */
	public function getName();
	
	/**
	 * Returns a HTML snipped which embeddes the checkout into the UI.
	 *
	 * @return string HTML
	 */
	public function getWidgetHtml();
	
}