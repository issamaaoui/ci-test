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

class Customweb_PayboxCw_AliaspayboxcwController extends Mage_Adminhtml_Controller_Action
{
	protected function _initCustomer($idFieldName = 'id')
    {
        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

	public function gridAction()
	{
		$this->_initCustomer();
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('Customweb_PayboxCw_Block_Adminhtml_Customer_Alias')->toHtml()
		);
	}

	public function deleteAction()
	{
		$transactionId = (int) $this->getRequest()->getParam('transaction_id');
        $transaction = Mage::getModel('payboxcw/transaction')->load($transactionId);
        $transaction->setAliasActive(false);
        $transaction->save();

        $this->_getSession()->addSuccess(
			Mage::helper('PayboxCw')->__('The alias has been deleted.')
		);
        $this->_redirect('*/customer/edit', array('id' => $transaction->getCustomerId(), 'active_tab' => 'payboxcw_alias'));
	}
}