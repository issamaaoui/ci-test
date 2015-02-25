<?php
$installer = $this;
$installer->startSetup();

// I should have added a name for the product_id foreign key
$connection = Mage::getSingleton('core/resource')->getConnection('core_read');

/*$sql = "SELECT
   COLUMN_NAME,
   CONSTRAINT_NAME
FROM
    `information_schema`.`KEY_COLUMN_USAGE`
WHERE
    `constraint_schema` = SCHEMA()
AND
    `table_name` = 'customer_product_registrations'
AND
    `referenced_column_name` IS NOT NULL
ORDER BY
    `column_name`";
$records = $connection->fetchAll($sql);
$fkName = '';
foreach ($records as $record) {
    if ($record['COLUMN_NAME'] == 'product_id') {
        $fkName = $record['CONSTRAINT_NAME'];
    }
}*/
$installer->run("
ALTER TABLE {$this->getTable('customer_product_registrations')} ADD `store_id` int NOT NULL DEFAULT 0;
ALTER TABLE {$this->getTable('customer_product_registrations')} ADD `product_name` varchar(255);
");

/*if ($fkName) {
    $installer->run("
ALTER TABLE {$this->getTable('customer_product_registrations')} DROP FOREIGN KEY `" . $fkName . "`;
");
}*/

$installer->endSetup();
