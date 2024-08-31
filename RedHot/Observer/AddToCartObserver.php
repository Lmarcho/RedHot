<?php

namespace Ef\RedHot\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ResourceConnection;

/**
 * Class AddToCartObserver
 * Observer to track and increment the count of Red Hot products added to the cart.
 */
class AddToCartObserver implements ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * AddToCartObserver constructor.
     *
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Execute the observer to update the add-to-cart count for Red Hot products.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $sku = $product->getSku();

        // Check if the product is a bundle
        if ($product->getTypeId() == 'bundle') {
            // Use the main product SKU instead of the complex SKU
            $sku = $product->getData()['sku'];
        }

        if ($product->getData('red_hot')) {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('ef_redhot_product_count');

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
