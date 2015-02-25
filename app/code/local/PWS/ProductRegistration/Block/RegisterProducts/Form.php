<?php
class PWS_ProductRegistration_Block_RegisterProducts_Form extends Mage_Directory_Block_Data
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('pws_productregistration/registerproducts/form.phtml');
    }

    public function isNewAccount()
    {
    	if (Mage::getSingleton('customer/session')->getCustomerId()) return false;
    	return true;
    }


    public function getProductHtmlOptions($defValue=null, $name='product_id', $id='product', $title='Product')
    {

		$productCollection = Mage::getResourceModel('catalog/product_collection');
        $productCollection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $productCollection
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToFilter('is_registrable', 1)
            ->addStoreFilter(1)
            ->setOrder('name', 'ASC');

		//make option array
		$options[0] = array('value'=>'','label'=>'');
		if ($productCollection->getSize()) {
			foreach($productCollection as $product) {
                $options[] = array('value'=>$product->getEntityId(), 'label' => $product->getName());
			}
		}

		$html = $this->getLayout()->createBlock('core/html_select')
            ->setName($name)
            ->setTitle(Mage::helper('pws_productregistration')->__($title))
            ->setClass('validate-select')
            ->setValue($defValue)
            ->setOptions($options)
            ->getHtml();
		return $html;
    }

    public function getProductJsonOptions()
    {
        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $productCollection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $productCollection
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToFilter('is_registrable', 1)
            ->addStoreFilter()
            ->setOrder('name', 'ASC');

		$options = array();
		if ($productCollection->getSize()) {
			foreach($productCollection as $product) {
                $options[] = $product->getName();
			}
		}

        return json_encode($options);
    }


    public function getCountryHtmlOptions($defValue=null, $name='country_id', $id='country', $title='Country')
    {
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }

        if ($options == false) {
            $options = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore()
                ->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }

        $html = $this->getLayout()->createBlock('core/html_select')
            ->setName($name)
            ->setId($id)
            ->setTitle(Mage::helper('pws_productregistration')->__($title))
            ->setClass('validate-select')
            ->setValue($defValue)
            ->setOptions($options)
            ->getHtml();

		return $html;

    }

	public function getFormData()
	{
	 	$form_data = array(
	 		'customer'=>array(
			   	 	'title' => '',
					'firstname' => '',
					'lastname' => '',
					'street' => array(0=>'',1=>''),
					'city' => '',
					'region' => '',
					'region_id' => '',
					'country_id' => '',
					'postcode' => '',
					'telephone' => '',
					'mobile' => '',
					'email' => '',
					'fax' => '',
                    'dob' => '',
			   	 	),
			'registered_product'=>array()
		);

		$logged_customer_data = '';
		$logged_customer      = Mage::getSingleton('customer/session')->getCustomerId();

		if ($logged_customer) {

			//we have an address
			if(Mage::getSingleton('customer/session')->getCustomer()->getPrimaryAddress('default_billing')){
				$logged_customer_data = Mage::getSingleton('customer/session')->getCustomer()
										->getPrimaryAddress('default_billing')->getData();

				$logged_customer_data['street'] = explode("\n",$logged_customer_data['street']);
				if(!isset($logged_customer_data['street'][1])){
					$logged_customer_data['street'][1] = '';
				}
			}

			$logged_customer_data['email']     = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
			$logged_customer_data['firstname'] = Mage::getSingleton('customer/session')->getCustomer()->getFirstname();
			$logged_customer_data['lastname']  = Mage::getSingleton('customer/session')->getCustomer()->getLastname();
		}

	 	if (Mage::registry('registration_data')) {
	 		// some fields might not be defined in logged_customer_data
	 		$form_data = array_merge($form_data, Mage::registry('registration_data'));

	 	} else if ($logged_customer_data) {
	 		// some fields might not be defined in logged_customer_data
	 		$form_data['customer'] = array_merge($form_data['customer'], $logged_customer_data);
	 	}

	 	$form_data['customer']['region_id'] = (empty($form_data['customer']['region_id'])) ? '': $form_data['customer']['region_id'];

        return $form_data;
    }


}
