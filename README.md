# Cross Sell Advanced II, v2.0.3
This repository contains the ***Cross Sell Advanced II*** plugin, for Zen Cart v1.5.7 and later (including 1.5.8),  which was derived from v1.5 of the previous version (https://www.zen-cart.com/downloads.php?do=file&id=400).  The plugin enables a site to display a list of additional, cross-sell products when viewing the details of an individual product.

This version of the plugin:

1. Has been rewritten to take advantage of the Zen Cart 1.5.7/1.5.8 admin 'base' and now requires Zen Cart 1.5.7 as its minimum Zen Cart version.
2. Combines the functionality of the two admin tools provided in previous versions of the plugin.
3. Monitors for product-removals and removes entries from its database table when a cross-sell product is removed from the store.
4. Provides a [Database I/O Manager](https://www.zen-cart.com/downloads.php?do=file&id=2091) handler, enabling the cross-sells to be manipulated via a `.csv` import.

***Zen Cart Support Thread:*** https://www.zen-cart.com/showthread.php?211884-Cross-Sell-Advanced-Support-Thread

***Zen Cart Download Link:*** https://www.zen-cart.com/downloads.php?do=file&id=2334

For additional documentation of the plugin's admin interfaces, see [Cross Sell Admin Changes](https://github.com/lat9/cross_sell_advanced/wiki/Cross-Sell-Advanced-II:-Admin-Interfaces).

For additional documentation of the plugin's storefront interfaces, see [Cross Sell Storefront Changes](./pages/storefront_interfaces.md)

