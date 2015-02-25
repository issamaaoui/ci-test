<?php
class Te_Ajaxcart_CartController extends Mage_Core_Controller_Front_Action
{
  /**
     * Elle permet de recuperer les données selectionnées des produits, crée le panier et ridirige vers le tunnel d'achat
     */




    public function formPostAction()
    {
        $post = $this->getRequest()->getPost();
        if ($post) {
            $session = Mage::getSingleton('checkout/session');
            try {
               // Mage::getSingleton('checkout/cart')->truncate()->save();
        //foreach(Mage::getSingleton('checkout/cart')->getQuote()->getAllItems() as $item){
        //  $item->delete();
        //}
              //  $session->setCartWasUpdated(true);
            } catch (Mage_Core_Exception $exception) {
                $session->addError($exception->getMessage());
            } catch (Exception $exception) {
                $session->addException($exception, $this->__('Cannot update shopping cart.'));
            }

            //if(is_null(Mage::getSingleton('checkout/cart')))
        //Mage::getSingleton('checkout/cart')->init();

            foreach($post as $id_field => $value){
                $field_data = explode("_", $id_field);
                $type = $field_data[0];
                $product_id = $field_data[1];
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                if(isset($type) && isset($product_id) && $type === "product"){
                    //$quote = Mage::getSingleton('checkout/cart')->getQuote();
                    $product =  Mage::getModel('catalog/product')->load($product_id);
                    if(!$quote->hasProductId($product_id) && $value >0){
                        Mage::getSingleton('checkout/cart')->addProduct($product, array('qty' => $value));
                    }else{
                        if($quote->hasProductId($product_id) && $value >0){
                            $item = $quote->getItemByProduct($product);
                            Mage::getSingleton('checkout/cart')->updateItem($item->getId(),array("qty"=>$value));
                        }else{
                            if($quote->hasProductId($product_id) && $value ==0){
                                $product =  Mage::getModel('catalog/product')->load($product_id);
                                $item = $quote->getItemByProduct($product);
                                $cartHelper = mage::helper("checkout/cart");
                                $cartHelper->getCart()->removeItem($item->getId())->save(); 
                            }
                        }
                    }
                }
    //       Mage::getSingleton('checkout/cart')->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
            }

            $session = Mage::getSingleton('customer/session');

            if($session->getCouponPromo())
            {
                $quote =  Mage::getSingleton('checkout/cart')->getQuote();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode($session->getCouponPromo())->collectTotals()->save();
                $session->unsetData("coupon_promo");
            }

            Mage::getSingleton('checkout/cart')->save();
            //$session->setCartWasUpdated(true);
                
            Mage::getSingleton('checkout/cart')->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
            $data = Mage::getSingleton('checkout/cart')->getQuote()->getData();

            $itemsData = array();
            foreach(Mage::getSingleton('checkout/cart')->getQuote()->getAllItems() as $item)
            {
                $itemsData["id"] = $item->getId();
                $itemsData["product_id"] = $item->getProductId();
                $itemsData["qty"] = $item->getQty();
                $itemsData["price"] = $item->getPrice();
               
            }
            $data["items"] =  $itemsData;
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($data));

                //$this->_redirect('checkout/onepage/',array('_forced_secure' => TRUE));
        }
        else{
            //$this->_redirect('*/*',array('_forced_secure' => TRUE));
        }
    }

    public function indexAction()
    {
        $data = Mage::getSingleton('checkout/cart')->getQuote()->getData();
        $itemsData = Mage::getSingleton('checkout/cart')->getItems()->toArray();
        $data["items"] =  $itemsData;
        $data["discount_amount"] = mage::helper("te_coupon")->getDiscountAmount();
       // print_r($itemsData);
       $this->getResponse()->setHeader('Content-type', 'application/json');
       $this->getResponse()->setBody(json_encode($data));

    }


    protected function _getQuote(){
      return Mage::getSingleton('checkout/cart')->getQuote();
    }

    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
      

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();


        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }
        


        $data = Mage::getSingleton('checkout/cart')->getQuote()->getData();
        $itemsData = Mage::getSingleton('checkout/cart')->getItems()->toArray();
        $data["items"] =  $itemsData;
        $data["discount_amount"] = mage::helper("te_coupon")->getDiscountAmount();
        $data["coupon_is_applied"] = false;
        $data["coupon_message"] = mage::helper("te_coupon")->__('Invalid gift code');

        if($couponCode == $this->_getQuote()->getCouponCode() && strlen($couponCode) > 0)
             $data["coupon_is_applied"] = true;

       // print_r($itemsData);
       $this->getResponse()->setHeader('Content-type', 'application/json');
       $this->getResponse()->setBody(json_encode($data));
    }
    
}
