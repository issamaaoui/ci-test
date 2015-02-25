<?php

class Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('fluidmenu/fluidmenu');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    public function addStoreFilter($store) {
        if (!Mage::app()->isSingleStoreMode()) {
            $this->getSelect()->join(
                            array('store_table' => $this->getTable('fluidmenu_store')), 'main_table.fluidmenu_id = store_table.fluidmenu_id', array()
                    )
                    ->where('store_table.store_id in (?)', array(0, $store));
            $this->getSelect()->distinct();
        }
        return $this;
    }
    
    /**
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('fluidmenu/fd_fluidmenu_store')),
                'main_table.fluidmenu_id = store_table.fluidmenu_id',
                array()
            )->group('main_table.fluidmenu_id');

            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }
        return parent::_renderFiltersBefore();
    }

}