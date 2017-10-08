# Magento2 Smart Category

Extension Smart Category rules dynamically change the product selection according to a set of conditions (Similar Smart Playlists on iTunes). 

### Category edit page

<img alt="Magento2 Smart Category" src="https://karliuka.github.io/m2/smart-category/category.png" style="width:100%"/>

## Install with Composer as you go

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

Additionally: [Smart Category Configurable](https://github.com/karliuka/m2.SmartCategoryConfigurable)
