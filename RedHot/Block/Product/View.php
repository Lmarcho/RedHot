<?php

namespace Ef\RedHot\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\View as MagentoProductView;
use Magento\Framework\App\ResourceConnection;

/**
 * Class View
 * Extends the Magento product view block to include "Red Hot" product count functionality.
 */
class View extends MagentoProductView
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
     * View constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->resource = $resource;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
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
}
