<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 5 févr. 2015
 *
 **/
class Te_Sapbyd_Model_Service_Soap_Client_ReadOutBoundDelivery
    extends Te_Sapbyd_Model_Service_Soap_Client_Abstract
{
    protected $_mainNode = 'OutboundDelivery';

    public function getTrackingId($uuid)
    {
        return $this->Read($uuid);
    }

    /**
     * Perform arguments pre-processing
     *
     * @param array $arguments
     */
    protected function _preProcessArguments($arguments)
    {
        return array(array('OutboundDelivery' => array('UUID' => $arguments[0])));
    }

    protected function _getFormattedResponse($result)
    {
        if (is_object($result)) {
            if (isset($result->TransportationTerms->TransportTracking->ID)) {
                return $result->TransportationTerms->TransportTracking->ID;
            }
        }
        if (is_array($result)) {
            foreach ($result as $delivery) {
                if ($id = $this->_getFormattedResponse($delivery)) {
                    return $id;
                }
            }
        }
    }
}