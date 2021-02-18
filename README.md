# magento
Payment gateway module for Magento 2

composer require quickpay/magento2
php bin/magento module:enable QuickPay_Gateway
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
php bin/magento setup:di:compile
php bin/magento cache:clean
