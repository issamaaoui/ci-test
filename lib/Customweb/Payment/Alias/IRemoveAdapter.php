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
 * With this adapter stored data on the payment gateway servers can be
 * removed. 
 * 
 * This can be applied on alias manager or recurring transaction.
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
interface Customweb_Payment_Alias_IRemoveAdapter {
	
	/**
	 * This method removes for the given transaction the alias on the payment
	 * gateways servers.
	 * 
	 * @param Customweb_Payment_Authorization_ITransaction $transaction
	 * @return void
	 * @throws Exception
	 */
	public function remove(Customweb_Payment_Authorization_ITransaction $transaction);
	
}
