<?php

class Fidesio_Fluidmenu_Model_Fluidmenu extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fluidmenu/fluidmenu');
    }
    
    
    /**
     * Elle retourne la liste des menus pour le select du formulaire d'édition d'un lien de menu
     * @return array
     */
    public function getInputSelectMenu()
    {
      $output = array();
      $output[] = array('value' => '', 'label' => '');
      
      $collection = $this->getCollection()
              ->addFieldToSelect('*')
              ->setOrder('name', 'asc');
      
      if(count($collection) > 0)
      {
        foreach($collection as $menu)
        {
          $output[] = array('value' => $menu->getId(), 'label' => $menu->getName());
        }
      }
      
      return $output;
    }
    
    /**
     * Elle retourne la liste des menus pour le filtre de la grille lien de menu dans adminhtml 
     * @return array
     */
    public function getGridInputSelectMenu()
    {
      $output = array();
      $collection = $this->getCollection()
              ->addFieldToSelect('*')
              ->setOrder('name', 'asc');
      
      if(count($collection) > 0)
      {
        foreach($collection as $menu)
        {
          $output[$menu->getId()] = $menu->getName();
        }
      }      
      return $output;
    }
    
	 /**
     * Elle permer de savoir le menu est actif ou pas.
     * @return bool
     */
    public function isActif()
    {
        return $this->getStatus() == Fidesio_Fluidmenu_Model_Status::STATUS_ENABLED;
    }

    /**
     * Elle permet de récuperer un menu avec une clée
     * @param $keyMenu
     * @return mixed
     */
    public function loadByCode($keyMenu){
        return Mage::getModel('fluidmenu/fluidmenu')
            ->getCollection()
            ->addFieldToFilter('key_menu', $keyMenu)
            ->getFirstItem();
    }
}
?>