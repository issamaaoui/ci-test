<?php
class PWS_ProductRegistration_Model_Adminhtml_System_Config_Backend_Serialnumbers extends Mage_Core_Model_Config_Data
{

    public function _afterSave()
    {
        $this->uploadAndImport($this);
    }

    public function uploadAndImport(Varien_Object $object)
    {
        $exceptions = array();

        if (!isset($_FILES['groups'])) {
            return false;
        }
        $csvFile     = $_FILES['groups']['tmp_name']['import_settings']['fields']['import_serial_numbers']['value'];
        $csvFileName = $_FILES['groups']['name']['import_settings']['fields']['import_serial_numbers']['value'];

        if (empty($csvFile)) return $this;

        $fileFormat = $_FILES['groups']['type']['import_settings']['fields']['import_serial_numbers']['value'];

        if (substr($csvFileName, -4) != '.csv') {
            $exceptions[] = Mage::helper('pws_productregistration')->__('Invalid File Extension , the file extension must be csv');
            throw new Exception( "\n" . implode("\n", $exceptions) );
        }

        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $rowNum = 1;
            while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($line);

                if ($num != '2') {
                    $exceptions[] = Mage::helper('pws_productregistration')->__('Invalid File Format, you have %s columns/row on line %s, the correct format is: sku, serial_numbers; eg: "abcd-123", "123456"', $num, $rowNum);
                    break;
                }
                $data[$line[0]][] = array(
                   'sku'                 => trim($line[0]),
                   'valid_serial_number' => trim($line[1]),
                );
                $rowNum++;
            }
            fclose($handle);
        }

        if (empty($exceptions)) {
            try{
                $config = Mage::helper('pws_productregistration')->getConfig();

                $table = Mage::getSingleton('core/resource')->getTableName('pws_productregistration/serial_numbers');
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');

                //remove previous data
                //$connection->delete($table);

                foreach ($data as $sku => $records) {
                    if ($config['import_mode'] == PWS_ProductRegistration_Model_Adminhtml_System_Config_Source_Mode::MODE_UPDATE) {
                        $where = $connection->quoteInto('sku = ?', $sku);
                        $connection->delete($table, $where);
                    }
                    $previousSerialNumbers = $this->getSerialNumbersForSku($sku);
                    foreach ($records as $dataLine) {
                       if (in_array($dataLine['valid_serial_number'], $previousSerialNumbers)) continue;
                       if (strlen($dataLine['valid_serial_number']) == 0) continue;
                       $connection->insert($table, $dataLine);
                    }
                }

            } catch (Exception $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        if (!empty($exceptions)) {
            throw new Exception( "\n" . implode("\n", $exceptions) );
        }

        return $this;
    }

    public function getSerialNumbersForSku($sku)
    {
        $skuSerialNumbers = array();

        $table      = Mage::getSingleton('core/resource')->getTableName('pws_productregistration/serial_numbers');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select     = $connection->select()
            ->from($table)
            ->where('sku = ?', $sku);

        if ($records = $connection->fetchAll($select)) {
            foreach ($records as $data) {
                $skuSerialNumbers[] = trim($data['valid_serial_number']);
            }
        }

        return $skuSerialNumbers;
    }


    public function getProductSkuForSerialNumber($serialNumber)
    {

        $productSku = '';

        $table      = Mage::getSingleton('core/resource')->getTableName('pws_productregistration/serial_numbers');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select     = $connection->select()
            ->from($table)
            ->where('LOWER(valid_serial_number) = ?', strtolower($serialNumber));

        $record = $connection->fetchRow($select);

        if ($record) {
            $productSku = $record['sku'];
        }

        return $productSku;
    }


    public function getUnusedSerialNumbersForSku($sku)
    {
        $skuSerialNumbers = array();

        $table = Mage::getSingleton('core/resource')->getTableName('pws_productregistration/serial_numbers');
        $tableRegisteredProducts = Mage::getSingleton('core/resource')->getTableName('pws_productregistration/productregistration');
        $product_table = Mage::getSingleton('core/resource')->getTableName('catalog/product');


        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select     = $connection->select()
            ->from(array('main_table' =>$table))
            ->joinInner(array('_table_product' => $product_table),
                '_table_product.sku=main_table.sku', array())
            ->joinLeft(
                array('_table_registered_products' => $tableRegisteredProducts),
                '_table_registered_products.product_id=_table_product.entity_id
                    AND _table_registered_products.serial_number=main_table.valid_serial_number', array())
            ->where('main_table.sku = ? AND _table_registered_products.serial_number is NULL', $sku);

        if ($records = $connection->fetchAll($select)) {
            foreach ($records as $data) {
                $skuSerialNumbers[] = trim($data['valid_serial_number']);
            }
        }

        return $skuSerialNumbers;
    }


    public function isValidSerialNumber($sku, $serialNumber)
    {
        $skuSerialNumbers = $this->getSerialNumbersForSku($sku);
        $serialNumber     = trim($serialNumber);

        // case insensitive
        foreach ($skuSerialNumbers as $skuSerialNumber) {
            if (strtolower($skuSerialNumber) == strtolower($serialNumber)) return true;
        }

        return false;
    }

    /**
     * This is to implement case insensitive registration for serial numbers
     * It returns the actual serial number (not the one user entered)
     */
    public function getRealSerialNumber($sku, $serialNumber)
    {
        $skuSerialNumbers = $this->getSerialNumbersForSku($sku);
        $serialNumber     = trim($serialNumber);

        foreach ($skuSerialNumbers as $skuSerialNumber) {
            if (strtolower($skuSerialNumber) == strtolower($serialNumber)) return $skuSerialNumber;
        }

        return $serialNumber;
    }
}
