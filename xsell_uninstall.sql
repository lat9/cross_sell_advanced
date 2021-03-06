DELETE FROM configuration WHERE configuration_key IN ('MIN_DISPLAY_XSELL', 'MAX_DISPLAY_XSELL', 'SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS', 'XSELL_DISPLAY_PRICE', 'XSELL_USE_COMMON_SORT_ORDER', 'XSELL_VERSION');
DELETE FROM configuration_group WHERE configuration_group_title = 'Cross Sell Advanced' LIMIT 1;
DELETE FROM admin_pages WHERE page_key IN ('configXsellCombo', 'catalogXSellComboAdmin', 'catalogXSellComboAdvancedAdmin');

# Un-comment below to also remove the cross-sell database table.
#DROP TABLE products_xsell;