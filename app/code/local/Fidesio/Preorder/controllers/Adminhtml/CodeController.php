<?php

class Fidesio_Preorder_Adminhtml_CodeController extends Mage_Adminhtml_Controller_Action {

    /**
     * Controller setup action
     * 
     * @return Fidesio_Preorder_Adminhtml_CodeController
     */
    protected function _initAction() {
        $this->loadLayout()
             ->_setActiveMenu('customer/preorder_code');

        return $this;
    }

    /**
     * Code index action
     * 
     */
    public function indexAction() {
        $this->_initAction()->renderLayout();
    }

    /**
     * Code edit action
     * 
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('preorder/code')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('code_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('customer/preorder_code');
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('preorder/adminhtml_code_edit'))
                 ->_addLeft($this->getLayout()->createBlock('preorder/adminhtml_code_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('preorder')->__('Le code n\'existe pas.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Action forwarded to edit action
     */
    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * Save action
     * @return type
     */
    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('preorder/code');

            // Ajout des données dans le model
            $model->setData($data)->setId($this->getRequest()->getParam('id'));

            try {
                // Date create and update
                if($this->getRequest()->getParam('id')){
                    $model->setUpdatedTime(Mage::getModel('core/date')->gmtDate());
                }
                else{
                    // nouveau lot
                    $model->setCreatedTime(Mage::getModel('core/date')->gmtDate());
                    $model->setUpdatedTime(Mage::getModel('core/date')->gmtDate());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('preorder')->__('Le code a été sauvegardé avec succès.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productmodel')->__('Impossible de trouver un enregistrement à sauvegarder'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('preorder/code')->load($this->getRequest()->getParam('id'));
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Le code a été supprimé avec succès.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $massIDs = $this->getRequest()->getParam('code');
        if (!is_array($massIDs)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Veuillez sélectionner un code(s).'));
        } else {
            try {
                foreach ($massIDs as $id) {
                    $model = Mage::getModel('preorder/code')->load($id);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                '%d enregistrement(s) ont été supprimés avec succès', count($massIDs)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $massIDs = $this->getRequest()->getParam('code');
        if (!is_array($massIDs)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Veuillez sélectionner un code(s).'));
        } else {
            try {
                foreach ($massIDs as $id) {
                    $model = Mage::getSingleton('preorder/code')
                            ->load($id)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('%d enregistrement(s) ont été mis à jour avec succès.', count($massIDs))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}