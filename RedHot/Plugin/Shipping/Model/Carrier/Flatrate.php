<?php

namespace Ef\RedHot\Plugin\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Flatrate
{
    /**
     * Adjust the flat rate shipping price if the cart contains a "RedHot" product.
     *
     * @param \Magento\OfflineShipping\Model\Carrier\Flatrate $subject
     * @param Result $result
     * @param RateRequest $request
     * @return Result
     */
    public function aroundCollectRates(
        \Magento\OfflineShipping\Model\Carrier\Flatrate $subject,
        \Closure $proceed,
        RateRequest $request
    ) {
        $result = $proceed($request);

        // Check if the result is valid and the flat rate shipping is active
        if ($result && $subject->getConfigFlag('active')) {
            $shippingPrice = $subject->getConfigData('price');

            // Iterate through all items in the request and check for "RedHot" products
            foreach ($request->getAllItems() as $item) {
                $product = $item->getProduct();
                if ($product->getData('red_hot')) {
                    $shippingPrice = 10.00; // Change shipping price to $10
                    break;
                }
            }

            // Adjust the shipping price for each rate in the result
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
