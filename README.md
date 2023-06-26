# Fast Simon for Adobe Magento 2  [![Latest Stable Version](https://poser.pugx.org/instantsearch/instantsearchplus/v/stable)](https://packagist.org/packages/instantsearch/instantsearchplus)

Magento AI‑Based Search & Merchandising To Maximize Conversion Rate.   
The best and fastest growing brands use Fast Simon to improve and optimize their entire shopping experience.

Fast Simon is helping merchants maximize the value of their online traffic to spark, sustain, and boost their growth. Current Fast Simon merchants report growth of up to 40% year-over-year after adopting the powerful search platform. 

Boost Conversion with Search, Merchandising  & Personalization

* Shoppers using search are on 4X more likely to buy
* Great Search can boost conversion by 30-150%
* Cross-sell & Upsell increase sales by up to 30%
* Personalization will boost conversion rates up to 50%
* Smart online merchandising that keeps you in control

Installation
------------
1.	Visit and purchase the free Fast Simon Extension at [Magento Marketplace](https://marketplace.magento.com/instantsearch-instantsearchplus.html)
2.  Connect to your server through SSH terminal (e.g. PUTTY.exe)
3.	Navigate to your Magento root (where app folder is)
4.	Make sure **php** is in your environment path (run ```php --help```  - see that you get list of possible commands) if  not - contact your server provider to find path to ftp executable.
5.	Enable maintenance mode ```php bin/magento maintenance:enable```
6.	Run following commands:  
	a.	```composer require instantsearch/instantsearchplus```  
	b.	Insert your public/private keys, as username/password from [Your Account](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/prerequisites/authentication-keys.html?lang=en)  
	c.	```php bin/magento module:enable Autocompleteplus_Autosuggest --clear-static-content```  
	d.	```php bin/magento setup:upgrade```  
	e.	```php bin/magento setup:di:compile```  
	e.	```php bin/magento cache:clean```  
	f.	```php bin/magento maintenance:disable```  
    

Upgrade
------------  
1. Run following commands  
	a.	```composer require instantsearch/instantsearchplus:REPLACE_ME_WITH_THE_LAST_VERSION_NUMBER```  
	b.	```php bin/magento setup:upgrade```  
	c.	```php bin/magento setup:di:compile```  
	d.	```php bin/magento cache:clean```   


