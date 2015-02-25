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

class Customweb_PayboxCw_Model_Asset_SkinResolver implements Customweb_Asset_IResolver
{
	public function resolveAssetStream($identifier) {
		$filePath = $this->getSkinFilePath($identifier);
		if (file_exists($filePath)) {
			return new Customweb_Core_Stream_Input_File($filePath);
		}
		throw new Customweb_Asset_Exception_UnresolvableAssetException($identifier);
	}

	public function resolveAssetUrl($identifier) {
		$this->resolveAssetStream($identifier);
		return new Customweb_Core_Url($this->getSkinUrl($identifier));
	}
	
	private function getSkinFilePath($identifier) {
		return Mage::getSingleton('core/design_package')->getFilename('customweb/payboxcw/assets/' . $identifier, array(
			'_type' => 'skin'
		));
	}
	
	private function getSkinUrl($identifier) {
		return Mage::getSingleton('core/design_package')->getSkinUrl('customweb/payboxcw/assets/' . $identifier);
	}
}