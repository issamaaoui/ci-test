<?php
class PWS_ProductRegistration_Helper_Data extends Mage_Core_Helper_Abstract
{

   const XML_PATH_EMAIL_IDENTITY                       = 'default/pws_productregistration/emails/email_identity';
   const XML_PATH_EMAIL_TEMPLATE                       = 'pws_productregistration_email_template';
   const XML_PATH_EMAIL_NOTIFICATION_INVALID_TEMPLATE  = 'pws_productregistration_email_notification_invalid_template';
   const XML_PATH_EMAIL_NOTIFICATION_ADMIN_TEMPLATE    = 'pws_productregistration_email_notification_admin_template';

	/**
	 *	reformat product array: product_id=>array(), serial_number=>array() => array('product_id'=>'','serial_number'=>'')
	 */
	public function formatProductData($product_data)
	{
		$formated_product_data = array();

		$num_products = count($product_data['serial_number']);

		for ($i=0;$i<$num_products; $i++) {
            $serialNumber = $product_data['serial_number'][$i];
            $productId    = isset($product_data['product_id'])   ?  $product_data['product_id'][$i]        : null;
            $productSku   = isset($product_data['product_sku'])  ? trim($product_data['product_sku'][$i])  : null;
            $productName  = isset($product_data['product_name']) ? trim($product_data['product_name'][$i]) : null;

            if ($productSku) {
               $productId = Mage::getModel('catalog/product')->getIdBySku($productSku);
            } elseif (!$productSku && $productId) {
               $productSku = Mage::getModel('catalog/product')->load($productId)->getSku();
            } elseif ($productName) {
                $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())
                    ->getCollection()
                    ->addAttributeToFilter('name', $productName)
                    ->getFirstItem();
                $productId = $product->getId();
            }

            if (!$productName) {
                $productName = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId)->getName();
            }

			$formated_product_data[$i] = array(
				'product_id'       => trim($productId),
                'product_sku'      => trim($productSku),
                'product_name'     => trim($productName),
                'store_id'         => Mage::app()->getStore()->getId(),
				'serial_number'    => trim($serialNumber),
				'date_of_purchase' => trim($product_data['date_of_purchase'][$i]),
				'purchased_from'   => trim($product_data['purchased_from'][$i]),
			);
		}

		return $formated_product_data;
	}

    public function getProductIdForSerialNumber($serialNumber)
    {
        $productSku = Mage::getModel('pws_productregistration/adminhtml_system_config_backend_serialnumbers')
            ->getProductSkuForSerialNumber($serialNumber);

        return Mage::getModel('catalog/product')->getIdBySku($productSku);
    }

	public function getProductName($productId)
	{
		$productModel = Mage::getModel('catalog/product')->load($productId);

		return $productModel->getName();
	}

    public function getProductSku($productId)
	{
		$productModel = Mage::getModel('catalog/product')->load($productId);

		return $productModel->getSku();
	}


	public function getCountryName($countryId)
	{
		$countryModel = Mage::getModel('directory/country')->loadByCode($countryId);
		return $countryModel->getName();
	}


	public function sendEmail($emailData)
	{

        $storeID = Mage::app()->getStore()->getId();

		$translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $result = Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeID));

        $emailData['registered_products_html'] = Mage::getModel('core/layout')
            ->createBlock('PWS_ProductRegistration_Block_RegisterProducts_Email')
                ->setBlockId('emails')
                ->setArea('frontend')
                ->addData(array(
                        'registered_products' => $emailData['registered_product'],
                        'customer_data'       => $emailData['customer']
                        )
                    )
                ->toHtml();

        $result->sendTransactional(
                self::XML_PATH_EMAIL_TEMPLATE,
                Mage::getConfig()->getNode(self::XML_PATH_EMAIL_IDENTITY),
                $emailData['customer']['email'],
                $emailData['customer']['firstname'].' '.$emailData['customer']['lastname'],
                $emailData,
                $storeID
               );
        //var_dump($result->getProcessedTemplate($emailData));
        //die('the email template');

        $translate->setTranslateInline(true);

        return $result;
	}


	public function getRegistrableProductOptions()
	{

		$productCollection = Mage::getModel('catalog/product')->getCollection()
								->addAttributeToSelect('name')
								->addAttributeToSelect('sku')
								->addAttributeToFilter('is_registrable', 1)
								->setOrder('name', 'ASC')
								->addStoreFilter();

		//make option array
		$options[0] = array('value'=>'','label'=>'');
		if($productCollection->getSize()){
			foreach($productCollection as $product){
					//var_dump($product->toArray());
					$options[] = array('value'=>$product->getEntityId(), 'label' => $product->getName());
			}
		}

		return $options;
	}


    public function getConfig()
    {
        $config = array(
            'sn_validation_enabled'                => Mage::getStoreConfig('pws_productregistration/serial_number_validation/sn_validation_enabled', Mage::app()->getStore()->getId()),
            'duplicate_sn'                         => Mage::getStoreConfig('pws_productregistration/serial_number_validation/duplicate_sn', Mage::app()->getStore()->getId()),
            'validation_type'                      => Mage::getStoreConfig('pws_productregistration/serial_number_validation/validation_type', Mage::app()->getStore()->getId()),
            'validation_rule'                      => Mage::getStoreConfig('pws_productregistration/serial_number_validation/validation_rule', Mage::app()->getStore()->getId()),
            'register_product_input'               => Mage::getStoreConfig('pws_productregistration/general_settings/register_product_input', Mage::app()->getStore()->getId()),
            'receive_email_notification'           => Mage::getStoreConfig('pws_productregistration/general_settings/receive_email_notification', Mage::app()->getStore()->getId()),
            'send_email_notification_to'           => Mage::getStoreConfig('pws_productregistration/general_settings/send_email_notification_to', Mage::app()->getStore()->getId()),
            'notify_customer_registration_invalid' => Mage::getStoreConfig('pws_productregistration/general_settings/notify_customer_registration_invalid', Mage::app()->getStore()->getId()),
            'import_mode'                          => Mage::getStoreConfig('pws_productregistration/import_settings/import_mode', Mage::app()->getStore()->getId()),
            'registered_products_title'            => Mage::getStoreConfig('pws_productregistration/general_settings/registered_products_title', Mage::app()->getStore()->getId()),
        );

        return $config;
    }

    public function getRegisteredProductsTitle()
    {
        $config = $this->getConfig();
        return empty($config['registered_products_title']) ? $this->__('My Registered Products') : $config['registered_products_title'];
    }

    public function isSerialNumberValidationEnabled()
    {
        $config = $this->getConfig();
        return (bool) $config['sn_validation_enabled'];
    }

    public function allowDuplicateSerialNumbers()
    {
        $config = $this->getConfig();
        return (bool) $config['duplicate_sn'];
    }


    public function useProductSkuInput()
    {
        $config = $this->getConfig();
        return $config['register_product_input'] == PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Productinput::PRODUCT_FIELD_INPUT_SKU;
    }

    public function useProductNameInput()
    {
        $config = $this->getConfig();

        return $config['register_product_input'] == PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Productinput::PRODUCT_FIELD_INPUT_NAME;
    }

    public function useProductSelectInput()
    {
        $config = $this->getConfig();
        $model  = Mage::getModel('pws_productregistration/adminhtml_system_config_source_productinput');

        return (!$this->useProductSkuInput() && !$this->useProductNameInput());
    }

    public function notifyCustomerInvalidRegistration()
    {
        $config = $this->getConfig();

        return (bool) $config['notify_customer_registration_invalid'];
    }

    public function canReceiveEmailNotificationRegistration()
    {
        $config = $this->getConfig();

        return (bool) $config['receive_email_notification'];
    }

    /**
     * @param PWS_ProductRegistration_Model_Productregistration $productRegistrationModel
     * @return bool
     */
    public function sendCustomerNotificationEmail($productRegistrationModel)
	{
        $customerInfo = Mage::getModel('customer/customer')->load($productRegistrationModel->getCustomerId());
        $productInfo  = Mage::getModel('catalog/product')->load($productRegistrationModel->getProductId());

        $emailData = array(
            'customer'           => array(),
            'registered_product' => array(),
        );

        $emailData['customer']['email']     = $customerInfo->getEmail();
        $emailData['customer']['firstname'] = $customerInfo->getFirstname();
        $emailData['customer']['lastname']  = $customerInfo->getLastname();
        $emailData['registered_product']    = array(
            'registered_on'    => Mage::helper('core')->formatDate($productRegistrationModel->getCreatedOn(), 'medium'),
            'serial_number'    => $productRegistrationModel->getSerialNumber(),
            'date_of_purchase' => Mage::helper('core')->formatDate($productRegistrationModel->getDateOfPurchase(), 'medium'),
            'purchased_from'   => $productRegistrationModel->getPurchasedFrom(),
            'product_name'     => $productInfo->getName(),
            'product_sku'      => $productInfo->getSku(),
            'customer_name'    => $emailData['customer']['firstname'] . ' ' . $emailData['customer']['lastname']
        );

        $storeID = Mage::app()->getStore()->getId();

		$translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $result = Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeID));

        $result->sendTransactional(
                self::XML_PATH_EMAIL_NOTIFICATION_INVALID_TEMPLATE,
                Mage::getConfig()->getNode(self::XML_PATH_EMAIL_IDENTITY),
                $emailData['customer']['email'],
                $emailData['customer']['firstname'].' '.$emailData['customer']['lastname'],
                $emailData['registered_product'],
                $storeID
               );
        //var_dump($result->getProcessedTemplate($emailData));
        //die('the email template');

        $translate->setTranslateInline(true);

        return $result;
	}

    /**
     * Notify admin that a new product was registered
     *
     */
    public function sendAdminEmailNotification($emailData)
	{

        $config                = $this->getConfig();
        $emailData['to_email'] = $config['send_email_notification_to'];
        if (empty($emailData['to_email'])) return;

        $storeID = Mage::app()->getStore()->getId();

		$translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $result = Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeID));

        $emailData['registered_products_html'] = Mage::getModel('core/layout')
            ->createBlock('PWS_ProductRegistration_Block_RegisterProducts_Email')
                ->setBlockId('emails')
                ->addData(array(
                        'registered_products' => $emailData['registered_product'],
                        'customer_data'       => $emailData['customer']
                        )
                    )
                ->toHtml();

        $result->sendTransactional(
                self::XML_PATH_EMAIL_NOTIFICATION_ADMIN_TEMPLATE,
                Mage::getConfig()->getNode(self::XML_PATH_EMAIL_IDENTITY),
                $emailData['to_email'],
                Mage::getConfig()->getNode(self::XML_PATH_EMAIL_IDENTITY),
                $emailData,
                $storeID
               );
        $translate->setTranslateInline(true);

        return $result;
	}

	/* @TODO */
	public function findProduct($serialNumber)
	{
	    $table = Mage::getSingleton('core/resource')->getTableName('pws_productregistration/serial_numbers');
	    
	    $this->getSelect()->from(array('main_table' => $table));
	    $this->_setIdFieldName('id');
	    return $this;
	    
	}
	
	
}
