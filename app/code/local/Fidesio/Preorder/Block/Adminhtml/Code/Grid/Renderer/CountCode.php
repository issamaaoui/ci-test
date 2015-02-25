<?php
class Fidesio_Preorder_Block_Adminhtml_Code_Grid_Renderer_CountCode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $resource           = Mage::getSingleton('core/resource');
        $readConnection     = $resource->getConnection('core_read');
        $tableName          = $resource->getTableName('preorder/code');
        $customer_id        = $row->getId();

        $query = 'SELECT count(*)
                    FROM '.$tableName.'
                    WHERE customer_id ='.$customer_id.'
                    AND status ='.Fidesio_Preorder_Model_Code::STATUT_USED;
        $countOrderCode = $readConnection->fetchOne($query);

        $link_code = '-';
        if($countOrderCode){
            $link_grid_code = Mage::helper("adminhtml")->getUrl('preorder/adminhtml_code/index', array('customer'=>$customer_id));
            $link_code = '<a href="'.$link_grid_code.'" title="'.$countOrderCode.'" >'.$countOrderCode.' code(s)</a>';
        }
        return $link_code;
    }
}
