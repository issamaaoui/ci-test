<?php
class PWS_ProductRegistration_Block_Adminhtml_ProductRegistration_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('productregistrationGrid');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('pws_productregistration/productregistration')
            ->getCollection()->joinCustomers()->joinProducts();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }


    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('pws_productregistration')->__('Customer ID'),
            'align'     =>'left',
            'index'     => 'customer_id',
        ));

        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('pws_productregistration')->__('Customer Name'),
            'align'     =>'left',
            'index'     => 'customer_name',
            'filter_condition_callback' => array($this, '_filterCustomerNameCondition')
        ));

        if(!Mage::helper('pws_productregistration')->useProductNameInput()) {
            $this->addColumn('product_id', array(
                'header'    => Mage::helper('pws_productregistration')->__('Product ID'),
                'align'     =>'left',
                'index'     => 'product_id',
            ));

            $this->addColumn('sku', array(
                'header'    => Mage::helper('pws_productregistration')->__('Sku'),
                'align'     =>'left',
                'index'     => 'sku',
            ));
        }

        $this->addColumn('actual_product_name', array(
            'header'    => Mage::helper('pws_productregistration')->__('Product Name'),
            'align'     =>'left',
            'index'     => 'actual_product_name',
            'filter_condition_callback' => array($this, '_filterProductNameCondition')
        ));

        $this->addColumn('serial_number', array(
            'header'    => Mage::helper('pws_productregistration')->__('Serial number'),
            'align'     =>'left',
            'index'     => 'serial_number',
        ));

         $this->addColumn('date_of_purchase', array(
            'header'    => Mage::helper('pws_productregistration')->__('Date of purchase'),
            'align'     =>'left',
            'index'     => 'date_of_purchase',
            'type'      => 'date',
        ));

        $this->addColumn('purchased_from', array(
            'header'    => Mage::helper('pws_productregistration')->__('Purchased From'),
            'align'     =>'left',
            'index'     => 'purchased_from',
        ));

        $this->addColumn('is_valid', array(
            'header'    => Mage::helper('pws_productregistration')->__('Is valid'),
            'align'     =>'left',
            'index'     => 'is_valid',
        ));

        $this->addColumn('created_on', array(
            'header'    => Mage::helper('pws_productregistration')->__('Created On'),
            'align'     =>'left',
            'index'     => 'created_on',
            'type'      => 'datetime',
        ));


        $this->addExportType('*/*/exportCsv', Mage::helper('pws_productregistration')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('pws_productregistration')->__('XML'));

        return parent::_prepareColumns();
    }


    protected function _filterProductNameCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter(
            'if(length(_table_product_name.value) > 0, _table_product_name.value, main_table.product_name)', array('like' => '%'.$value.'%')
        );
    }


    protected function _filterCustomerNameCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter('CONCAT(_table_customer_firstname.value, \' \', _table_customer_lastname.value)', array('like' => '%'.$value.'%'));
    }

    protected function _filterCustomerEmailCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter('email', array('like' => '%'.$value.'%'));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('registration_id');
        $this->getMassactionBlock()->setFormFieldName('registration');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('pws_productregistration')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('pws_productregistration')->__('Are you sure?')
        ));


        return $this;
    }
}
