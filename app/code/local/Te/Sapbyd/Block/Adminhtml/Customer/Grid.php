<?php

/**
 * @author te
 *
 */
class Te_Sapbyd_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sapbyd_customer_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }


    protected function _joinCustomerAttribute($code, $collection){
        
        $attribute = Mage::getModel( 'eav/config' )->getAttribute( 'customer' , $code );
        $type = $attribute->getBackendType();
        $collection->getSelect()->joinLeft(array('customer_'.$code => $collection->getTable('customer/entity')."_".$type), 'e.entity_id = customer_'.$code.'.entity_id and customer_'.$code.'.attribute_id = '.$attribute->getId(), array($code=>"value"));
    }

    

    protected function _prepareCollection()
    {
         $orders = mage::getModel("sales/order")->getCollection()
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING)
            ->addFieldToFilter('sapbyd_order_id', array("null"=>'123'));
        $orders->getSelect()->reset(Zend_Db_Select::COLUMNS);

        $orders->getSelect()->columns("customer_id")->group("customer_id");

        

        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('website_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
            $this->_joinCustomerAttribute("sapbyd_customer_id", $collection);
            $collection->getSelect()
                ->where(new Zend_Db_Expr("customer_sapbyd_customer_id.value is NULL"))
                ->where("e.entity_id in (".new Zend_Db_Expr( $orders->getSelect()->__toString()).")");


        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));
        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

       if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }

        $this->addColumn('Telephone', array(
            'header'    => Mage::helper('customer')->__('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone'
        ));

        $this->addColumn('billing_postcode', array(
            'header'    => Mage::helper('customer')->__('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer_ids');

        if (Mage::getSingleton('admin/session')->isAllowed('dvl_sapbyd/sapbyd_customer/actions/synchronize')) {
            $this->getMassactionBlock()->addItem('synchronize_order', array(
                 'label'=> Mage::helper('sales')->__('Synchronize to SAP'),
                 'url'  => $this->getUrl('*/*/massSynchronize'),
            ));
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }
}
