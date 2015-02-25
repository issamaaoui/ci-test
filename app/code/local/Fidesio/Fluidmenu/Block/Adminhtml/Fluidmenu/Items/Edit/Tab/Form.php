<?php
class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu_Items_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

  protected function _prepareForm() {
    $form = new Varien_Data_Form();
    $this->setForm($form);
    
    $dataMenu = Mage::registry('fluidmenu_data')->getData();
    $parent_id = isset($dataMenu['parent_id']) ? $dataMenu['parent_id'] : 0;
    
    // Fieldset 'Informations générales'
    $fieldset = $form->addFieldset('fluidmenu_items_form', array('legend' => Mage::helper('fluidmenu')->__('Informations générales du lien')));

    $fieldset->addField('name', 'text', array(
        'label' => Mage::helper('fluidmenu')->__('Name'),
        'required' => true,
        'name' => 'name',
    ));
    
    // Menu
    $eventMenu = $fieldset->addField('menu_id', 'select', array(
        'label' => Mage::helper('fluidmenu')->__('Menu'),
        'name' => 'menu_id',
        'required' => true,
        'values' => Mage::getModel('fluidmenu/fluidmenu')->getInputSelectMenu(),
    	'after_element_html' => '<div class="hint"><p class="note">'.$this->__('Sélectionnez le menu dans lequel le lien va apparaître.').'</p></div>',
    	'onchange' => 'CheckSelectedParentItem(this, 0)'
    ));    
    $eventMenu->setAfterElementHtml("<script type=\"text/javascript\">
	    function CheckSelectedParentItem(selectMenu, parentSelectedId){
	    	   
	        var reloadurlMenu = '". $this->getUrl('fluidmenu/adminhtml_fluidmenu_items/CheckSelectedParentItem')."menu_id/'+selectMenu.value+'/parentSelectedId/'+parentSelectedId;
	        new Ajax.Request(reloadurlMenu, {
	            method: 'get',
	            onLoading: function (transport) {
	                $('parent_id').update('Searching...');
	            },
	            onComplete: function(transport) {
	                    $('parent_id').update(transport.responseText);
	                    updateLevelItem( $('parent_id').getValue() );		// Mise à jour du niveau car le parent à changé
	            }
	        });
	    }
	    // Précharger les liens du menu courant
	    var inputMenu = document.getElementById('menu_id');
	    CheckSelectedParentItem(inputMenu, '".$parent_id."');
	</script>");
    
    
    // Parent lien
    $eventParent = $fieldset->addField('parent_id', 'select', array(
        'label' => Mage::helper('fluidmenu')->__('Parent'),
        'required' => false,
        'values' => Mage::getModel('fluidmenu/fluidmenu_items')->getInputSelectParentItem(),
        'name' => 'parent_id',
    	'onchange' => 'updateLevelItem(this.value)'
    ));
    $eventParent->setAfterElementHtml("<script type=\"text/javascript\">
	    function updateLevelItem(parentId){	 
	        var reloadurlParent = '". $this->getUrl('fluidmenu/adminhtml_fluidmenu_items/updateLevelItem')."parent_id/'+parentId;
	        new Ajax.Request(reloadurlParent, {
	            method: 'get',
	            onComplete: function(transport) {
	                    $('level').setValue(transport.responseText);
	            }
	        });
	    }
	    // Pécharger le level en fonction du parent
	   updateLevelItem('".$parent_id."');
	</script>");
    
    
    
    // Type de lien    
    $fieldset->addField('cms_type', 'select', array(
        'label' => Mage::helper('fluidmenu')->__('Type de lien'),
        'name' => 'cms_type',
    	'required' => true,
        'values' => Mage::getModel('fluidmenu/fluidmenu_items')->getInputSelectCmsType(),
    	'after_element_html' => '<div class="hint"><p class="note">'.$this->__('Sélectionnez le type de lien et mettez l\'identifiant dans le champs (Identifiant / URL).').'</p></div>',
    ));
    
    // Identifiant / URL
    $fieldset->addField('url', 'text', array(
        'label' => Mage::helper('fluidmenu')->__('Identifiant / URL '),
        'required' => false,
        'name' => 'url',
    ));

        
    // Niveau
    $fieldset->addField('level', 'hidden', array(
        'label' => Mage::helper('fluidmenu')->__('Niveau'),
        'required' => false,
        'name' => 'level',
   	));
    
    
    // Position
    $fieldset->addField('position', 'text', array(
        'label' => Mage::helper('fluidmenu')->__('Position'),
        'required' => false,
        'name' => 'position',
        'class' => 'validate-digits',
   		'after_element_html' => '<div class="hint"><p class="note">'.$this->__('Permet de définir l\'ordre d\'affichage du lien.').'</p></div>',
    ));
       
    // Statut
    $fieldset->addField('status', 'select', array(
        'label' => Mage::helper('fluidmenu')->__('Status'),
        'name' => 'status',
        'values' => array(
            array(
                'value' => 1,
                'label' => Mage::helper('fluidmenu')->__('Enabled'),
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('fluidmenu')->__('Disabled'),
            ),
        ),
    ));
    
    
    // Fieldset 'Options'
    $fieldset = $form->addFieldset('fluidmenu_items_options', array('legend' => Mage::helper('fluidmenu')->__('Options du lien')));

    // Ancre
    $fieldset->addField('ancre', 'text', array(
        'label' => Mage::helper('fluidmenu')->__('Ancre'),
        'required' => false,
        'name' => 'ancre',
    ));
    
    // Title
    $fieldset->addField('title_link', 'text', array(
        'label' => Mage::helper('fluidmenu')->__('Propriété (title)'),
        'required' => false,
        'name' => 'title_link',
    ));
    
    // Id
    $fieldset->addField('id_link', 'text', array(
        'label' => Mage::helper('fluidmenu')->__('Propriété (id)'),
        'required' => false,
        'name' => 'id_link',
    ));
    
    // Class
    $fieldset->addField('class_link', 'text', array(
        'label' => Mage::helper('fluidmenu')->__('Propriété (class)'),
        'required' => false,
        'name' => 'class_link',
    ));

    // Target
    $fieldset->addField('target_link', 'select', array(
        'label' => Mage::helper('fluidmenu')->__('Propriété (target)'),
        'name' => 'target_link',
        'values' =>  Mage::getModel('fluidmenu/fluidmenu_items')->getInputSelectTarget()
    ));
    
    $form->setValues(Mage::registry('fluidmenu_data')->getData());
 
	return parent::_prepareForm();
  }

}