<?php

class Fidesio_Preorder_Block_Adminhtml_Code_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct()
    {
        parent::__construct();
        $this->setId('codeGrid');
        $this->setDefaultSort('auto_id');
        $this->setDefaultDir('ASC');

        if ($customer_id = $this->getRequest()->getParam('customer'))
            $this->setDefaultFilter(array('customer_id' => $customer_id));

        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('preorder/code')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns() {
        $this->addColumn('auto_id', array(
            'header' => Mage::helper('preorder')->__('ID'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'auto_id',
        ));

        $this->addColumn('code', array(
            'header' => Mage::helper('preorder')->__('Shopping code'),
            'align' => 'left',
            'index' => 'code',
        ));

        $this->addColumn('customer_id', array(
            'header' => Mage::helper('adminhtml')->__('Customer'),
            'align' => 'left',
            'index' => 'customer_id',
            'renderer'  => 'preorder/adminhtml_code_grid_renderer_customer'
        ));

        $this->addColumn('order_id', array(
            'header' => Mage::helper('adminhtml')->__('Commande'),
            'align' => 'left',
            'index' => 'order_id',
            'renderer'  => 'preorder/adminhtml_code_grid_renderer_order'
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('cms')->__('Status'),
            'index'     => 'status',
            'align' => 'left',
            'width' => '150px',
            'type'      => 'options',
            'options'   => Fidesio_Preorder_Model_Code::getStatusOption()
        ));

        $this->addColumn('created_time', array(
            'header' => Mage::helper('preorder')->__('Created at'),
            'index' => 'created_time',
            'type' => 'datetime',
            'width' => '150px',
            'gmtoffset' => true,
            'default' => ' -- '
        ));

        $this->addColumn('updated_time', array(
            'header' => Mage::helper('preorder')->__('Updated at'),
            'index' => 'updated_time',
            'width' => '150px',
            'type' => 'datetime',
            'gmtoffset' => true,
            'default' => ' -- '
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('preorder')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('adminhtml')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('code_id');
        $this->getMassactionBlock()->setFormFieldName('code');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('adminhtml')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('preorder')->__('Etes vous sûre de vouloir supprimer ce code ?')
        ));
        $statuses = Fidesio_Preorder_Model_Code::getStatusValues();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('preorder')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('preorder')->__('Statut'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }


    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}