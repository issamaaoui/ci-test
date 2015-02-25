<?php
class PWS_ProductRegistration_Adminhtml_ProductregistrationController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
       	$this->loadLayout();

        $this->_setActiveMenu('catalog/pws_productregistration');
        $this->_addBreadcrumb(
            Mage::helper('pws_productregistration')->__('Registered Products'),
            Mage::helper('pws_productregistration')->__('Registered Products')
        );
        $this->_addContent($this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_list'));

        $this->renderLayout();
    }


    public function serialNumbersAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('catalog/pws_productregistration');
        $this->_addBreadcrumb(
            Mage::helper('pws_productregistration')->__('Unregistered Products'),
            Mage::helper('pws_productregistration')->__('Unregistered Products')
        );
        $this->_addContent($this->getLayout()->createBlock('pws_productregistration/adminhtml_productSerialNumbers_list'));

        $this->renderLayout();
    }

    /**
     * Export registered products grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'registered_products.csv';
        $content  = $this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export registered products grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName = 'registered_products.xml';
        $content  = $this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_grid')->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Export registered products grid to CSV format
     */
    public function serialNumbersExportCsvAction()
    {
        $fileName = 'product_serial_numbers_unused.csv';
        $content  = $this->getLayout()->createBlock('pws_productregistration/adminhtml_productSerialNumbers_grid')->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export registered products grid to XML format
     */
    public function serialNumbersExportXmlAction()
    {
        $fileName = 'product_serial_numbers_unused.xml';
        $content  = $this->getLayout()->createBlock('pws_productregistration/adminhtml_productSerialNumbers_grid')->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

	public function massDeleteAction()
    {
        $registrationIds = $this->getRequest()->getParam('registration');
        if (!is_array($registrationIds)) {
            $this->_getSession()->addError($this->__('Please select at least one entry'));
        }
        else {
            try {
                foreach ($registrationIds as $registrationId) {
                    $registration =Mage::getModel('pws_productregistration/productregistration')->load($registrationId);
                    $registration->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($registrationIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


    public function editAction()
    {
        $this->loadLayout();

        $productRegistrationId  = (int) $this->getRequest()->getParam('id');
        $productRegistration    = Mage::getModel('pws_productregistration/productregistration')->setStoreId($this->getRequest()->getParam('store', 0));
        $productRegistration->load($productRegistrationId);

        if ($productRegistrationData = Mage::getSingleton('adminhtml/session')->getProductRegistrationData()) {
            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

            $date = Mage::app()->getLocale()->date(
                $productRegistrationData['product_registration']['date_of_purchase'],
                Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                null,
                false
            );
            $productRegistrationData['product_registration']['date_of_purchase'] = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $productRegistration->addData($productRegistrationData['product_registration']);
            Mage::getSingleton('adminhtml/session')->setProductRegistrationData(false);
        }

        Mage::register('product_registration', $productRegistration);
        Mage::register('current_product_registration', $productRegistration);

        $this->_setActiveMenu('catalog/pws_productregistration');
        $this->_addBreadcrumb(Mage::helper('pws_productregistration')->__('Manage Product Registration'), Mage::helper('pws_productregistration')->__('Manage Product Registration'));

        $this->_addContent($this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_edit'))
            ->_addLeft($this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_edit_tabs'));
        $this->renderLayout();
    }

    public function addAction()
    {
        $this->loadLayout();

        $customerId = (int) $this->getRequest()->getParam('customer_id');
        $customer   = Mage::getModel('customer/customer')->load($customerId);

        $productRegistration   = Mage::getModel('pws_productregistration/productregistration')
            ->setStoreId($this->getRequest()->getParam('store', 0));
        $productRegistration->setCustomerId($customerId);

        Mage::register('product_registration', $productRegistration);
        Mage::register('current_product_registration', $productRegistration);

        $this->_setActiveMenu('catalog/pws_productregistration');
        $this->_addBreadcrumb(
            Mage::helper('pws_productregistration')->__('Manage Product Registration'),
            Mage::helper('pws_productregistration')->__('Manage Product Registration')
        );

        $this->_addContent($this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_edit'))
            ->_addLeft($this->getLayout()->createBlock('pws_productregistration/adminhtml_productRegistration_edit_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$this->getRequest()->getPost()) return;

        try {
            $productRegistrationModel = Mage::getModel('pws_productregistration/productregistration');

            if ($this->getRequest()->getParam('id')) {
                $productRegistrationModel->load($this->getRequest()->getParam('id'));
            }

            $product_registration_data = $this->getRequest()->getParam('product_registration');

            $errors = Mage::getModel('pws_productregistration/productregistration')
                ->validate(array($product_registration_data), $productRegistrationModel->getId());

            if ($errors) {
                foreach ($errors as $error) {
                    Mage::getSingleton('adminhtml/session')->addError($error);
                }
                Mage::getSingleton('adminhtml/session')->setProductRegistrationData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }

            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $date   = Mage::app()->getLocale()->date(
                $product_registration_data['date_of_purchase'],
                Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                null,
                false
            );

            $product_registration_data['date_of_purchase'] = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            if (Mage::helper('pws_productregistration')->useProductSkuInput()) {
                $product  = Mage::getModel('catalog/product')->loadByAttribute('sku', trim($product_registration_data['product_sku']));
                $product_registration_data['product_id']   = $product->getId();
                $product_registration_data['product_name'] = $product->getName();
            }

            if (Mage::helper('pws_productregistration')->useProductNameInput()) {
                $product_registration_data['product_id'] = 0;
            } else {
                $product = Mage::getModel('catalog/product')->load($product_registration_data['product_id']);
                $product_registration_data['product_name'] = $product->getName();
            }

            $originalIsValidValue                  = $productRegistrationModel->getIsValid();
            $product_registration_data['is_valid'] = isset($product_registration_data['is_valid'])? $product_registration_data['is_valid']: 'yes';

            $productRegistrationModel
                ->setPurchasedFrom($product_registration_data['purchased_from'])
                ->setProductId($product_registration_data['product_id'])
                ->setProductName($product_registration_data['product_name'])
                ->setCustomerId($product_registration_data['customer_id'])
                ->setDateOfPurchase($product_registration_data['date_of_purchase'])
                ->setSerialNumber($product_registration_data['serial_number'])
                ->setNotes($product_registration_data['notes'])
                ->setIsValid($product_registration_data['is_valid'])
                ->save();

            // notify customer
            if (Mage::helper('pws_productregistration')->notifyCustomerInvalidRegistration()) {
                if ($productRegistrationModel->getIsValid() == 'no'
                    && $productRegistrationModel->getIsValid() != $originalIsValidValue) {
                    Mage::helper('pws_productregistration')->sendCustomerNotificationEmail($productRegistrationModel);
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pws_productregistration')->__('Customer was notified - product registration invalidated'));
                }
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pws_productregistration')->__('Product Registration have been successfully saved'));
            Mage::getSingleton('adminhtml/session')->setProductRegistrationData(false);

            $this->_redirect('*/*/');
            return;

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setProductRegistrationData($this->getRequest()->getPost());
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
        $this->_redirect('*/*/');
    }


    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('pws_productregistration/productregistration');
                $model->setId($this->getRequest()->getParam('id'))->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('pws_productregistration')->__('Product Registration have been successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

}
