<?php
// -----
// Cross Sell Advanced, v2.0.0 for Zen Cart v1.5.7 and later
//
// @copyright 2013 C Jones
// $copyright 2021, lat9 (https://vinosdefrutastropicales.com).
// @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
// @version $Id: init_xsell_config.php v2.0.0, 2022-01-01, lat9 $
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

define('XSELL_CURRENT_VERSION', '2.0.1');

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
            ('Display Cross-Sell Products Maximum', 'MAX_DISPLAY_XSELL', '6', 'This is the maximum number of configured Cross-Sell products to be displayed.<br>Default: 6', $cgi, 25, now(), NULL, NULL),

            ('Cross-Sell Products Columns per Row', 'SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS', '3', 'Identify the number of cross-sells to display per row (on the storefront).  Set the value to <em>0</em> to display <em>all</em> products on a single row.  Default: <b>3</b>.', $cgi, 30, now(), NULL, NULL),
            
            ('Cross-Sell - Display prices?', 'XSELL_DISPLAY_PRICE', 'false', 'Cross-Sell &mdash; Do you want to display the product prices too?<br>Default: false', $cgi, 35, now(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),

            ('Cross Sell Advanced II Version', 'XSELL_VERSION', '0.0.0', 'Current <em>Cross Sell Advanced II</em> Version', $cgi, 1, now(), NULL, 'zen_cfg_read_only(')"
    );

    // -----
    // Add the plugin's database table.
    //
    $db->Execute(
        "CREATE TABLE IF NOT EXISTS " . TABLE_PRODUCTS_XSELL . "(
            ID int(11) NOT NULL auto_increment,
            products_id int(11) NOT NULL,
            xsell_id int(11) NOT NULL,
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

    define('XSELL_VERSION', '0.0.0');
}

if (XSELL_VERSION !== XSELL_CURRENT_VERSION) {
    switch (true) {
        // -----
        // v2.0.0: On installation, provide some fixups from previous versions and remove any previous "Cross Sell Advanced" tool
        // from the admin menus.
        //
        case version_compare(XSELL_VERSION, '2.0.0', '<'):
            // -----
            // The functionality present in the previous "xsell_advanced" module is now provided in the base "xsell"; remove
            // the previous module from the admin pages.
            //
            zen_deregister_admin_pages(['catalogXSellComboAdvancedAdmin']);

            // -----
            // Update configuration settings, removing those now obsolete and updating descriptions of those remaining.
            //
            $db->Execute(
                "UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_title = 'Cross Sell Advanced II Version',
                        configuration_description = 'Current <em>Cross Sell Advanced II</em> Version',
                        sort_order = 1,
                        set_function = 'zen_cfg_read_only('
                  WHERE configuration_key = 'XSELL_VERSION'
                  LIMIT 1"
            );
            $db->Execute(
                "DELETE FROM " . TABLE_CONFIGURATION . "
                  WHERE configuration_key IN ('MIN_DISPLAY_XSELL', 'XSELL_USE_COMMON_SORT_ORDER')"
            );
            $db->Execute(
                "UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_description = 'Identify the maximum number of cross-sells to display on the storefront (default: <b>6</b>).<br><br>Set the value to <b>0</b> to disable the storefront display.'
                  WHERE configuration_key = 'MAX_DISPLAY_XSELL'
                  LIMIT 1"
            );
            $db->Execute(
                "UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_description = 'Identify the number of cross-sells to display per row (on the storefront).  Set the value to <em>0</em> to display <em>all</em> products on a single row.  Default: <b>3</b>.',
                        set_function = NULL
                  WHERE configuration_key = 'SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS'
                  LIMIT 1"
            );
            $db->Execute(
                "UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_description = 'Should the cross-sell product prices be displayed on the storefront (default: \'false\')?'
                  WHERE configuration_key = 'XSELL_DISPLAY_PRICE'
                  LIMIT 1"
            );

            // -----
            // Previous versions of "Cross Sell" plugins used 'unsigned int(10)' for the keys and provided a
            // default value for the products_id and xsell_id fields.
            //
            // Extend all integer fields to 'int(11)', matching the 'products' table, and indicate that the
            // products_id and xsell_id fields must be supplied on an insert.
            //
            $db->Execute(
                "ALTER TABLE " . TABLE_PRODUCTS_XSELL . "
                    MODIFY COLUMN `ID` int(11) NOT NULL auto_increment,
                    MODIFY COLUMN products_id int(11) NOT NULL,
                    MODIFY COLUMN xsell_id int(11) NOT NULL,
                    MODIFY COLUMN sort_order int(11) NOT NULL DEFAULT 1"
            );

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

    // -----
    // Update the plugin's version in the database and let the current admin know that the plugin's
    // been installed or updated.
    //
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = '" . XSELL_CURRENT_VERSION . "'
          WHERE configuration_key = 'XSELL_VERSION'
          LIMIT 1"
    );
    if (XSELL_VERSION === '0.0.0') {
        $messageStack->add_session(sprintf(MESSAGE_XSELL_INSTALLED, XSELL_CURRENT_VERSION), 'success');
    } else {
        $messageStack->add_session(sprintf(MESSAGE_XSELL_UPDATED, XSELL_CURRENT_VERSION), 'success');
    }
}
