# Ef_RedHot Module for Magento 2

## Overview

The **Ef_RedHot** module is a custom Magento 2 extension that adds a "Red Hot" feature to products. This feature allows store owners to highlight certain products and display a count of how many customers have added the product to their cart. Additionally, the module adjusts the flat rate shipping cost for orders containing "Red Hot" products.

## Features

- Adds a `red_hot` attribute to products to mark them as "Red Hot".
- Displays the count of how many customers love the product on the product page.
- Adjusts flat rate shipping to $10 for orders containing "Red Hot" products.
- Supports configurable, bundle, simple, virtual, downloadable, and grouped products.

## Installation

### Step 1: Upload the Module

Place the `Ef` folder in `app/code/` directory of your Magento installation.

### Step 2: Enable the Module

```bash
php bin/magento module:enable Ef_RedHot
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

### Step 3: Verify Installation

Ensure the module is enabled:

```bash
php bin/magento module:status Ef_RedHot
```

## Usage

### Marking a Product as "Red Hot"

1. In the Magento Admin Panel, navigate to `Catalog` > `Products`.
2. Edit a product and set the `Red Hot Product` attribute to "Yes".
3. Save the product.

### Displaying Customer Count on Product Page

Visit the product page to see the message indicating how many customers have added the product to their cart.

### Adjusting Flat Rate Shipping

- The flat rate shipping cost will automatically be adjusted to $10 for orders containing a "Red Hot" product.

## Configuration

### Attribute Set

The `red_hot` attribute is added to the "Product Details" attribute group in the default attribute set.

### Database Table

The module creates a table `ef_redhot_product_count` to track the number of times "Red Hot" products are added to the cart.


## License

This module is open-source and licensed under the [MIT License](LICENSE).
