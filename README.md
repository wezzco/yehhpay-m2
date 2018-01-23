# Yehhpay Magento 2 Extension

This extension will enable merchants to accept post delivery payments via Yehhpay to increase their conversion rates.
With Yehhpay, merchants can offer their customers an convenient post delivery payment method and at the same time control credit risks.

When installing this extension, an additional payment method will be added to your checkout.

In this installation manual we assume you have already got an account with us. If not, you can apply for an account with Yehhpay on https://account.postcode.nl/aanmelden/yehhpay

The extension was created by Wezz e-Commerce for Yehhpay.

__Looking for the Yehhpay Magento 1 Extension?__

https://www.wezz.co/extensions/yehhpay

__FAQ__

https://www.wezz.co/extensions/yehhpay

__API Documentation__

This extension uses Yehhpay's Merchant API. Documentation of the API can be found on
https://api.yehhpay.nl/documentation/merchant/api

# Installation


## 2. Installation

### 2.1 Installation Magento Marketplace

Soon this extension will be available on Magento Marketplace.

### 2.2 Installation through Composer

1. On the command line, go to your Magento root folder.
2. Run the following command to add the extension to your codebase:

`
composer require wezz/yehhpay-m2
`

3. Enable the extension in Magento using the following commands

`
php bin/magento module:enable Wezz_Yehhpay
`

`
php bin/magento setup:upgrade
`

`
php bin/magento cache:clean
`

And, when running production mode:

`
php bin/magento setup:static-content:deploy
`

Now, you can log in to the Magento backend and configure and enable the extension.

## Configuration

Navigate to the module configuration using the following path:

__Stores__ -> __Configuration__ -> __Sales__ -> Yehhpay

### Fields

Fill in the following fields to configure your Yehhpay extension.


#### General
| Field | Explanation |
| :--- | :--- |
| Enabled | Option to enable/disable the module on your Magento store |
| Payment mode | Switch between test and live payment mode |
| Application key | Please find your Application key in your Yehhpay account |
| Application secret | Please find your Application secret in your Yehhpay account |
| Minimum Order Total | The minimum order amount that can be payed with Yehhpay |
| Maximum Order Total | The maximum order amount that can be payed with Yehhpay |

#### Advanced

| Field | Explanation |
| :--- | :--- |
| Check on billing and delivery addresses | Yehhpay requires customers shippings and invoice address to be the same, must be set to __yes__. |
| Service identifier | Please find your Application identifier in your Yehhpay account |
| Frontend label | The name of the Yehhpay Payment method in the Magento checkout |
| Payment success status | The status of the order when the payment module returns status success |
| Payment failed status | The status of the order when the payment module returns status failed |
| Payment from Specific Countries | Select specific country from with customers can pay with Yehhpay, leave empty for __all countries__ |

## Troubleshooting


For __Frequently Asked Questions__ please visit [https://www.wezz.co/extensions/yehhpay](https://www.wezz.co/extensions/yehhpay).
