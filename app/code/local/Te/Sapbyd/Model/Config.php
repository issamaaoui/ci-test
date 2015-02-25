<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 9 févr. 2015
 *
**/
class Te_Sapbyd_Model_Config extends Varien_Simplexml_Config
{
    const CACHE_TAG         = 'service_sapbyd';

    public function __construct($sourceData=null)
    {
        $this->setCacheId(self::CACHE_TAG);
        $this->setCacheTags(array(self::CACHE_TAG));
        $this->setCacheChecksum(null);

        parent::__construct($sourceData);
        $this->_construct();
    }

    protected function _construct()
    {
        if (Mage::app()->useCache(self::CACHE_TAG)) {
            if ($this->loadCache()) {
                return $this;
            }
        }

        $config = Mage::getConfig()->loadModulesConfiguration('service.xml');
        $this->setXml($config->getNode('sapbyd'));

        if (Mage::app()->useCache(self::CACHE_TAG)) {
            $this->saveCache();
        }

        return $this;
    }

    public function getDefaultAdapter()
    {
        return (string)$this->getNode('adapters/default/use');
    }

    public function getMethod($type)
    {
        return (string)$this->getNode("methods/{$type}");
    }

    public function getModel($type)
    {
        return (string)$this->getNode("adapters/{$this->getDefaultAdapter()}/models/{$type}");
    }
}