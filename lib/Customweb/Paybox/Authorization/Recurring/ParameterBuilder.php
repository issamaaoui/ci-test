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

//require_once 'Customweb/Paybox/Util.php';
//require_once 'Customweb/Paybox/Authorization/AbstractParameterBuilder.php';


/**
 *
 * @author Thomas Brenner
 *
 */
class Customweb_Paybox_Authorization_Recurring_ParameterBuilder extends Customweb_Paybox_Authorization_AbstractParameterBuilder {
	
	private $transaction;
	
	public function __construct(Customweb_Paybox_Authorization_Transaction $transaction, Customweb_Paybox_Configuration $configuration) {
		parent::__construct($transaction, $configuration);
	}
	
	public function buildRecurringParameters(Customweb_Paybox_Authorization_Transaction $oldTransaction) {
			$parameters = array_merge(
					$this->buildBaseRecurringParameters($oldTransaction),
					$this->buildTransactionParameters()
			);
			$parameters['REFABONNE'] = Customweb_Paybox_Util::convert($this->getTransactionAppliedSchema($oldTransaction));
			$parameters['PORTEUR'] = $oldTransaction->getAliasToken();
			$parameters['DATEVAL'] = $oldTransaction->getDateVal();
			$parameters['TYPE'] = "00053";
			$parameters['ACTIVITE'] = "027";
			return $parameters;
	}
	
	public function buildBaseRecurringParameters(Customweb_Paybox_Authorization_Transaction $oldTransaction) {
		return array(
			'VERSION'			=> '00104',
			'SITE'				=> $this->getConfiguration()->getSiteNumber($oldTransaction),
			'RANG'				=> $this->getConfiguration()->getRangNumber($oldTransaction),
			'CLE'				=> $this->getConfiguration()->getPassword($oldTransaction),
		);
	}
}