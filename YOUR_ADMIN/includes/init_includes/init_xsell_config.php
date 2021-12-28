<?php
// -----
// Cross Sell Advanced, v2.0.0 for Zen Cart v1.5.7 and later
//
// @copyright 2013 C Jones
// $copyright 2021, lat9 (https://vinosdefrutastropicales.com).
// @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
// @version $Id: init_xsell_config.php v2.0.0, 2021-12-24, lat9 $
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

define('XSELL_CURRENT_VERSION', '2.0.0-beta3');

// -----
// Only update configuration when an admin is logged in.
//
if (!isset($_SESSION['admin_id'])) {
    return;
}

// -----
// First, see if the older (Cross Sell) plugin is installed and, if so, remove its associated
// configuration settings.
//
$result = $db->Execute(
    "SELECT configuration_group_id
       FROM " . TABLE_CONFIGURATION_GROUP . "
      WHERE configuration_group_title = 'Cross Sell'
      LIMIT 1"
);
if (!$result->EOF) {
    $old_cgi = $result->fields['configuration_group_id'];
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = $old_cgi");
    $db->Execute("DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = $old_cgi");
    zen_deregister_admin_pages('configXSELL');
}

// -----
// Now, check to see if the Cross Sell Advanced configuration has been previously set, adding the plugin's
// configuration settings if not.
//
$result = $db->Execute(
    "SELECT configuration_group_id
       FROM " . TABLE_CONFIGURATION_GROUP . "
      WHERE configuration_group_title = 'Cross Sell Advanced'
      LIMIT 1"
);
if (!$result->EOF) {
    $cgi = $result->fields['configuration_group_id'];
} else {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
            (configuration_group_title, configuration_group_description, sort_order, visible)
         VALUES
            ('Cross Sell Advanced', 'Cross Sell Advanced Configuration', 0, 1)"
    );
    $cgi = $db->Insert_ID();
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION_GROUP . "
            SET sort_order = $cgi
          WHERE configuration_group_id = $cgi
          LIMIT 1"
    );
    $db->Execute(
        "DELETE FROM " . TABLE_CONFIGURATION . "
          WHERE configuration_key IN ('MIN_DISPLAY_XSELL', 'MAX_DISPLAY_XSELL', 'SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS', 'XSELL_DISPLAY_PRICE', 'XSELL_USE_COMMON_SORT_ORDER', 'XSELL_VERSION')"
    );
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function)
         VALUES
            ('Display Cross-Sell Products Minimum', 'MIN_DISPLAY_XSELL', '1', 'This is the minimum number of configured Cross-Sell products required in order to cause the Cross Sell information to be displayed.<br>Default: 1', $cgi, 20, now(), NULL, NULL),

            ('Display Cross-Sell Products Maximum', 'MAX_DISPLAY_XSELL', '6', 'This is the maximum number of configured Cross-Sell products to be displayed.<br>Default: 6', $cgi, 25, now(), NULL, NULL),

            ('Cross-Sell Products Columns per Row', 'SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS', '3', 'Cross-Sell Products Columns to display per Row<br>0= off or set the sort order.<br>Default: 3', $cgi, 30, now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\'),'),
            
            ('Cross-Sell - Display prices?', 'XSELL_DISPLAY_PRICE', 'false', 'Cross-Sell &mdash; Do you want to display the product prices too?<br>Default: false', $cgi, 35, now(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),

            ('Cross-Sell - Use common sort order?', 'XSELL_USE_COMMON_SORT_ORDER', 'false', 'Cross-Sell &mdash; Use per product sort order (on Catalog-&gt;Cross-Sell Admin) or common sort order (on Catalog-&gt;Categories/Products Edit) ?<br>Default: false', $cgi, 38, now(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),

            ('Cross Sell Advanced Version', 'XSELL_VERSION', '0.0.0', 'Cross Sell Advanced Version (DO NOT MODIFY THIS VALUE!)', $cgi, 1, now(), NULL, NULL)"
    );

    // -----
    // Add the plugin's database table.
    //
    $db->Execute(
        "CREATE TABLE IF NOT EXISTS " . TABLE_PRODUCTS_XSELL . "(
            ID int(11) NOT NULL auto_increment,
            products_id int(11) NOT NULL DEFAULT 1,
            xsell_id int(11) NOT NULL DEFAULT 1,
            sort_order int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (ID),
            KEY idx_products_id_xsell (products_id)
         )"
    );

    // -----
    // Register the plugin's configuration and tools in the admin menus.
    //
    zen_deregister_admin_pages(['configXsellCombo', 'catalogXSellComboAdmin', 'catalogXSellComboAdvancedAdmin']);
    zen_register_admin_page('configXsellCombo', 'BOX_CONFIGURATION_XSELL', 'FILENAME_CONFIGURATION', 'gID=' . $cgi, 'configuration', 'Y');
    zen_register_admin_page('catalogXSellComboAdmin', 'BOX_CATALOG_XSELL', 'FILENAME_XSELL', '', 'catalog', 'Y');
    zen_register_admin_page('catalogXSellComboAdvancedAdmin', 'BOX_CATALOG_XSELL_ADVANCED', 'FILENAME_XSELL_ADVANCED', '', 'catalog', 'Y');

    define('XSELL_VERSION', '0.0.0');
}

if (XSELL_VERSION !== XSELL_CURRENT_VERSION) {
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = '" . XSELL_CURRENT_VERSION . "'
          WHERE configuration_key = 'XSELL_VERSION'
          LIMIT 1"
    );

    switch (true) {
        // -----
        // v2.0.0: On installation, provide some fixups from previous versions.
        //
        case version_compare(XSELL_VERSION, '2.0.0', '<'):
            // -----
            // Remove duplicate entries in the 'products_xsell' table possibly allowed by previous "Cross Sell" plugins.
            //
            $xsells = $db->Execute(
                "SELECT *
                   FROM " . TABLE_PRODUCTS_XSELL . "
                  ORDER BY `ID` ASC"
            );
            $xsells_found = [];
            $xsells_removed = 0;
            foreach ($xsells as $next_xsell) {
                if (!in_array($next_xsell['products_id'] . '^' . $next_xsell['xsell_id'], $xsells_found)) {
                    $xsells_found[] = $next_xsell['products_id'] . '^' . $next_xsell['xsell_id'];
                } else {
                    $xsells_removed++;
                    $db->Execute(
                        "DELETE FROM " . TABLE_PRODUCTS_XSELL . "
                          WHERE `ID` = " . $next_xsell['ID'] . "
                          LIMIT 1"
                    );
                }
            }
            if ($xsells_removed !== 0) {
                $messageStack->add_session(sprintf(MESSAGE_XSELL_DUPLICATES_REMOVED, $xsells_removed), 'warning');
            }

            // -----
            // Now, remove any cross-sell products (and their cross-sells) that no longer exist.
            //
            $db->Execute(
                "DELETE FROM " . TABLE_PRODUCTS_XSELL . "
                  WHERE products_id NOT IN (SELECT p.products_id FROM " . TABLE_PRODUCTS . " p)
                     OR xsell_id NOT IN (SELECT p.products_id FROM " . TABLE_PRODUCTS . " p)"
            );
            $xsells_removed = $db->affectedRows();
            if ($xsells_removed !== 0) {
                $messageStack->add_session(sprintf(MESSAGE_XSELL_REMOVED, $xsells_removed), 'warning');
            }
        default:                                            //-Fall-through from the above processing.
            break;
    }

    if (XSELL_VERSION === '0.0.0') {
        $messageStack->add_session(sprintf(MESSAGE_XSELL_INSTALLED, XSELL_CURRENT_VERSION), 'success');
    } else {
        $messageStack->add_session(sprintf(MESSAGE_XSELL_UPDATED, XSELL_CURRENT_VERSION), 'success');
    }
}
