<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 9 févr. 2015
 *
**/
class Te_Sapbyd_Model_Service
    implements Te_Sapbyd_Model_Service_Interface
{
    /**
     *
     * @var Te_Sapbydesign_Model_Config
     */
    protected $_config;

    public function getInvoice($incrementId)
    {
        return $this->_fetchMethod('invoice', $incrementId);
    }

    public function getUUID($outbounDeliveryID)
    {
        return $this->_fetchMethod('uuid', $outbounDeliveryID);
    }

    public function getTrackingId($uuid)
    {
        return $this->_fetchMethod('tracking', $uuid);
    }

    protected function _fetchMethod($type, $arg)
    {
        $method = $this->getConfig()->getMethod($type);
        $model = $this->getConfig()->getModel($type);
        if (!$method  || !$model) {
            return;
        }

        return call_user_func(array(Mage::getModel($model), $method), $arg);
    }

    public function getConfig()
    {
        if (null === $this->_config) {
            $this->_config = Mage::getSingleton('te_sapbyd/config');
        }

        return $this->_config;
    }
}