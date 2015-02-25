<?php
class PWS_ProductRegistration_Block_Adminhtml_ProductSerialNumbers_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('productregistrationGrid');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('pws_productregistration/productserialnumberscollection');
        $collection->joinProducts();

        $this->setCollection($collection);



        parent::_prepareCollection();


        return $this;
    }

    public function getRowUrl($row)
    {
        return '';
    }

    protected function _prepareColumns()
    {
        $this->addColumn('sku', array(
            'header'    => Mage::helper('pws_productregistration')->__('Product SKU'),
            'align'     =>'left',
            'index'     => 'sku',
            'filter_condition_callback' => array($this, '_filterProductSkuCondition')
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('pws_productregistration')->__('Product Name'),
            'align'     =>'left',
            'index'     => 'product_name',
            'filter_condition_callback' => array($this, '_filterProductNameCondition')
        ));

        $this->addColumn('valid_serial_number', array(
            'header'    => Mage::helper('pws_productregistration')->__('Unused Serial Number'),
            'align'     =>'left',
            'index'     => 'valid_serial_number'
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
        $this->getCollection()->addFieldToFilter('`_table_product_name`.`value`', array('like' => '%'.$value.'%'));
    }

    protected function _filterProductSkuCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addFieldToFilter('`main_table`.`sku`', array('like' => '%'.$value.'%'));
    }

}
