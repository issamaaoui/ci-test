<?php

class Fidesio_Fluidmenu_Adminhtml_Fluidmenu_ItemsController extends Mage_Adminhtml_Controller_Action {

  protected function _initAction() {
    $this->loadLayout()
            ->_setActiveMenu('fluidmenu/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Gestion des liens de menu'), Mage::helper('adminhtml')->__('Gestion des liens de menu'));

    return $this;
  }

  public function indexAction() {
    $this->_initAction()
            ->renderLayout();
  }

  public function editAction() {
    $id = $this->getRequest()->getParam('id');
    $model = Mage::getModel('fluidmenu/fluidmenu_items')->load($id);

    if ($model->getId() || $id == 0) {
      $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
      if (!empty($data)) {
        $model->setData($data);
      }

      Mage::register('fluidmenu_data', $model);

      $this->loadLayout();
      $this->_setActiveMenu('fluidmenu/items');

      $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Gestion des menus'), Mage::helper('adminhtml')->__('Gestion des liens de menu'));
      $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Menu News'), Mage::helper('adminhtml')->__('Gestion des liens de menu'));

      $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

      $this->_addContent($this->getLayout()->createBlock('fluidmenu/adminhtml_fluidmenu_items_edit'))
              ->_addLeft($this->getLayout()->createBlock('fluidmenu/adminhtml_fluidmenu_items_edit_tabs'));

      $this->renderLayout();
    } else {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fluidmenu')->__('Le lien de menu n\'existe pas.'));
      $this->_redirect('*/*/');
    }
  }

  public function newAction() {
    $this->_forward('edit');
  }

  public function saveAction() {
    if ($data = $this->getRequest()->getPost()) {

      $model = Mage::getModel('fluidmenu/fluidmenu_items');

      $model->setData($data)
              ->setId($this->getRequest()->getParam('id'));

      try {

        $model->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fluidmenu')->__('Le lien de menu a été sauvegardé avec succès.'));
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
    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fluidmenu')->__('Impossible de trouver le lien de menu pour sauvegarder.'));
    $this->_redirect('*/*/');
  }

  public function deleteAction() {
    if ($this->getRequest()->getParam('id') > 0) {
      try {
        $model = Mage::getModel('fluidmenu/fluidmenu_items')->load($this->getRequest()->getParam('id'));
        Mage::helper('fluidmenu')->removeChilds($model->getId());
        $model->delete();

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Le lien de menu a été supprimé avec succès.'));
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
          $model = Mage::getModel('fluidmenu/fluidmenu_items')->load($contactformId);
          Mage::helper('fluidmenu')->removeChilds($model->getId());
          $model->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($contactformIds)
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
          $bannerslider = Mage::getSingleton('fluidmenu/fluidmenu_items')
                  ->load($idMenu)
                  ->setStatus($this->getRequest()->getParam('status'))
                  ->setIsMassupdate(true)
                  ->save();
        }
        $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were successfully updated', count($idsMenu))
        );
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }

  
  /**
   * Ajax, Elle permet de mettre à jour la liste des liens en fonction des memus
   * 
   * @see array la liste des options pour le menu select
   */
  public function CheckSelectedParentItemAction(){
   		$menuId = $this->getRequest()->getParam('menu_id');
        $model = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()
                 ->addFieldToFilter('menu_id', $menuId);

        $id_selected = $this->getRequest()->getParam('parentSelectedId'); 
        $ouput = '<option value="" > </option>';
      	foreach($model as $items){
      		  $selected = ($id_selected == $items->getId()) ? 'selected="selected" ' : '' ;
              $ouput .= '<option value="'.$items->getId().'" '.$selected.' >'. $items->getName().' </option>';
       	}
       echo $ouput;
  }
  
  
  /**
   * Ajax, Elle permet de mettre à jour le niveau 'level' du lien en fonction du parent
   * 
   * @see int affiche le level+1 du parent
   */
  public function updateLevelItemAction(){
   		$parentId = $this->getRequest()->getParam('parent_id');
        $parentItem = Mage::getModel('fluidmenu/fluidmenu_items')->load($parentId);

        if(!is_null($parentItem))	echo $parentItem->getLevel()+1;
        else echo '1';
  }
  
}