<?php

class Fidesio_Fluidmenu_Model_Fluidmenu_Items extends Mage_Core_Model_Abstract
{
	public $is_active = false;
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('fluidmenu/fluidmenu_items');
    }
    
    public function getInputSelectParentItem()
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
    
    public function getInputSelectPages()
    {
      $output = array();
      $output[] = array('value' => '', 'label' => '');
      
      $collection = Mage::getModel('cms/page')->getCollection()
              ->addFieldToSelect('*')
              ->setOrder('title', 'asc');
      
      if(count($collection) > 0)
      {
        foreach($collection as $page)
        {
          $output[] = array('value' => $page->getId(), 'label' => $page->getTitle());
        }
      }
      
      return $output;
    }
    
    
    /**
     * Elle retourne la liste des Type de lien
     * 
     * @return array liste type lien
     */
    public function getInputSelectCmsType(){
      	$output = array(
            array('value' => '', 'label' => '',
            ),
            array(
                'value' => '0',
                'label' => Mage::helper('fluidmenu')->__('Page d\'accueil'),
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('fluidmenu')->__('Page CMS'),
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('fluidmenu')->__('Catégorie'),
            ),
            array(
                'value' => 3,
                'label' => Mage::helper('fluidmenu')->__('Produit'),
            ),
            array(
                'value' => 4,
                'label' => Mage::helper('fluidmenu')->__('Lien interne'),
            ),
            array(
                'value' => 5,
                'label' => Mage::helper('fluidmenu')->__('Lien externe'),
            ),
        );        
      	return $output;    	
    }
    
    /**
     * Elle retourne la liste des valeurs de la propriété target=''
     * 
     * @return array valeurs target
     */
    public function getInputSelectTarget(){
    	 $output = array(
            array('value' => '', 'label' => '',
            ),
            array(
                'value' => '_blank',
                'label' => Mage::helper('fluidmenu')->__('_blank'),
            ),
            array(
                'value' => '_parent',
                'label' => Mage::helper('fluidmenu')->__('_parent'),
            ),
            array(
                'value' => '_self',
                'label' => Mage::helper('fluidmenu')->__('_self'),
            ),
            array(
                'value' => '_top',
                'label' => Mage::helper('fluidmenu')->__('_top'),
            )
        );        
      	return $output;  
    }
    
    
    /**
     * Elle prepapre le select filtre dans le gestionnaire des liens de menu
     * 
     * @return array 
     */
    public function getGridInputSelectCmsType()
    {
      $output = array();            
      $collectionCmsType = $this->getInputSelectCmsType();      
      if(count($collectionCmsType) > 0)
      {
        foreach($collectionCmsType as $type)
        {
        	if($type['value']!='')
          		$output[$type['value']] = $type['label'];
        }
      }      
      return $output;
    }
    
    public function getGridInputSelectParentItem()
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
     * Elle génere le lien en fonction du de son type (Page, Catégorie, Produit, Autre)
     * @return string
     */
    public function getRealUrl()
    {
      	$typeCMS 	= $this->getCmsType();
      	$idCMS		= $this->getUrl();
      	$url = '';
      	switch ($typeCMS) {
      		case 0: //Page accueil
      			$url = Mage::getUrl();
      			break;
      		
      		case 1: //Page
      			$page = Mage::getModel('cms/page')->load($idCMS);
      			$url = (!is_null($page)) ? Mage::getUrl().$page->getIdentifier() : '';
      			break;    
      			  			
      		case 2: //Catégorie
      			$category = Mage::getModel('catalog/category')->load($idCMS); 
				$url = (!is_null($category)) ? $category->getUrl() : '';
      			break;   
      			   		
      		case 3: //Produit     			
      			$product = Mage::getModel('catalog/product')->load($idCMS);
      			$url = (!is_null($product)) ? $product->getProductUrl() : '';
      			break;
      			
      		case 4: //Lien interne
      			$url = Mage::getUrl().$this->getUrl();
      			break;
      			
      		case 5: //Lien externe
      			$url = $this->getUrl();
      			break;
      	}
      	   	
      	// Ajout de l'ancre
      	$url .= ($this->getAncre()!='') ? '#'.$this->getAncre() : '';     	
      	
      	return $url;
    }
    
    
    /**
     * Elle retourne l'attribut 'id' du lien
     * @return string
     */
    public function getIdHtml(){
	      if($this->getIdLink() != '')
	      {
	        return ' id="' . $this->getIdLink() . '"';
	      }
	      return '';
    }
    
    /**
     * Elle retourne la liste des class du lien.
     * Elle rajoute aussi la class 'active' si telle est le cas.
     * @return string
     */
    public function getClassHtml(){
    	  $listClass  = ($this->getClassLink() != '') ? $this->getClassLink() : '';
    	  $listClass .= ($this->isActive()) ? ' active ' : '';
    	  
	      if($listClass != '')
	        	return ' class="'. $listClass .'"';

	      return '';
    }
    
    /**
     * Elle retourne l'attribut 'title' du lien
     * @return string
     */
    public function getTitleHtml(){
	      if($this->getTitleLink() != '')
	        return ' title="'.$this->getTitleLink().'"';
	      else
	      	return ' title="' . $this->getName() . '"';
    }
    
    /**
     * Elle retourne l'attribut 'target' du lien
     * @return string
     */
    public function getTargetHtml()
    {
	      if($this->getTargetLink() != '')
	      {
	        return ' target="'.$this->getTargetLink().'"';
	      }
	      return '';
    }
    
    
    /**
     * Elle retourne une contenation de l'ensemble des attributs du lien
     * @return string
     */
    public function getProprerty(){
    	return  $this->getIdHtml().' '.$this->getClassHtml().' '.$this->getTargetHtml().' '.$this->getTitleHtml();
    }
    
    /**
     * Elle permet de savoir si le lien est actif
     * @return bool
     */
    public function isActive(){
        // Lien actif
        $url = $this->getRealUrl();
        if($url!='' and strstr(Mage::helper('core/url')->getCurrentUrl(), $url) and Mage::getUrl()!=$url)
          $this->is_active = true;
    	return $this->is_active;
    }
}
?>