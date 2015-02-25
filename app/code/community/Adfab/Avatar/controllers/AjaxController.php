<?php

class Adfab_Avatar_AjaxController extends Mage_Core_Controller_Front_Action
{
	public function savePhotoAction(){
	 	$resultat = Mage::helper("avatar")->processImage();
	 	if (!is_array($resultat)){
	 		$resultat = str_replace("\\","/",$resultat);
	 		$session = Mage::getSingleton('customer/session');
	        $session->setAvatar($resultat);
	        $res = array(
	 			'success'	=> true,
                'error'     => false,
                'message'   => Mage::getBaseUrl('media')."avatar".$resultat
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($res));
	 	} else {
	 		$res = array(
	 			'success'	=> false,
                'error'     => true,
                'message'   => $resultat[0]
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($res));
	 	}
	}
}
