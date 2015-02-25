<?php

class Fidesio_Preorder_Model_Order_Observer
{

    /**
     * Enregistre le code d'achat de la commande
     * @param Varien_Event_Observer $observer
     * @return Feed_Sales_Model_Order_Observer
     */
    public function save_code($observer)
    {
        $order = $observer->getOrder();
        if($order->getId()){
            // Récuperer le code
            $data = Mage::getSingleton('customer/session')->getData();
            if(isset($data['shopping_code'])){
                $code = $data['shopping_code'];
                $customer_id = $order->getCustomerId();
                $checkCode = Mage::getModel('preorder/code')->loadByCode($code);

                if($checkCode->getId()){
                    $checkCode->setOrderId($order->getIncrementId())
                        ->setCustomerId($customer_id)
                        ->setStatus(Fidesio_Preorder_Model_Code::STATUT_USED)
                        ->setUpdatedTime(Mage::getModel('core/date')->gmtDate());

                    $checkCode->save();

                    // Supprimer le code dans la session
                    Mage::getSingleton('customer/session')->unsShoppingCode();
                }
            }
        }
    }


    /**
     * Elle permet de controller si le code d'achat est active avant d'afficher le contenu
     * @param Varien_Event_Observer $observer
     * @return Feed_Sales_Model_Order_Observer
     */
    public function catalog_category_view_predispatch($observer){
        //$controller = $observer->getEvent()->getControllerAction();
        $url_preorder = Mage::getUrl('preorder');

        // Vérifier si le code n'est pas active sinon le balancer ailleurs
        $data = Mage::getSingleton('customer/session')->getData();
        if(isset($data['shopping_code'])){
            $code = $data['shopping_code'];
            $checkCode = Mage::getModel('preorder/code')->loadByCode($code);

            if(!$checkCode->getId()){
                // Code non valade ou déjà utilisé
                // Supprimer le code dans la session et redirigé vers la page du code d'achat
                Mage::getSingleton('customer/session')->unsShoppingCode();
                $response = Mage::app()->getFrontController()->getResponse();
                $response->setRedirect($url_preorder);
            }
        }
        else{
            $response = Mage::app()->getFrontController()->getResponse();
            $response->setRedirect($url_preorder);
        }
    }

    /**
     * Elle permet de recopier la valeur de la date de livraison du produit
     * dans l'item de la commande
     */
    function sales_quote_item_set_attribute($observer){
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $quoteItem->setShippingDate($product->getShippingDate());
    }
}