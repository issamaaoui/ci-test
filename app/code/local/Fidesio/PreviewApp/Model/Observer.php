<?php

class Fidesio_PreviewApp_Model_Observer extends Mage_Core_Model_Abstract
{

    /**
     * Add Fidesio PreviewApp script to head in frontend
     * @param $observer
     */
    public function addScript($observer)
    {
        if(Mage::helper('previewapp')->isEnabled() and $observer->getLayout()->getBlock('head')) {
            $block = $observer->getLayout()
                ->createBlock('core/text', 'previewapp.script', array('text' => '<script type="text/javascript" src="http://projets.preview-app.net/injection.js"></script>'));

            $observer->getLayout()->getBlock('head')->append($block);
        }
    }

    /**
     * Add Fidesio PreviewApp script before body end in admin
     * @param $observer
     */
    public function addScriptAdmin($observer)
    {
        if(Mage::helper('previewapp')->isEnabled() and $observer->getLayout()->getBlock('js')) {
            $block = $observer->getLayout()
                ->createBlock('core/text', 'previewapp.script', array('text' => '<script type="text/javascript" src="http://projets.preview-app.net/injection.js"></script>'));
            $observer->getLayout()->getBlock('js')->append($block);
        }
    }


    public function setHeader($observer)
    {
        if(Mage::helper('previewapp')->isEnabled()) {
            header('X-Frame-Options: http://projets.preview-app.net/');
        }
    }
}
