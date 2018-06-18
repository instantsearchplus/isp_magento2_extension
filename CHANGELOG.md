# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [4.0.7] - 2016-08-10 00:00:00
### Changed
- Stable version of InstantSearch+ for Magento 2 Community (CE) and Enterprise (EE), supports all Magento 1 features

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

## [4.0.9] - 2016-09-25 15:43:19
### Changed
- Make api-endpoint hardcoded.
- Add timeout in seconds as buildRequest method parameter.
- Use productMetaData object instead of objectManager.
- Make Install Call to acp-magento at extension installtion.
- Keep serp template in cache for 1 minute.

## [4.0.11] - 2016-09-25 15:43:19
### Changed
- Add file docs.
- Specify Zend_Http_Client_Adapter_Curl as request adapter.
- Add work around for missing ssl cert (localhost) in comment of buildRequest method.

### Added
- Basic search results mode (ids only).

### Fixed
- Compilation errors in Serp Block class.

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