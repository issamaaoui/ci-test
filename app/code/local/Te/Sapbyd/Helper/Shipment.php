<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 12 févr. 2015
 *
**/

class Te_Sapbyd_Helper_Shipment extends Mage_Core_Helper_Abstract
{
    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $trackingNumber
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        if (!$order->canShip()) {
            return;
        }
        $savedQtys = $this->_getItemQtys($order);
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
        $shipment->register();
        $shipment->addComment('Created shipment from Sap ByDesign');
        $order->setCustomerNoteNotify(false);
        $order->setIsInProcess(true);
        return $shipment;
    }

    public function addTracking(Mage_Sales_Model_Order_Shipment $shipment, $trackingNumber)
    {
        /* @var $track Mage_Sales_Model_Order_Shipment_Track */
        $track = Mage::getModel('sales/order_shipment_track')->setData(array(
            'carrier_code'  => 'custom',
            'title'         => 'Devialet',
            'number'        => $trackingNumber,
        ));

        return $shipment->addTrack($track);
    }

    public function save(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    private function _getItemQtys($order)
    {
        $savedQtys = array();
        /* @var $orderItem Mage_Sales_Model_Order_Item */
        foreach ($order->getAllItems() as $orderItem) {
            $savedQtys[$orderItem->getId()] = $orderItem->getQtyToShip();
        }
        return $savedQtys;
    }
}