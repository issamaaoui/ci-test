<?php

class Fidesio_Fluidmenu_Helper_Data extends Mage_Core_Helper_Abstract {

  /**
   * Remove all sub menu items for parent item
   * 
   * @param type $parentId - parent item ID
   */
  public function removeChilds($parentId) {
    $collection = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()
            ->addFieldToFilter('parent_id', $parentId);

    if (count($collection) > 0) {
      foreach ($collection as $val) {
        $this->removeChilds($val->getId());
        $val->delete();
      }
    }
  }

  /**
   * Elle retourne la liste des liens du menu
   * 
   * @param string $menuKey - La clé du menu
   * @param int $limit - La limite
   * @return Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Items_Collection liste de liens 
   */
  public function getMenuItems($key_menu, $limit=null) {
      
	    $menu = Mage::getModel('fluidmenu/fluidmenu')
                    ->getCollection()                    
                    ->addFieldToFilter('key_menu', $key_menu)
                    ->getFirstItem();          
            
            if (!Mage::app()->isSingleStoreMode()) {
                $menu = Mage::getModel('fluidmenu/fluidmenu')
                    ->getCollection()                    
                    ->addFieldToFilter('key_menu', $key_menu)
                    ->addStoreFilter(Mage::app()->getStore()->getStoreId())->getFirstItem();  
            }
            
            
	    if ($menu->getId() and $menu->isActif()) {
	      $collection = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()
	              ->addFieldToSelect('*')
	              ->addFieldToFilter('menu_id', $menu->getId())
	              ->addFieldToFilter('parent_id', 0)
	              ->addFieldToFilter('status', Fidesio_Fluidmenu_Model_Status::STATUS_ENABLED)
	              ->setOrder("position", 'asc');            
	       	
	        if(!is_null($limit) and is_int($limit))
	      		$collection->getSelect()->limit($limit);
	      return $collection;
	    } 
	    return null;
  }
  
  /**
   * Elle retourne la liste des items du sous menu courant
   * 
   * @param string $menuKey la clé du menu
   * @param int $levelItems le niveau des liens 
   * @param int $limit le nombre de liens
   * @return Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Items_Collection liste de liens
   */
  public function getCurrentSubMenu($menuKey, $levelItems, $limit=null){
	  $menu = Mage::getModel('fluidmenu/fluidmenu')->load($menuKey, 'key_menu');
	  $currentUrl = Mage::helper('core/url')->getCurrentUrl();
	  
//	  var_dump($currentUrl);
//	  echo Mage::app()->getFrontController()->getRequest()->getRouteName();
	
	    if ($menu->getId() and $menu->isActif()) {
	    	  // Recherche dans les parents
		      $collection_parent = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()
		              ->addFieldToSelect('*')
		              ->addFieldToFilter('menu_id', $menu->getId())
		              ->addFieldToFilter('level', $levelItems-1)
		              ->addFieldToFilter('status', Fidesio_Fluidmenu_Model_Status::STATUS_ENABLED);
		       foreach ($collection_parent as $item) {
		       		if($currentUrl == $item->getRealUrl()){
		       			return $this->getItemsByParent($item->getId(), $levelItems, $limit);
		       		}
		       }
		       
		       // Recherche dans le niveau correspondant
		    	$collection_level = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()
		              ->addFieldToSelect('*')
		              ->addFieldToFilter('menu_id', $menu->getId())
		              ->addFieldToFilter('level', $levelItems)
		              ->addFieldToFilter('status', Fidesio_Fluidmenu_Model_Status::STATUS_ENABLED);
		       foreach ($collection_level as $item) {
		       		if($currentUrl == $item->getRealUrl()){
		       			return $this->getItemsByParent($item->getParentId(), $levelItems, $limit);
		       		}
		       }
	    } 
	   	return null;
  }
  
  /**
   * Elle retourne la liste des liens pour un parent
   * @param int $parentId
   * @param int $limit
   * @return Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Items_Collection liste de liens
   */
  function getItemsByParent($parentId, $levelItems, $limit=null){
  	      $collection = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()
	              ->addFieldToSelect('*')
	              ->addFieldToFilter('parent_id', $parentId)
	              ->addFieldToFilter('level', $levelItems)
	              ->addFieldToFilter('status', Fidesio_Fluidmenu_Model_Status::STATUS_ENABLED)
	              ->setOrder("position", 'asc');
	              
	      if(!is_null($limit))
	      		$collection->getSelect()->limit($limit);
	      return $collection; 
  }
  

  /**
   * Get menu items of menu by parent menu item
   * 
   * @param type $menuItem
   * @return Fidesio_Fluidmenu_Model_Mysql4_Fluidmenu_Items_Collection collection of menu items 
   */
  public function getMenuSubItems($menuItem) {
    $collection = Mage::getModel('fluidmenu/fluidmenu_items')->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('parent_id', $menuItem->getId())
            ->addFieldToFilter('status', Fidesio_Fluidmenu_Model_Status::STATUS_ENABLED)
            ->setOrder("position", 'asc');
    return $collection;
  }

}