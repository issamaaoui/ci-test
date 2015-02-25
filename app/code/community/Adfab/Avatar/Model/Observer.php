<?php
class Adfab_Avatar_Model_Observer {
    function customer_save_before($observer) {
        try {
			$customer = $observer->getCustomer();
            $session = Mage::getSingleton('customer/session');
    		if($session->hasAvatar()) {
                $customer->setData('avatar_src',$session->getAvatar());
                $customer->setData('avatar_valid',0);
    		    $customer->setAvatarSrc($session->getAvatar());
    			$session->unsAvatar();
    		}
        } catch ( Exception $e ) {
			Mage::log( "customer_save_before observer failed: " . $e->getMessage() );
		}
	}
}