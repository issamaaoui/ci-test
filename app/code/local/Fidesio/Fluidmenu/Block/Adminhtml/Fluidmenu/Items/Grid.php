<?php
class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu_Items_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('fluidmenu_itemGrid');
        $this->setDefaultSort('menu_id');
        $this->setDefaultDir('ASC');

        if ($menu_id = $this->getRequest()->getParam('menu'))
            $this->setDefaultFilter(array('menu_id' => $menu_id));
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('fluidmenu_item_id', array(
            'header' => Mage::helper('fluidmenu')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'fluidmenu_item_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('fluidmenu')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));
        $this->addColumn('menu_id', array(
            'header' => Mage::helper('fluidmenu')->__('Menu'),
            'align' => 'left',
            'index' => 'menu_id',
            'type' => 'options',
            'options' => Mage::getModel('fluidmenu/fluidmenu')->getGridInputSelectMenu()
        ));

        $this->addColumn('parent_id', array(
            'header' => Mage::helper('fluidmenu')->__('Parent'),
            'align' => 'left',
            'index' => 'parent_id',
            'type'  => 'options',
            'options' => Mage::getModel('fluidmenu/fluidmenu_items')->getGridInputSelectParentItem()
        ));

        $this->addColumn('cms_type', array(
            'header' => Mage::helper('fluidmenu')->__('Type de lien'),
            'align' => 'left',
            'index' => 'cms_type',
            'type'  => 'options',
            'options' => Mage::getModel('fluidmenu/fluidmenu_items')->getGridInputSelectCmsType()
        ));
        $this->addColumn('level', array(
            'header' => Mage::helper('fluidmenu')->__('Niveau'),
            'align' => 'left',
            'index' => 'level',
            'width' => '50px',
            'align' => 'center'
        ));
        $this->addColumn('position', array(
            'header' => Mage::helper('fluidmenu')->__('Position'),
            'align' => 'left',
            'index' => 'position',
            'width' => '50px',
            'align' => 'center'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('fluidmenu')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Activé',
                2 => 'Désactivé',
            ),
        ));

        $this->addColumn('url', array(
            'header' => Mage::helper('fluidmenu')->__('Url'),
            'align' => 'left',
            'index' => 'url',
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'fluidmenu/adminhtml_fluidmenu_items_grid_renderer_action'
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('fluidmenu')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('fluidmenu')->__('Edit'),
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
        $this->setMassactionIdField('fluidmenu_item_id');
        $this->getMassactionBlock()->setFormFieldName('fluidmenu');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('fluidmenu')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('fluidmenu')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('fluidmenu/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('fluidmenu')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('fluidmenu')->__('Status'),
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