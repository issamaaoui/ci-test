<?php
class Fidesio_Preorder_Block_Adminhtml_Code_Grid_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $customer = Mage::getModel('customer/customer')->load($row->getCustomerId());
        $customer_link = $row->getCustomerId();
        if($customer->getId()){
            $link_edit_customer = Mage::helper("adminhtml")->getUrl('adminhtml/customer/edit', array('id'=>$customer->getId()));
            $customer_name = $customer->getId().' - '.$customer->getName() .' ('.$customer->getEmail().')';
            $customer_link = '<a href="'.$link_edit_customer.'" title="'.$customer_name.'" >'.$customer_name.'</a>';
        }

        return $customer_link;
    }
}
