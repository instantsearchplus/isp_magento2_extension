# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [4.4.7] - 2018-01-01 10:02:00
### Added
- Option to print ids
- Meta keywors support

### Fixed
- Add minimal price to collection count to get accurate results in Getnumofproducts request

## [4.4.6] - 2017-11-27 10:02:00
### Fixed
- Api product updates support

## [4.4.5] - 2017-11-15 10:02:00
### Fixed
- Wrong template at search result page
- Default sorting when serp enabled

## [4.4.4] - 2017-11-14 10:02:00
### Changed
- Legacy code cleanup

## [4.4.3] - 2017-11-13 12:02:00
### Added
- Batches helper, that is responsible for insert/update batches table
- Pagination in sendupdated requests
- Special price from/to support

## [4.4.2] - 2017-10-24 09:25:00
### Changed
- 2.0.* compatibility: Use \Magento\Customer\Model\ResourceModel\Group\Collection class to get all customer groups.

## [4.4.1] - 2017-10-03 14:00:00
### Added
- Cart page and Success pages webhooks.
- MSRP support.
- Added miniform_change flag to vers request.

## [4.3.5] - 2017-08-10 00:00:00
### Changed
- Fall back to small image if base image is missing.

### Added
- Variable products images import.
- Feeds import support.
- Update product attributes from grid support.
- Search configurable product by simple products skus.
- Search Grouped product by simple products skus.
- Refresh cart and messages block after ajax add-to-cart submission.

### Fixed
- Syncing attribute with missing frontend_label.

## [4.3.2] - 2017-07-11 00:00:00
### Changed
- Refactor product rendering at catalog sync.
- Treat disabled product as deleted.

### Added
- Full page cache support.
- Added additional frontend call to bring dynamic js params.
- Grouped product pricing support.
- Search of configurable product by skus of simple products.
- Add UUID and store_id to get Serp template request.
- Add ProductView event handler for adding product sku to js injection.

### Fixed
- Product removal store id.

## Removed
- Attribute cacheable from blocks.
- Checksum files.
- Code duplication.

## [4.2.2] - 2017-05-15 00:00:00
### Fixed
- Api roles bug fix.

## Removed
- Checksum api.

## [4.2.1] - 2017-04-19 00:00:00
### Changed
- Use magento notificatiaon system.
- Sync product attributes that are sytem defined as well as user defined.

### Added
- Added support for attributes which have array value type.

### Fixed
- Store id in products_send.

## [4.2.0] - 2017-02-28 00:00:00
### Changed
- Make Serp and Injection blocks not cacheable.
- Get products colletion with specified attributes only.
- Get current stock status of simple product assigned to configurable.
- Add minimal price (from index) to product collection.

### Added
- Add ajax add-to-cart from Serp.
- Add tiered pricing support.
- Add customer group id to js injection.

### Fixed
- Fix wrong product id in updates xml.
- Fix compilation errors.

## [4.1.0] - 2016-12-12 00:00:00
### Changed
- Optimizing products send.
- Wrap serp template call into try/catch.

### Added
- Add route for seo friendly search url.
- Add miniform url switch.
- Link to isp dashboard with custom font icon.
- Add configurable product price range.

### Fixed
- Fix base image fetch.
- Fix configurable product prices fetch.

## [4.0.11] - 2016-09-25 15:43:19
### Changed
- Add file docs.
- Specify Zend_Http_Client_Adapter_Curl as request adapter.
- Add work around for missing ssl cert (localhost) in comment of buildRequest method.

### Added
- Basic search results mode (ids only).

### Fixed
- Compilation errors in Serp Block class.

## [4.0.9] - 2016-09-25 15:43:19
### Changed
- Make api-endpoint hardcoded.
- Add timeout in seconds as buildRequest method parameter.
- Use productMetaData object instead of objectManager.
- Make Install Call to acp-magento at extension installtion.
- Keep serp template in cache for 1 minute.

## [4.0.8] - 2016-09-08 15:55:58
### Changed
- PSR2 formatting fixes.
- Use object manager for getMultiStoreData request.
- Make remove date in GMT+3 timezone.
- Calculate product purchase popularity.

### Removed
- Attributes.php external image url feature

### Fixed
- Magento version in xml.
- Orders per product query.

## [4.0.7] - 2016-08-10 00:00:00
### Changed
- Stable version of InstantSearch+ for Magento 2 Community (CE) and Enterprise (EE), supports all Magento 1 features






