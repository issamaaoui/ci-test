<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 5 févr. 2015
 *
**/

class Te_Sapbyd_Model_Service_Soap_Response
{
    protected $_attributes = array();

    /**
     *
     * @param stdClass $response
     */
    public function __construct($response = null)
    {
        if (!is_object($response)) {
            return;
        }
        foreach ($response as $property => $value) {
            if (is_object($value)) {
                if (property_exists($value, '_')) {
                    $this->{$property} = $value->_;
                    unset($value->_);
                    $reflection = new ReflectionObject($value);
                    foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
                        $this->_attributes[$property][$prop->getName()] = $value->{$prop->getName()};
                    }
                } else {
                    $this->{$property} = new self($value);
                }
            } elseif (is_array($value)) {
                foreach ($value as $index => $subValue) {
                    $this->{$property}[$index] = new self($subValue);
                }
            } else {
                $this->{$property} = $value;
            }
        }
    }

    public function getAttribute($node, $attr)
    {
        if (isset($this->_attributes[$node][$attr])) {
            return $this->_attributes[$node][$attr];
        }
    }
}