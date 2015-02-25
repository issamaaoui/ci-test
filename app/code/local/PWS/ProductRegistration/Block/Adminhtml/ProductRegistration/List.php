<?php
class PWS_ProductRegistration_Block_Adminhtml_ProductRegistration_List extends Mage_Adminhtml_Block_Widget_Container
{
   
    protected $_blockGroup = 'adminhtml';
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_controller = 'productregistration';
        $this->_headerText = Mage::helper('pws_productregistration')->__('Registered Products');
               
        $this->setTemplate('widget/grid/container.phtml');

       
    }
    
    protected function _prepareLayout()
    {        
        $this->setChild( 'grid',
            $this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_grid',
            $this->_controller . '.grid')->setSaveParametersInSession(true) );
        return parent::_prepareLayout();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    protected function getAddButtonLabel()
    {
        return $this->_addButtonLabel;
    }

    protected function getBackButtonLabel()
    {
        return $this->_backButtonLabel;
    }

    protected function _addBackButton()
    {
        $this->_addButton('back', array(
            'label'     => $this->getBackButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'     => 'back',
        ));
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
