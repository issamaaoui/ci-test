<?php

class Fidesio_PreviewApp_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'previewapp/base_config/enabled';

    public function isEnabled()
    {
        return Mage::getStoreConfig(static::XML_PATH_ENABLED);
    }
}
