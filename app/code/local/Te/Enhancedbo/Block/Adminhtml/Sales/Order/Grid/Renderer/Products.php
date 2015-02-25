<?php

class Te_Enhancedbo_Block_Adminhtml_Sales_Order_Grid_Renderer_Products extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$order_id = $row->getData("entity_id");
		$items = $row->getItemsCollection();
		$html = array();
		foreach($items as $item)
		{
			$html []= ((int)$item->getQtyOrdered())." x ".$item->getName();
		}
		 $value = implode("\n",$html);
		return $value;
	}
}