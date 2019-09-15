# Instant Search + Magento 2  [![Latest Stable Version](https://poser.pugx.org/instantsearch/instantsearchplus/v/stable)](https://packagist.org/packages/instantsearch/instantsearchplus)
Search That Boosts Conversion: Fastest Semantic Search, Search Filters, and Search Autocomplete

Installation
------------
1.	Visit and purchase the free InstantSearch+ Extension at [Magento Marketplace](https://marketplace.magento.com/instantsearch-instantsearchplus.html)
2.  Connect to your server through SSH terminal (e.g. PUTTY.exe)
3.	 Navigate to your Magento root (where app folder is)
4.	Make sure **php** is in your environment path (run ```php --help```  - see that you get list of possible commands) if  not - contact your server provider to find path to ftp executable.
5.	Disable Compilation by setting developer mode ```php bin/magento deploy:mode:set developer```
6.	Run following commands:  
    a.	```composer require instantsearch/instantsearchplus```  
    b.	Insert your public/private keys, as username/password from [Your Account](https://marketplace.magento.com/customer/accessKeys/list/)  
    c.	```php bin/magento module:enable Autocompleteplus_Autosuggest```  
    d.	```php bin/magento setup:upgrade```  
    e.	```php bin/magento cache:flush```  
    f.	```php bin/magento deploy:mode:set production```  
    

Upgrade
------------  
1. Run following commands  
    a. ```composer require instantsearch/instantsearchplus:LAST_VERSION_NUMBER```  
    b. ```php bin/magento setup:upgrade```    
    c. ```php bin/magento cache:flush```   


