<?php
Class Te_SapbydExport_Block_List_Order extends Mage_Core_Block_Template
{
    public function getOrderCollection()
    {
       $orders = mage::getModel("sales/order")->getCollection()
        ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING)
        ->addFieldToFilter('sapbyd_order_id', array("null"=>'123'));

        return $orders;
    }


    public function getLink($order)
    {
        return "#";
    }
}