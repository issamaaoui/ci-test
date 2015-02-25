<?php

/**
 * @author te
 *
 */
class Te_Sapbyd_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_countryOptions;

    public function __construct()
    {
        parent::__construct();
        $this->setId('sapbyd_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_collection');
        
        
        $collection = mage::getModel("sales/order")->getCollection()
        ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING)
        ->addFieldToFilter('sapbyd_order_id', array("null"=>'123'))
        ;
        $this->setCollection($collection);
        $this->_joinAdress();
        return parent::_prepareCollection();
    }

    protected function _joinAdress()
    {
        $this->getCollection()
            ->addExpressionFieldToSelect('customer_name', "CONCAT({{firstname}},' ',{{lastname}})", array(
                'firstname' => 'customer_firstname',
                'lastname' => 'customer_lastname',
            ))->getSelect()
            ->joinLeft(
                array('billing' => $this->getCollection()->getTable('sales/order_address')),
                "(main_table.entity_id = billing.parent_id"
                    . " AND billing.address_type = 'billing')",
                array(
                    'billing_country_id' => 'billing.country_id',
                )
            )->joinLeft(
                array('shipping' => $this->getCollection()->getTable('sales/order_address')),
                "(main_table.entity_id = shipping.parent_id"
                    . " AND shipping.address_type = 'shipping')",
                array(
                    'shipping_country_id' => 'shipping.country_id',
                )
            );
    }

    protected function _getCountryOptions()
    {
        if (null === $this->_countryOptions) {
            $this->_countryOptions = array();
            $options = Mage::getResourceModel('directory/country_collection')->loadData()
                ->toOptionArray(false);
            foreach ($options as $data) {
                $name = Mage::app()->getLocale()->getCountryTranslation($data['value']);
                if (!empty($name)) {
                    $sort[$name] = $data['value'];
                }
            }

            Mage::helper('core/string')->ksortMultibyte($sort);
            foreach ($sort as $label => $value) {
                $this->_countryOptions[$value] = $label;
            }
        }

        return $this->_countryOptions;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '100px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('te_sapbyd')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '140px',
        ));

        $this->addColumn('customer_name', array(
            'header' => Mage::helper('sales')->__('Name'),
            'index' => 'customer_name',
        ));

        $this->addColumn('email', array(
            'header' => Mage::helper('sales')->__('Email'),
            'index' => 'customer_email',
        ));

        $this->addColumn('billing_country', array(
            'header' => Mage::helper('te_sapbyd')->__('Billing country'),
            'index' => 'billing_country_id',
            'type'  => 'options',
            'width' => '100px',
            'options' => $this->_getCountryOptions(),
        ));

        $this->addColumn('shipping_country', array(
            'header' => Mage::helper('te_sapbyd')->__('Shipping country'),
            'index' => 'shipping_country_id',
            'type'  => 'options',
            'width' => '100px',
            'options' => $this->_getCountryOptions(),
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'width' => '100px',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '100px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');

        if (Mage::getSingleton('admin/session')->isAllowed('dvl_sapbyd/sapbyd_order/actions/synchronize')) {
            $this->getMassactionBlock()->addItem('synchronize_order', array(
                 'label'=> Mage::helper('sales')->__('Synchronize to SAP'),
                 'url'  => $this->getUrl('*/*/massSynchronize'),
            ));
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
