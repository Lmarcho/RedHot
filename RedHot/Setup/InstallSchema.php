<?php

namespace Ef\RedHot\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$setup->tableExists('ef_redhot_product_count')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('ef_redhot_product_count')
            )
                ->addColumn(
                    'sku',
                    Table::TYPE_TEXT,
                    64,
                    ['nullable' => false, 'primary' => true],
                    'Product SKU'
                )
                ->addColumn(
                    'add_to_cart_count',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Add to Cart Count'
                )
                ->setComment('RedHot Product Add to Cart Count Table');

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
