<?php

class Fidesio_Fluidmenu_Block_Fluidmenu extends Mage_Core_Block_Template {

  /**
   * Prepare layout and set general template for menus
   * 
   * @return type
   */
  public function _prepareLayout() {
    $this->setTemplate('fluidmenu/menu.phtml');
    return parent::_prepareLayout();
  }

  /**
   * Elle retourne la liste des liens du menu en fonction de sa clé ($menuKey)
   * 
   * @param string $menuKey la clé du menu
   * @param int $limit la limite
   * @return Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Items_Collection liste des liens de menu
   */
  public function getMenuItems($menuKey, $limit=null) {
    return Mage::helper('fluidmenu')->getMenuItems($menuKey, $limit);
  }
  
  /**
   * Elle retourne le sous menu du niveau correspondant
   * 
   * @param string $menuKey la clé du menu
   * @param int $levelItems le niveau des liens
   */
  public function getCurrentSubMenu($menuKey, $levelItems, $limit=null){
  		return Mage::helper('fluidmenu')->getCurrentSubMenu($menuKey, $levelItems, $limit);
  }

  /**
   * Get submenu item block and html
   * 
   * @param type Fidesio_Fluidmenu_Block_Fluidmenu
   */
  public function getSubMenuBlock($menuItem) {
    $subMenuBlock = $this->getLayout()->createBlock('fluidmenu/fluidmenu')->setTemplate($this->_getSubmenuTemplate());
    $subMenuBlock->setParentMenu($menuItem);
    return $subMenuBlock;
  }

  /**
   * Get menu items of menu by parent menu $menuItem
   * 
   * @param type $menuLabel
   * @return Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Items_Collection collection of menu items 
   */
  public function getMenuSubItems($menuItem) {
    return Mage::helper('fluidmenu')->getMenuSubItems($menuItem);
  }

  /**
   * Get submenu template if is seted
   * 
   * @return String Submenu Template
   */
  protected function _getSubmenuTemplate(){
    if($this->getSubmenuTemplate() == null){
      return 'fluidmenu/submenu.phtml';
    }
    return $this->getSubmenuTemplate();
  }

}