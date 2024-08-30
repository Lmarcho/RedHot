<?php

namespace Ef\RedHot\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product;

class AddToCartObserver implements ObserverInterface
{
    protected $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getData('red_hot')) {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('ef_redhot_product_count');

            $sku = $product->getSku();
            $select = $connection->select()->from($tableName, 'add_to_cart_count')->where('sku = ?', $sku);
            $count = $connection->fetchOne($select);

            if ($count !== false) {
                // SKU exists, increment the count
                $connection->update(
                    $tableName,
                    ['add_to_cart_count' => $count + 1],
                    ['sku = ?' => $sku]
                );
            } else {
                // SKU does not exist, insert a new row
                $connection->insert(
                    $tableName,
                    ['sku' => $sku, 'add_to_cart_count' => 1]
                );
            }
        }
    }
}
