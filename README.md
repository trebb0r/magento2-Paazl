# magento2-Paazl
Magento 2 Paazl integration

[Changelog](CHANGELOG.md)

## Installation

```BASH
composer config repositories.honl/magento2-paazl vcs git@github.com:ho-nl/magento2-Paazl.git
composer require paazl/magento2

bin/magento setup:upgrade
```

## Configure
The following settings are required to be able to create shipments:

- Configure Stores -> Configuration -> General -> Store Information
- Configure Stores -> Configuration -> Shipping Settings -> Origin

If you want to use custom street, housenumber and housenumber additional fields, you 
may add field names in config.xml:
```
<exclude>
	<street_name></street_name>
	<house_number></house_number>
	<house_number_addition></house_number_addition>
</exclude>
   ```
   
## Note
The default Magento street field (multiline) will be removed in forms including it's validation. Instead seperate field will be used: Street Name, House Number and House Number Addition.
This module will save the information from these seperate fields back into the default Magento street field on the first line using a customer_address_save_before event.

If you are using Magento Enterprise and are already using certain fields for House Number, etc. see the usage of the `<exclude>` config and convert your fields to have to following attribute_code: street_name, house_number, house_number_addition.
