# magento
Payment gateway module for Magento 2

### Installation
```
composer require freepay/magento2
php bin/magento module:enable FreePay_Gateway
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
``` 
