<?php
Class Te_SapbydExport_Model_Source_Environment
{

	public function toOptionArray(){
		$options = array(
			array('value'=>'dev', 'label'=>Mage::helper('adminhtml')->__("Development")),
			array('value'=>'stage', 'label'=>Mage::helper('adminhtml')->__("Stage")),
			array('value'=>'production', 'label'=>Mage::helper('adminhtml')->__("Production"))
		);
		return $options;
	}

}