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
 *
 * @category	Customweb
 * @package		Customweb_PayboxCw
 * @version		1.0.49
 */

$installer = $this;

$installer->startSetup();

if (version_compare(Mage::getVersion(), '1.5', '>='))
{
	try {
		$data = array();
		$data[] = array(
				'status'    => 'canceled_payboxcw',
				'label'     => 'Canceled Paybox'
		);
		$data[] = array(
				'status'    => 'pending_payboxcw',
				'label'     => 'Pending Paybox'
		);

		$statusTable        = $installer->getTable('sales/order_status');
		$installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);


		$data = array();
		$data[] = array(
				'status'    => 'canceled_payboxcw',
				'state'     => Customweb_PayboxCw_Model_Method::STATE_CANCELLED,
				'is_default'=> 0
		);

		$data[] = array(
				'status'    => 'pending_payboxcw',
				'state'     => Customweb_PayboxCw_Model_Method::STATE_PENDING,
				'is_default'=> 0
		);

		$statusStateTable   = $installer->getTable('sales/order_status_state');
		$installer->getConnection()->insertArray(
				$statusStateTable,
				array('status', 'state', 'is_default'),
				$data
		);

	}
	catch(Exception $e) {}
}

$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('payboxcw_alias_data')} (
	`alias_id` INT NOT NULL AUTO_INCREMENT,
	`customer_id` INT NOT NULL ,
	`order_id` INT NOT NULL ,
	`alias_for_display` VARCHAR(50) NOT NULL ,
	`payment_method` VARCHAR(255) NOT NULL ,
	PRIMARY KEY ( `alias_id` )
	) ENGINE = InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('payboxcw_customer_context')} (
	`customer_id` INT NOT NULL,
	`context` TEXT ,
	PRIMARY KEY ( `customer_id` )
	) ENGINE = InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();