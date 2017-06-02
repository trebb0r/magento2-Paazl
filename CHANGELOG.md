# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [1.2.9] - 2017-06-02
### Changed
- Bugfix matrix code when empty

## [1.2.8] - 2017-05-24
### Changed
- added body of request during exception logging

## [1.2.7] - 2017-05-24
### Changed
- prevent undefined variable responseTime during exception

## [1.2.6] - 2017-05-24
### Changed
- Bugfix matrix code
- Add more debug information to Paazl Log when exception occurs

## [1.2.5] - 2017-05-12
### Changed
- Bugfix error message when Paazl is offline
- Bugfix logged in user could not go to Paazl Perfect popup with saved address when coming from product
- Fixup region
- Bugfix loading correct addres data for Paazl Perfect popup

## [1.2.4] - 2017-05-11
### Changed
- Bugfix logged in user could not go to Paazl Perfect popup with saved address
- Bugfix load correct address data for Paazl Perfect popup with saved address

## [1.2.3] - 2017-05-09
### Changed
- Add magento errors to paazl_log for cronjobs
- Bugfix issue when adding address with wrong postcode + house nr or loading a saved adres.
- Bugfix when checkoutStatus gives a half response
- Bugfix no house number on payment step in checkout

## [1.2.2] - 2017-05-05
### Changed
- Added response time to paazl_log
- Bugfix when there are no servicePoints in Paazl Perfect

## [1.2.1] - 2017-05-05
### Changed
- Make Matrix attribute into a select

## [1.2.0] - 2017-05-04
### Changed
- Add notification for delivery type IDM
- Automatically create shipment
- Automatically create shipping labels
- Allow to show Paazl error messages in the checkout or display a custom message
- Bugfix 'select a shipping method' after choosing something in paazl perfect popup
- Bugfix issue radio buttons become disabled after page refresh. See: https://github.com/magento/magento2/issues/7497
- Set correct shipping method coming from Paazl
- Add customsValue for Fedex orders

## [1.0.5] - 2017-04-26
### Changed
- Bugfix for checkoutRequest with error code 1053
- Bugfix class name
- Allow shipping methods who don't have a delivery date

## [1.0.4] - 2017-04-24
### Changed
- Bugfix when there is no lowerBound delivery time.
- Set same text as in popup when only lowerBound or upperBound is available
- Bugfix "Undefined index: deliveryDates" when a delivery has no dates

## [1.0.3] - 2017-04-20
### Changed
- Added preferred delivery date to commitOrder

## [1.0.1] - 2017-04-18
### Changed
- Don't show carrier title

## [1.0.0] - 2017-04-18
### Note
- First release of Paazl_Shipping.
