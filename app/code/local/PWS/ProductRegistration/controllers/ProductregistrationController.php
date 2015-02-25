<?php
class PWS_ProductRegistration_ProductregistrationController extends Mage_Core_Controller_Front_Action
{

    /**
     * Customer registered products history
     */
    public function historyAction()
    {
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('pws_productregistration')->getRegisteredProductsTitle());

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }


    //----------------------------------------------------------------------
    //					START REGISTRATION
    //-----------------------------------------------------------------------


    /**
     * for testing
     */
    public function clearSessAction()
    {
    	Mage::getSingleton('pws_productregistration/session')->clear();
    	Mage::getSingleton('customer/session')->logout();
    	$this->_redirect('*/*/setLanguage');
       	return;
    }


    public function setRegistrationMethodAction()
    {
        //user is already logged in
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
        	Mage::getSingleton('pws_productregistration/session')->setRegistrationMethod('login');
        	$this->_redirect('*/*/registerProduct');
		    return;
		}


    	$this->loadLayout();
        $this->_initLayoutMessages('pws_productregistration/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('Registration Method'));


        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('registration_method')) {

        	if ($this->getRequest()->getPost('registration_method') == 'signup') {
        		Mage::getSingleton('pws_productregistration/session')->setRegistrationMethod($this->getRequest()->getPost('registration_method'));
		    	$this->_redirect('*/*/registerProduct');
		    	return;

        	} elseif ($this->getRequest()->getPost('registration_method') == 'login') {

        		//user is already logged in
        		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        			Mage::getSingleton('pws_productregistration/session')->setRegistrationMethod($this->getRequest()->getPost('registration_method'));
        			$this->_redirect('*/*/registerProduct');
		    		return;
        		} else {
        			//redirect to loggin form
        			$loginUrl = Mage::helper('customer')->getLoginUrl();

        			Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
                	$this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
                	return;
            	}

        	} else {
        		$message = $this->__('Invalid registration method');
        		Mage::getSingleton('pws_productregistration/session')->addError($message);
        		$this->_redirect('*/*/*');
        		return;
        	}
        }

        $this->renderLayout();
    }

    /**
     * Register product form
     */
    public function registerProductAction()
    {

        // user is already logged in and entered directly on this page
        if (!(Mage::getSingleton('pws_productregistration/session')->getRegistrationMethod())
            && Mage::getSingleton('customer/session')->isLoggedIn()) {
        	Mage::getSingleton('pws_productregistration/session')->setRegistrationMethod('login');
		}

        if (!(Mage::getSingleton('pws_productregistration/session')->getRegistrationMethod())) {
        	$message = $this->__('Please select a registration method first');
        	Mage::getSingleton('pws_productregistration/session')->addError($message);
        	$this->_redirect('*/*/setRegistrationMethod');
        	return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('pws_productregistration/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('Register Products'));


        if ($this->getRequest()->isPost()) {
        		$registration_data = array(
        			'customer'           => $this->getRequest()->getPost('customer'),
        			'registered_product' => $this->getRequest()->getPost('registered_product'),
        		);

        		// reformat product array: product_id=>array(), serial_number=>array() => array('product_id'=>'','serial_number'=>'')
        		$registration_data['registered_product'] = Mage::helper('pws_productregistration')
                    ->formatProductData($registration_data['registered_product']);

                // verify if data is valid
                $errors = Mage::getModel('pws_productregistration/productregistration')
                    ->validate($registration_data['registered_product']);

                if ($errors) {
                    foreach ($errors as $error) {
                        Mage::getSingleton('pws_productregistration/session')->addError($error);
                    }
                    Mage::getSingleton('pws_productregistration/session')->setRegistrationData($registration_data);
                    $this->_redirect('*/*/registerProduct');
                    return;
                } else {
                    Mage::getSingleton('pws_productregistration/session')->setRegistrationData($registration_data);
                    $this->_redirect('*/*/previewRegisterProduct');
                }
        }

        if (Mage::getSingleton('pws_productregistration/session')->getRegistrationData()) {
        	Mage::register('registration_data', Mage::getSingleton('pws_productregistration/session')->getRegistrationData());
        }

        $this->renderLayout();
    }


    public function previewRegisterProductAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('pws_productregistration/session');

        if (!Mage::getSingleton('pws_productregistration/session')->getRegistrationData()) {
        	$message = $this->__('Please fill out the form first');
        	Mage::getSingleton('pws_productregistration/session')->addError($message);
        	$this->_redirect('*/*/registerProduct');
        	return;
        }

        $this->getLayout()->getBlock('head')->setTitle($this->__('View registrations'));

        Mage::register('registration_data', Mage::getSingleton('pws_productregistration/session')->getRegistrationData());

        $this->renderLayout();
    }


    public function saveRegisteredProductAction()
    {

        if (!Mage::getSingleton('pws_productregistration/session')->getRegistrationData()){
        	$message = $this->__('Please fill out the form first');
        	Mage::getSingleton('pws_productregistration/session')->addError($message);
        	$this->_redirect('*/*/registerProduct');
        	return;
        }

        if (!$this->getRequest()->isPost()) return;

        try {
            $registration_data = Mage::getSingleton('pws_productregistration/session')->getRegistrationData();

            $customer_id              = '';
            $created_customer_account = false;

            // logged in customer
            if(Mage::getSingleton('customer/session')->getCustomerId()){
                $customer_id = Mage::getSingleton('customer/session')->getCustomerId();

                // update customer account info
                $this->updateCustomerAccountInfo($registration_data['customer']);

                // update customer default address
                $this->updateCustomerAddressInfo($registration_data['customer']);
            } else {
                // create new customer account
                $customer_id = $this->createCustomerAccount($registration_data['customer']);

                $created_customer_account = true;

                if (!$customer_id) Mage::throwException(Mage::helper('pws_productregistration')->__('Cannot save contact information'));
            }

            //save registered products
            Mage::getModel('pws_productregistration/productregistration')->saveRegisteredProducts($customer_id, $registration_data['registered_product']);

            //send email
            $registration_data['created_customer_account'] = $created_customer_account;
            Mage::helper('pws_productregistration')->sendEmail($registration_data);

            // notifiy admin
            if (Mage::helper('pws_productregistration')->canReceiveEmailNotificationRegistration()) {
                Mage::helper('pws_productregistration')->sendAdminEmailNotification($registration_data);
            }

            Mage::getSingleton('pws_productregistration/session')->addSuccess($this->__('Your Contact Information and your Registered Products have been successfully saved'));

            $this->_redirect('*/*/registrationSuccess');

        } catch (Zend_Mail_Exception $e) {
            Mage::getSingleton('pws_productregistration/session')->addSuccess($this->__('Your Contact Information and your Registered Products have been successfully saved'));
            Mage::getSingleton('pws_productregistration/session')->addError($e->getMessage());
            $this->_redirect('*/*/registrationSuccess');
        } catch(Exception $e) {
            Mage::getSingleton('pws_productregistration/session')->addError($e->getMessage());
            $this->_redirect('*/*/previewRegisterProduct');
        }
    }

    public function registrationSuccessAction()
    {

		$this->loadLayout();
        $this->_initLayoutMessages('pws_productregistration/session');

        if (!Mage::getSingleton('pws_productregistration/session')->getRegistrationData()) {
        	$message = $this->__('Please fill out the form first');
        	Mage::getSingleton('pws_productregistration/session')->addError($message);
        	$this->_redirect('*/*/registerProduct');
        	return;
        }

        $this->getLayout()->getBlock('head')->setTitle($this->__('View registrations'));

        Mage::register('registration_data', Mage::getSingleton('pws_productregistration/session')->getRegistrationData());

        //remove post form data
		Mage::getSingleton('pws_productregistration/session')->setRegistrationData('');
		Mage::getSingleton('pws_productregistration/session')->clear();

        $this->renderLayout();
    }


    private function createCustomerAccount($customer_data)
    {
        $customer = Mage::getModel('customer/customer')->setId(null);
    	$customer->setData('firstname', $customer_data['firstname']);
    	$customer->setData('lastname', $customer_data['lastname']);
    	$customer->setData('email', $customer_data['email']);
    	$customer->setData('password', $customer_data['password']);
    	$customer->setData('confirmation', $customer_data['confirmation']);

        if (isset($customer_data['dob'])) {
           $customer->setData('dob', $customer_data['dob']);
        }

    	$customer->getGroupId();

    	// set address
    	$address = Mage::getModel('customer/address')
                    ->setData($customer_data)
                    ->setIsDefaultBilling(1)
                    ->setIsDefaultShipping(1)
                    ->setId(null);
        $customer->addAddress($address);

        $errors = $address->validate();
        if (!is_array($errors)) {
            $errors = array();
        }

		$validationCustomer = $customer->validate();
		if (is_array($validationCustomer)) {
			$errors = array_merge($validationCustomer, $errors);
		}
		$validationResult = count($errors) == 0;

		if (true === $validationResult) {
			$customer->save();
			Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

		} else {
			if (is_array($errors)) {
			    foreach ($errors as $errorMessage) {
			        Mage::getSingleton('pws_productregistration/session')->addError($errorMessage);
			    }
			}
			else {
			    Mage::getSingleton('pws_productregistration/session')->addError($this->__('Invalid customer data'));
			}
			return false;
		}


        Mage::getSingleton('pws_productregistration/session')->setEscapeMessages(true);
        return $customer->getId();
    }


    private function updateCustomerAccountInfo($customer_data)
    {
		$customer = Mage::getModel('customer/customer')
			->setId(Mage::getSingleton('customer/session')->getCustomerId())
			->setWebsiteId(Mage::getSingleton('customer/session')->getCustomer()->getWebsiteId());

		$customer->setData('firstname', $customer_data['firstname']);
		$customer->setData('lastname', $customer_data['lastname']);
		$customer->setData('email', $customer_data['email']);

        if (isset($customer_data['dob'])) {
           $customer->setData('dob', $customer_data['dob']);
        }

		$errors = $customer->validate();
		if (!is_array($errors)) {
			$errors = array();
		}

		/**
		* we would like to preserver the existing group id
		*/
		if (Mage::getSingleton('customer/session')->getCustomerGroupId()) {
			$customer->setGroupId(Mage::getSingleton('customer/session')->getCustomerGroupId());
		}

		if (!empty($errors)) {
            foreach ($errors as $message) {
                Mage::getSingleton('pws_productregistration/session')->addError($message);
            }
            Mage::throwException(Mage::helper('pws_productregistration')->__('Cannot save contact information'));
        }else{
        	$customer->save();
        }

    }

    private function updateCustomerAddressInfo($customer_data)
    {

    	$address = Mage::getModel('customer/address')
                ->setData($customer_data)
                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                ->setIsDefaultBilling(1)
                ->setIsDefaultShipping(1);
    	$addressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();

        if ($addressId) {
            $customerAddress = Mage::getSingleton('customer/session')->getCustomer()->getAddressById($addressId);
            if ($customerAddress->getId() && $customerAddress->getCustomerId() == Mage::getSingleton('customer/session')->getCustomerId()) {
                $address->setId($addressId);
            }
            else {
                $address->setId(null);
            }
        }
        else {
            $address->setId(null);
        }

        $addressValidation = $address->validate();
        if (true === $addressValidation) {
            $address->save();
        } else {
            if (is_array($addressValidation)) {
                foreach ($addressValidation as $errorMessage) {
                	Mage::getSingleton('pws_productregistration/session')->addError($errorMessage);
                	Mage::throwException(Mage::helper('pws_productregistration')->__('Cannot save contact address information'));
                }
            } else {
            	Mage::throwException(Mage::helper('pws_productregistration')->__('Cannot save contact address information'));
            }
        }

    }
}
