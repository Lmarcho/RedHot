<?php

namespace Ef\RedHot\Plugin\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Flatrate
{
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

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
                if ($product->getCustomAttribute('red_hot') && $product->getCustomAttribute('red_hot')->getValue() == 1) {
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
