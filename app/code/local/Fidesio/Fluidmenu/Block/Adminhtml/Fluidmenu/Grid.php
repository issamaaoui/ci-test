<?php

class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct()
    {
        parent::__construct();
        $this->setId('fluidmenuGrid');
        $this->setDefaultSort('fluidmenu_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('fluidmenu/fluidmenu')->getCollection();
        
        if (!Mage::app()->isSingleStoreMode()) {
            $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tableName = Mage::getSingleton('core/resource')->getTableName('fd_fluidmenu_store');
            //$collection->getSelect()->joinLeft($tableName, 'main_table.fluidmenu_id = '. $tableName . '.fluidmenu_id', array($tableName . '.store_id as store_id'));
            
            foreach($collection as $link){
                $results = $readConnection->fetchAll("SELECT store_id FROM $tableName WHERE fluidmenu_id = {$link->getId()}");
                $stores = array();
                foreach($results as $store_id) {
                    $stores[] = $store_id['store_id'];
                }
                if( count($stores) > 0 ){
                    $link->setStoreId($stores);
                }
                else{
                    $link->setStoreId(null);
                }
           }   
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

  
	protected function _prepareColumns() {
	    $this->addColumn('fluidmenu_id', array(
	        'header' => Mage::helper('fluidmenu')->__('ID'),
	        'align' => 'right',
	        'width' => '50px',
	        'index' => 'fluidmenu_id',
	    ));
	
	    $this->addColumn('name', array(
	        'header' => Mage::helper('fluidmenu')->__('Name'),
	        'align' => 'left',
	        'index' => 'name',
	    ));
	    
	    $this->addColumn('key_menu', array(
	        'header' => Mage::helper('fluidmenu')->__('Identifiant menu'),
	        'align' => 'left',
	        'index' => 'key_menu',
	    ));
	    
    
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => true,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }
	
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
	            ),
                array(
	                'caption' => Mage::helper('fluidmenu')->__('Voir les liens'),
	                'url' => array('base' => '*/adminhtml_fluidmenu_items/index'),
	                'field' => 'menu'
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
	    $this->setMassactionIdField('fluidmenu_id');
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

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }
	    
    public function lookupStoreIds($objectId)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('fd_fluidmenu_store');
        $select  = $adapter->select()
            ->from($tableName, 'store_id')
            ->where('fluidmenu_id = ?',(int)$objectId);

        return $adapter->fetchCol($select);
    }
  
	  public function getRowUrl($row) {
	    return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	  }

}