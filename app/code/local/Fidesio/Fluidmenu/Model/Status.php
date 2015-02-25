<?php

class Fidesio_Fluidmenu_Model_Status extends Varien_Object {

  const STATUS_ENABLED = 1;  	// Menu status enabled
  const STATUS_DISABLED = 2;  	// Menu status disabled

  /**
   * Elle retourne la liste des options dans un tableau (niveau 1)
   * 
   * @return option array
   */

  static public function getOptionArray() {
    return array(
        self::STATUS_ENABLED => Mage::helper('fluidmenu')->__('Activé'),
        self::STATUS_DISABLED => Mage::helper('fluidmenu')->__('Désactivé')
    );
  }
  
}