<?php

namespace Ef\RedHot\Plugin\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class Flatrate
 *
 * Plugin to adjust the flat rate shipping method if the cart contains "Red Hot" products.
 */
class Flatrate
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Flatrate constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * Around plugin for collectRates method.
     *
     * Adjusts the shipping price if "Red Hot" products are present in the cart.
     *
     * @param \Magento\OfflineShipping\Model\Carrier\Flatrate $subject
     * @param \Closure $proceed
     * @param RateRequest $request
     * @return Result
     */
    public function aroundCollectRates(
        \Magento\OfflineShipping\Model\Carrier\Flatrate $subject,
        \Closure $proceed,
        RateRequest $request
    ) {
        $result = $proceed($request);

        if ($result && $subject->getConfigFlag('active')) {
            $shippingPrice = $subject->getConfigData('price');

            foreach ($request->getAllItems() as $item) {
                $productId = $item->getProduct()->getId();
                $product = $this->productRepository->getById($productId);

                $redHotAttribute = $product->getCustomAttribute('red_hot');
                if ($redHotAttribute && $redHotAttribute->getValue() == 1) {
                    $shippingPrice = 10.00;
                    break;
                }
            }

            foreach ($result->getAllRates() as $rate) {
                if ($rate->getCarrier() == 'flatrate') {
                    $rate->setPrice($shippingPrice);
                    $rate->setCost($shippingPrice);
                }
            }
        }

        return $result;
    }
}
