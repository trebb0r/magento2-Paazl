# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.3.0-rc1] - To be released

With the 1.3.0 release we're introducing a better way for Paazl to store customer address information in Magento. By default, Magento handles the street information in a single field and uses multiple lines to store the information. This is considered suboptimal as it causes a lot of problems with compatibility. New separate Customer attributes are introduced: street_name, house_number, house_number_addition.

*EE Only: If you're already using custom Customer attributes (e.g. `house number`), make sure it follows Paazl's exact naming convention (it are varchar fields).*

- All UI components that have address fields are replaced with the new customer attributes (frontend / backend).
- All existing addresses are automatically converted to the new format when they are loaded.
- To maintain 100% compatibility, before saving the customer address a new flattened value is stored in the original street attribute.

### Improvements / Bug fixes
A lot of bug fixes and small improvements are included in this release:
- The product matrix is now configurable from A-ZZ instead of A-Z.
- Added option to enable the postcode validation (disabled by default due to UI issues with addresses outside the Netherlands, better UI planned for a future release).
- Checkout field sort order set to: Street Number, House Number, House Number Addition, Postcode, City, State, Country
- Solve issue in the cart where the tax would not be calculated properly because Paazl expects a full address instead of only a postcode. (Reported by ISM e-Company)
- House number now has numeric validation on the frontend
- Solve issue in the cart where filling in your state would throw a JS error.
- Solve issue where saving a customer address would throw an exception. CE Only
- Solve issue where a reorder would throw an exception. (Reported by Guapa)
- Solve issue where having a different configuration per website would cause issues (Reported by Guapa).

### Known Issues
- When the cron job is trying to communicate orders to Paazl and Paazl throws an error for a single order, the rest of the orders wont be processed.
- 'Admin Panel Order Create' and 'Frontend multishipping' does not support Paazl Perfect, only basic functionality is supported
- Admin Panel View Order Edit address does not save house number. Confirmed as Magento Core bug.

## [1.2.10] - 2017-07-06
### Changed
- Bugfix commit order would not use correct paazl account in a multistore setup

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
