<?php
class Fidesio_Preorder_Model_Adminhtml_Observer  extends Mage_Core_Model_Abstract {

    function customer_grid_block_html_before($observer) {
        try {
            $block = $observer->getBlock();
            if (!isset($block)) return;

            switch ($block->getType()) {
                case 'adminhtml/customer_grid':
                    $block->addColumn('code_achat', array(
                        'header'    => Mage::helper('preorder')->__("Codes d'achat"),
                        'index'     => 'code_achat',
                        'default'   => '0',
                        'align'     => 'center',
                        'width'     => '100',
                        'filter'    => false,
                        'sortable'  => false,
                        'renderer'  => 'preorder/adminhtml_code_grid_renderer_countCode'
                    ));
                    $block->addColumnsOrder('code_achat','entity_id');
                    $block->sortColumnsByOrder();
                    break;
            }
        } catch ( Exception $e ) {
            Mage::log( "customer_block_html_before observer failed: " . $e->getMessage() );
        }
    }
}