<?php
class Fidesio_Preorder_Block_Adminhtml_Code_Grid_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($row->getOrderId());
        $order_link = $row->getOrderId();
        if($order->getId()){
            $link_view_order = Mage::helper("adminhtml")->getUrl('adminhtml/sales_order/view', array('order_id'=>$order->getId()));
            $order_infos = $order->getIncrementId();
            $order_link = '<a href="'.$link_view_order.'" title="'.$order_infos.'" >'.$order_infos.'</a>';
        }
        return $order_link;
    }
}
