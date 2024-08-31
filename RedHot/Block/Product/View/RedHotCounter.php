<?php

namespace Ef\RedHot\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;

/**
 * Class RedHotCounter
 * Handles the logic to retrieve and display the "Red Hot" product count.
 */
class RedHotCounter extends Template
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * RedHotCounter constructor.
     *
     * @param Template\Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resource
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resource,
        Registry $registry,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->resource = $resource;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get the "Red Hot" count for the current product.
     *
     * @return int
     */
    public function getRedHotCount()
    {
        $product = $this->getProduct();
        if ($product) {
            switch ($product->getTypeId()) {
                case 'simple':
                case 'virtual':
                case 'downloadable':
                case 'grouped':
                case 'bundle':
                    return $this->getSingleProductRedHotCount($product);

                case 'configurable':
                    return $this->getConfigurableProductRedHotCount($product);

                default:
                    return 0;
            }
        }
        return 0;
    }

    /**
     * Get the "Red Hot" count for a single product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    protected function getSingleProductRedHotCount($product)
    {
        if ($product->getData('red_hot')) {
            return $this->getProductRedHotCount($product->getSku());
        }
        return 0;
    }

    /**
     * Get the "Red Hot" count for a configurable product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    protected function getConfigurableProductRedHotCount($product)
    {
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        $totalCount = 0;

        foreach ($childProducts as $child) {
            if ($child->getData('red_hot')) {
                $totalCount += $this->getProductRedHotCount($child->getSku());
            }
        }

        return $totalCount;
    }

    /**
     * Retrieve the "Red Hot" count for a product by SKU.
     *
     * @param string $sku
     * @return int
     */
    protected function getProductRedHotCount($sku)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('ef_redhot_product_count');

        $select = $connection->select()->from($tableName, 'add_to_cart_count')->where('sku = ?', $sku);
        $count = $connection->fetchOne($select);

        return $count !== false ? $count : 0;
    }

    /**
     * Retrieve the current product from the registry.
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }
}
