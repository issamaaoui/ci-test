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

 * @category	Customweb
 * @package		Customweb_PayboxCw
 * @version		1.0.49
 */

class Customweb_PayboxCw_Block_Iframe extends Mage_Core_Block_Template
{
	private $iframeUrl;
	private $iframeHeight;
	
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('customweb/payboxcw/iframe.phtml');
		
		$transaction = Mage::registry('cw_transaction');
		$this->iframeUrl = $transaction->getTransactionObject()->getPaymentMethod()->getIFrameUrl($transaction, $transaction->getOrder()->getPayment()->getAdditionalInformation());
		$this->iframeHeight = $transaction->getTransactionObject()->getPaymentMethod()->getIFrameHeight($transaction, $transaction->getOrder()->getPayment()->getAdditionalInformation());
		$transaction->save();
	}

	public function getIframeUrl() {
		return $this->iframeUrl;
	}
	
	public function getIframeHeight() {
		return $this->iframeHeight;
	}
}