<?php
/**
 * Reason for this class:
 * I don't want have to create 3 classes just to display serial numbers in a grid
 */
class PWS_ProductRegistration_Model_Productserialnumberscollection extends Varien_Data_Collection_Db
{
    protected $_idFieldName;

    public function __construct($conn = null)
    {

        $this->setConnection(Mage::getSingleton('core/resource')->getConnection('core_read'));
        parent::__construct(Mage::getSingleton('core/resource')->getConnection('core_read'));
        $this->_initSelect();
    }

    protected function _initSelect()
    {
        $table = Mage::getSingleton('core/resource')->getTableName('pws_productregistration/serial_numbers');

        $this->getSelect()->from(array('main_table' => $table));
        $this->_setIdFieldName('id');
        return $this;
    }

    public function joinProducts()
    {
    	$resource      = Mage::getSingleton('core/resource');
    	$product_table = $resource->getTableName('catalog/product');

    	$productResource = Mage::getResourceSingleton('catalog/product');
    	$nameAttr        = $productResource->getAttribute('name');
    	$nameAttrId      = $nameAttr->getAttributeId();
        $nameAttrTable   = $nameAttr->getBackend()->getTable();

        $tableRegisteredProducts = Mage::getSingleton('core/resource')
            ->getTableName('pws_productregistration/productregistration');

    	$this->getSelect()->joinInner(array('_table_product' => $product_table),
            '_table_product.sku=main_table.sku', array());

        $this->getSelect()->joinInner(
        	array('_table_product_name' => $nameAttrTable),
            '_table_product.entity_id=_table_product_name.entity_id'
                . ' AND _table_product_name.attribute_id = '.(int)$nameAttrId, array())
            ->joinLeft(
                    array('_table_registered_products' => $tableRegisteredProducts),
                    '_table_registered_products.product_id=_table_product.entity_id
                        AND _table_registered_products.serial_number=main_table.valid_serial_number', array())
            ->where('_table_registered_products.serial_number is NULL')
            ->from("",array(
                        'product_name' => "_table_product_name.value",
                        'valid_serial_number' => "main_table.valid_serial_number",
                        )
            );
        return $this;

    }

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_renderFilters()
             ->_renderOrders()
             ->_renderLimit();

        $this->printLogQuery($printQuery, $logQuery);

        $data = $this->_fetchAll($this->_select);
        if (is_array($data)) {
            foreach ($data as $row) {
                $item = $this->getNewEmptyItem();
                if ($this->getIdFieldName()) {
                    $item->setIdFieldName($this->getIdFieldName());
                }
                $item->addData($row);
                $this->addItem($item);
            }
        }

        $this->_setIsLoaded();
        $this->_afterLoad();
        return $this;
    }


}
