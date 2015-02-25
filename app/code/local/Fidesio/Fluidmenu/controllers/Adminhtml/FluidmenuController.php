<?php

class Fidesio_Fluidmenu_Adminhtml_FluidmenuController extends Mage_Adminhtml_Controller_Action {

    /**
     * Controller setup action
     * 
     * @return Fidesio_Fluidmenu_Adminhtml_FluidmenuController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fluidmenu/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Gestion des menus'), Mage::helper('adminhtml')->__('Gestion des menus'));

        return $this;
    }

    /**
     * Fluidmenu index action
     * 
     */
    public function indexAction() {
        $this->_initAction()->renderLayout();
    }

    /**
     * Fluidmenu edit action
     * 
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fluidmenu/fluidmenu')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('fluidmenu_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('fluidmenu/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Gestion des menus'), Mage::helper('adminhtml')->__('Gestion des menus'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Menu News'), Mage::helper('adminhtml')->__('Menu News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('fluidmenu/adminhtml_fluidmenu_edit'))
                    ->_addLeft($this->getLayout()->createBlock('fluidmenu/adminhtml_fluidmenu_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fluidmenu')->__('Menu does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Action forwarded to edit action
     * 
     */
    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * Save action
     * 
     * @return type
     */
    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('fluidmenu/fluidmenu');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fluidmenu')->__('Le menu a été sauvegardé avec succès.'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fluidmenu')->__('Impossible de trouver un enregistrement à sauvegarder'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('fluidmenu/fluidmenu')->load($this->getRequest()->getParam('id'));
                $menuItemModel = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()->addFieldToFilter('menu_id', $model->getId());
                foreach ($menuItemModel as $val) {
                    $val->delete();
                }
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Le menu a été supprimé avec succès'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {

        $contactformIds = $this->getRequest()->getParam('fluidmenu');
        if (!is_array($contactformIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Veuillez sélectionner le menu(s).'));
        } else {
            try {
                foreach ($contactformIds as $contactformId) {
                    $model = Mage::getModel('fluidmenu/fluidmenu')->load($contactformId);
                    $menuItemModel = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()->addFieldToFilter('menu_id', $model->getId());
                    foreach ($menuItemModel as $val) {
                        $val->delete();
                    }
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                '%d enregistrement(s) ont été supprimés avec succès', count($contactformIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $idsMenu = $this->getRequest()->getParam('fluidmenu');
        if (!is_array($idsMenu)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Veuillez sélectionner le menu(s).'));
        } else {
            try {
                foreach ($idsMenu as $idMenu) {
                    $bannerslider = Mage::getSingleton('fluidmenu/fluidmenu')
                            ->load($idMenu)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('%d enregistrement(s) ont été mis à jour avec succès.', count($idsMenu))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}