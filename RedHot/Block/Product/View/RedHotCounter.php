<?php

namespace Ef\RedHot\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;

class RedHotCounter extends Template
{
    protected $productRepository;
    protected $resource;
    protected $_registry;

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

    public function getRedHotCount()
    {
        $product = $this->getProduct();
        if ($product) {
            switch ($product->getTypeId()) {
                case 'simple':
                case 'virtual':
                case 'downloadable':
                case 'grouped':
                    return $this->getSingleProductRedHotCount($product);

                case 'configurable':
                    return $this->getConfigurableProductRedHotCount($product);

                case 'bundle':
                    return $this->getBundleProductRedHotCount($product);

                default:
                    return 0;
            }
        }
        return 0;
    }

    protected function getSingleProductRedHotCount($product)
    {
        if ($product->getData('red_hot')) {
            return $this->getProductRedHotCount($product->getSku());
        }
        return 0;
    }

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

    protected function getBundleProductRedHotCount($product)
    {
        $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
            $product->getTypeInstance()->getOptionsIds($product),
            $product
        );

        $totalCount = 0;

        foreach ($selectionCollection as $selection) {
            $childProduct = $this->productRepository->getById($selection->getProductId());
            if ($childProduct->getData('red_hot')) {
                $totalCount += $this->getProductRedHotCount($childProduct->getSku());
            }
        }

        return $totalCount;
    }

    protected function getProductRedHotCount($sku)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('ef_redhot_product_count');

        $select = $connection->select()->from($tableName, 'add_to_cart_count')->where('sku = ?', $sku);
        $count = $connection->fetchOne($select);

        return $count !== false ? $count : 0;
    }

    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }
}
