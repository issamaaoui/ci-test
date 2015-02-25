<?php
class Dvl_Sapbyd_Block_Web extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getWeb()
    {
        if (!$this->hasData('sapbyd')) {
            $this->setData('sapbyd', Mage::registry('sapbyd'));
        }
        return $this->getData('sapbyd');

    }
}