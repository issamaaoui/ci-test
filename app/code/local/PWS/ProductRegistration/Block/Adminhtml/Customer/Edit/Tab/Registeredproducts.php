<?php
class PWS_ProductRegistration_Block_Adminhtml_Customer_Edit_Tab_Registeredproducts extends Mage_Adminhtml_Block_Widget_Container
{
    protected $_blockGroup = 'adminhtml';


    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'productregistration';
        $this->_headerText = Mage::helper('pws_productregistration')->__('Registered Products');

        $this->setTemplate('widget/grid/container.phtml');
    }

    public function getCustomer()
    {
        $customer = Mage::registry('current_customer');
        return $customer;
    }

    protected function _prepareLayout()
    {
        $blockGrid = $this->getLayout()
            ->createBlock(
                'pws_productregistration/adminhtml_customer_edit_tab_grid',
                $this->_controller . '.grid'
            )
            ->setData('customer', $this->getCustomer())
            ->setSaveParametersInSession(true);
        $this->setChild('grid', $blockGrid);
        return parent::_prepareLayout();
    }

    public function getCreateUrl()
    {
        return;
    }

    public function getExportButtonHtml()
    {
        return;
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    protected function getAddButtonLabel()
    {
        return;
    }

    protected function getBackButtonLabel()
    {
        return;
    }

    protected function _addBackButton()
    {
        return;
    }

    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }
}
