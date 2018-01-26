# Magento2 Smart Category

[![Total Downloads](https://poser.pugx.org/faonni/module-smart-category/downloads)](https://packagist.org/packages/faonni/module-smart-category)
[![Latest Stable Version](https://poser.pugx.org/faonni/module-smart-category/v/stable)](https://packagist.org/packages/faonni/module-smart-category)	

Extension Smart Category rules dynamically change the product selection according to a set of conditions (Similar Smart Playlists on iTunes).

You can create categories based on rules you specify, and then update these categories automatically as your products changes.

For example, you could create a category  includes only new products. Or you could create a categories of products by a particular brand, color, size, etc. You can add as many conditions to the expression as needed to describe the products to include.

## Compatibility

Magento CE 2.1.x, 2.2.x

## Install

#### Install with Composer as you go

1. Go to Magento2 root folder

2. Enter following commands to install module:

    ```bash
    composer require faonni/module-smart-category-kit
    ```
   Wait while dependencies are updated.

3. Enter following commands to enable module:

    ```bash
	php bin/magento setup:upgrade
	php bin/magento setup:di:compile
	php bin/magento setup:static-content:deploy  (optional)

### Category edit page

<img alt="Magento2 Smart Category" src="https://karliuka.github.io/m2/smart-category/category.png" style="width:100%"/>

Additionally: [Smart Category Configurable](https://github.com/karliuka/m2.SmartCategoryConfigurable)
