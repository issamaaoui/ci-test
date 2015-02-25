<?php

class Fidesio_Preorder_Model_Code extends Mage_Core_Model_Abstract
{
    const STATUT_USED = 1;
    const STATUT_NOT_USED = 2;

    public function _construct()
    {
        parent::_construct();
        $this->_init('preorder/code');
    }

	 /**
     * Elle permer de savoir le code d'achat est actif ou pas.
     * @return bool
     */
    public function isActif()
    {
        return $this->getStatus() == Fidesio_Preorder_Model_Code::STATUT_NOT_USED;
    }

    static public function getStatusOption() {
        return array(
            self::STATUT_USED       => Mage::helper('preorder')->__('Used'),
            self::STATUT_NOT_USED   => Mage::helper('preorder')->__('Not used')
        );
    }

    static public function getStatusValues(){
        return array(
            array(
                'value' => self::STATUT_USED,
                'label' => Mage::helper('preorder')->__('Used'),
            ),
            array(
                'value' => self::STATUT_NOT_USED,
                'label' => Mage::helper('preorder')->__('Not used'),
            )
        );
    }

    /**
     * Elle permet de rechercher le model by code
     * @param $code
     */
    public function loadByCode($code){
        $collection = Mage::getModel('preorder/code')
                        ->getCollection()
                        ->addFieldToFilter('code', $code)
                        ->addFieldToFilter('status', self::STATUT_NOT_USED)
                        ->getFirstItem();
        return $collection;
    }

}
?>