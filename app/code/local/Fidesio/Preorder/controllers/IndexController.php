<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FIDESIO
 * Date: 26/11/14
 * Time: 12:00
 * To change this template use File | Settings | File Templates.
 */
class Fidesio_Preorder_IndexController extends Mage_Core_Controller_Front_Action
{
    const XML_PATH_EMAIL_TEMPLATE   = 'code_achat/email/email_template';
    const XML_PATH_EMAIL_SENDER     = 'code_achat/email/sender_email_identity';
    const XML_PATH_EMAIL_RECIPIENT  = 'code_achat/email/recipient_email';
    const XML_PATH_AUTHOR_TEMPLATE  = 'code_achat/email/author_template';

    const XML_URL_PHANTOM = 'code_achat/order/url_product';

    public function indexAction()
    {
        // Vérifier si le code est déjà dans la session faire une redirection vers la fiche produit
        $data = Mage::getSingleton('customer/session')->getData();
        if(isset($data['shopping_code'])){
            $this->_redirect(Mage::getStoreConfig(self::XML_URL_PHANTOM));
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    /**
     * Elle traite la saisie du code d'achat pour pouvoir commander
     */
    public function codePostAction(){
        $post = $this->getRequest()->getPost();
        if ($post) {
            $code = $post['code-achat'];
            $checkCode = Mage::getModel('preorder/code')->loadByCode($code);
            if(is_null($checkCode->getId())) {
                Mage::getSingleton('customer/session')->addError(Mage::helper('preorder')->__("This purchase code <strong>%s</strong> is not valid!", $code));
                return $this->_redirect('*/*',array('_forced_secure' => TRUE));
            }
            else{
                Mage::getSingleton('customer/session')->setShoppingCode($code);
                //Mage::getSingleton('customer/session')->addSuccess(Mage::helper('preorder')->__("Votre code d'achat \"<strong>%s</strong>\" a été mis dans la session et ne sera utilisé que lorsque vous aurez valider la commande!", $code));
                return $this->_redirect(Mage::getStoreConfig(self::XML_URL_PHANTOM), array('_forced_secure' => TRUE));
            }
        }
        else{
            $this->_redirect('*/*',array('_forced_secure' => TRUE));
        }
    }

    /**
     * Elle traite les demandes de codes d'achat envoyées depuis la page pre-commande
     */
    public function emailPostAction()
    {

        $post = $this->getRequest()->getPost();
        if ($post) {
            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(FALSE);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = FALSE;
                if (!Zend_Validate::is(trim($post['code-email']), 'EmailAddress')) {
                    $error = TRUE;
                }
                if ($error) {
                    throw new Exception('Error code-email');
                }


		// Stockage en base
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$connection->beginTransaction();
		$fields = array();
		$fields['email']= $post['code-email'];
		$fields['country']=$post['code-country'];
		$connection->insert('dvl_invitation', $fields);
		$connection->commit();

                // Email pour l'admin
                $link_to_send_code = "mailto:".$post['code-email']."?subject=[Devialet][Code d'achat]";
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['code-email'])
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        NULL,
                        array('data' => $postObject, 'link_code' =>$link_to_send_code)
                    );
                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                // Email pour l'auteur
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_AUTHOR_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        trim($post['code-email']),
                        NULL,
                        array()
                    );
                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }
                $translate->setTranslateInline(TRUE);
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('preorder')->__("Thank you for you request. PHANTOM will send you an invitation code via email as soon as possible."));

                $this->_redirect('*/*/',array('_forced_secure' => TRUE));
                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(TRUE);
                Mage::getSingleton('customer/session')->addError(Mage::helper('preorder')->__('Unable to submit your request. Please, try again later'));
                $this->_redirect('*/*/',array('_forced_secure' => TRUE));
                return;
            }

        } else {
            $this->_redirect('*/*/',array('_forced_secure' => TRUE));
        }
    }


    /**
     * Elle permet de recuperer les données selectionnées des produits, crée le panier et ridirige vers le tunnel d'achat
     */
    function formPostAction(){
        $post = $this->getRequest()->getPost();
        if ($post) {
            $session = Mage::getSingleton('checkout/session');
            try {
                Mage::getSingleton('checkout/cart')->truncate()->save();
                $session->setCartWasUpdated(true);
            } catch (Mage_Core_Exception $exception) {
                $session->addError($exception->getMessage());
            } catch (Exception $exception) {
                $session->addException($exception, $this->__('Cannot update shopping cart.'));
            }

            Mage::getSingleton('checkout/cart')->init();

            foreach($post as $id_field => $value){
                $field_data = explode("_", $id_field);
                $type = $field_data[0];
                $product_id = $field_data[1];
                if(isset($type) && isset($product_id) && $type === "product" && $value != "0"){
                    $product =  Mage::getModel('catalog/product')->load($product_id);
                    Mage::getSingleton('checkout/cart')->addProduct($product, array('qty' => $value));
                }
            }

            Mage::getSingleton('checkout/cart')->save();

            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
            $this->_redirect('checkout/onepage/',array('_forced_secure' => TRUE));
        }
        else{
            $this->_redirect('*/*',array('_forced_secure' => TRUE));
        }
    }
}
