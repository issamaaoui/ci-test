<?php 
class Dvl_Avatar_Model_Api2_Avatar extends Mage_Api2_Model_Resource
{
    /**
     * Resource specific method to retrieve attributes' codes. May be overriden in child.
     *
     * @return array
     */
    protected function _getResourceAttributes()
    {
        return $this->getEavAttributes(true, true);
    } 
}

?>