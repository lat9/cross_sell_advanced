# Cross Sell Advanced II: Admin Interfaces

## Configuration Settings

The plugin includes an auto-loading installation script that creates (or updates) various configuration settings and database tables used by the plugin.  A new table (`products_xsell`) is created that maps a primary product, via its `products_id`, to other `products_id` values as cross-sell products.

A new set of configuration settings (***Cross Sell Advanced II***) enables an admin to control the storefront display of any product cross-sells:

![Configuration Settings](../images/configuration.png)

| Configuration Title                 | Default | Description                                                  |
| ----------------------------------- | ------- | ------------------------------------------------------------ |
| Cross Sell Advanced II Version      | varies  | Displays the (read-only) current version of the plugin.      |
| Display Cross Sell Products Maximum | 6       | Identifies the maximum number of cross-sell products to display for a given product.  Set the value to `0` to disable the storefront display. |
| Cross-Sell Products Columns Per Row | 3       | Identifies the number of cross-sell products to display per storefront centerbox row. |
| Cross-Sell: Display Prices?         | false   | Indicates whether (`true`) or not (`false`) to include the price of each cross-sell product on the storefront display. |



