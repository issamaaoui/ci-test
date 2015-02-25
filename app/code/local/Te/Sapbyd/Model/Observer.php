<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 11 févr. 2015
 *
**/
class Te_Sapbyd_Model_Observer
{
    public function importTrackingNumbers()
    {
        if (!Mage::getStoreConfig('te_sapbyd/cron/tracking')) {
            return;
        }
        /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('sapbyd_order_id', array('notnull' => 1));
        //filter to orders without shipping
        $collection->getSelect()->joinLeft(
            array('shipment' => $collection->getTable('sales/shipment')),
            'main_table.entity_id = shipment.order_id',
            array('shipment_id' => 'entity_id')
        )->where('shipment.entity_id IS NULL');

        /* @var $order Mage_Sales_Model_Order*/
        /* @var $service Te_Sapbyd_Model_Service */
        /* @var $helper Te_Sapbyd_Helper_Shipment */
        $helper = Mage::helper('te_sapbyd/shipment');
        $service = Mage::getModel('te_sapbyd/service');
        foreach ($collection as $order) {
            if ($uuid = $service->getUUID($order->getSapbydOrderId())) {
                if ($trackingId = $service->getTrackingId($uuid)) {
                    $shipment = $helper->create($order);
                    $helper->addTracking($shipment, $trackingId);
                    $helper->save($shipment);
                }
            }
        }
    }

    public function importInvoices()
    {
        if (!Mage::getStoreConfig('te_sapbyd/cron/invoice')) {
            return;
        }

        /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('sapbyd_order_id', array('notnull' => 1));

        /* @TODO filter orders */

        /* @var $service Te_Sapbyd_Model_Service */
        /* @var $order Mage_Sales_Model_Order*/
        $service = Mage::getModel('te_sapbyd/service');
        foreach ($collection as $order) {
            $invoice = $service->getInvoice($order->getIncrementId());
            /* @TODO generate pdf */
        }
    }
}