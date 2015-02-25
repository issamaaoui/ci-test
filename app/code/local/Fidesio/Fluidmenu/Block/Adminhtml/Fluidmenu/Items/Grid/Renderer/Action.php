<?php
class Fidesio_Fluidmenu_Block_Adminhtml_Fluidmenu_Items_Grid_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$href = $row->getRealUrl();
    	$title = "Lien: ".$row->getName();
        return '<a href="'.$href.'" target="_blank" title="'.$title.'" >'.$this->__('Preview').'</a>';
    }
}
