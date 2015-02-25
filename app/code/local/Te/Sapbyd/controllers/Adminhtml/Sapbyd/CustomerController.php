<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 6 févr. 2015
 *
**/

class Te_Sapbyd_Adminhtml_Sapbyd_CustomerController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title(Mage::helper('te_sapbyd')->__('ByDesign'))
            ->_title(Mage::helper('customer')->__('Manage Customers'));

        $this->loadLayout();
        $this->_setActiveMenu('dvl_sapbyd/customer');
        $this->renderLayout();
    }



    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massSynchronizeAction()
    {
        $customerIds = $this->getRequest()->getPost('customer_ids', array());
        /* @var $orders Mage_Sales_Model_Resource_Order_Collection */
        /*$customers = Mage::getResourceModel('customer/customer_collection');
        $customers->addAttributeToFilter('entity_id', array('in' => $customerIds))
        ->addAttributeToselect("default_billing");
        */
        Mage::dispatchEvent('export_customer_to_sapbyd', array('customers' => $customerIds));



        $this->_getSession()->addSuccess($this->__('Clients sélectionnées: %s', implode(', ',$customerIds)));

        $this->_redirect('*/*/');
    }
}
