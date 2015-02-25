<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 6 févr. 2015
 *
**/

class Te_Sapbyd_Adminhtml_Sapbyd_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title(Mage::helper('te_sapbyd')->__('ByDesign'))
            ->_title($this->__('Orders'));
        $this->loadLayout();
        $this->_setActiveMenu('dvl_sapbyd/order');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massSynchronizeAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        /* @var $orders Mage_Sales_Model_Resource_Order_Collection */
        $orders = Mage::getResourceModel('sales/order_collection');
        $orders->addAttributeToFilter('entity_id', array('in' => $orderIds));

        Mage::dispatchEvent('export_order_to_sapbyd', array('orders' => $orders));



        $this->_getSession()->addSuccess($this->__('Commandes sélectionnées: %s', implode(', ',$orders->getColumnValues('increment_id'))));

        $this->_redirect('*/*/');
    }
}
