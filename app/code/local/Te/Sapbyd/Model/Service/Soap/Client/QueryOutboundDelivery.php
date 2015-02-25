<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 4 févr. 2015
 *
 **/
class Te_Sapbyd_Model_Service_Soap_Client_QueryOutboundDelivery
    extends Te_Sapbyd_Model_Service_Soap_Client_Abstract
{
    protected $_mainNode = 'OutboundDelivery';

    public function getUUID($outboundDeliveryID)
    {
        return $this->FindByElements($outboundDeliveryID);
    }

    /**
     * Perform arguments pre-processing
     *
     * @param array $arguments
     */
    protected function _preProcessArguments($arguments)
    {
        $processedArguments = array(
            'OutboundDeliveryFindByElementsRequestMessageBody' => array(
                'SelectionByItemBusinessTransactionDocumentReferenceSalesOrderItemReferenceID' => array(
                    'InclusionExclusionCode' => 'I',
                    'IntervalBoundaryTypeCode' => 1,
                    'LowerBoundaryIdentifier' => $arguments[0],
                ),
            ),
            'ProcessingConditions' => array(
                'QueryHitsUnlimitedIndicator' => false,
            ),
        );

        return array($processedArguments);
    }

    protected function _getFormattedResponse($result)
    {
        if (is_object($result)) {
            return $result->OutboundDeliveryUUID;
        }
        if (is_array($result)) {
            $uuid = array();
            foreach ($result as $delivery) {
                $uuid[] = $this->_getFormattedResponse($delivery);
            }
        }

        return $uuid;
    }
}