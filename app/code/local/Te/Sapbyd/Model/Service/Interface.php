<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 9 févr. 2015
 *
**/
interface Te_Sapbyd_Model_Service_Interface
{
    /**
     *
     * @param string $incrementId
     * @return Varien_Object
     */
    public function getInvoice($incrementId);

    /**
     *
     * @param integer $outbounDeliveryID
     * @return string
     */
    public function getUUID($outbounDeliveryID);

    /**
     *
     * @param string|array $uuid
     * @return string
     */
    public function getTrackingId($uuid);
}