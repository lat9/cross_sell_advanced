<?php
// -----
// Admin-level installation script for the "encapsulated" Cross-Sell Advanced II plugin for Zen Cart, by lat9.
// Copyright (C) 2021-2026, Vinos de Frutas Tropicales.
//
// Last updated: v3.0.0
//
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    private string $configGroupTitle = 'Cross Sell Advanced';

    protected function executeInstall()
    {
        if ($this->purgeOldFiles() === false) {
            return false;
        }

        // -----
        // First, see if the older (Cross Sell) plugin is installed and, if so, remove its associated
        // configuration settings.
        //
        $this->deleteConfigurationGroup('Cross Sell', true);
        zen_deregister_admin_pages('configXSELL');

        // -----
        // Next, determine the configuration-group-id and install the settings.
        //
        $cgi = $this->getOrCreateConfigGroupId(
            $this->configGroupTitle,
            $this->configGroupTitle . ' Settings'
        );
        $this->executeInstallerSql(
            "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function)
             VALUES
                ('Display Cross-Sell Products Maximum', 'MAX_DISPLAY_XSELL', '6', 'This is the maximum number of configured Cross-Sell products to be displayed.<br>Default: 6', $cgi, 25, now(), NULL, NULL),

                ('Cross-Sell Products Columns per Row', 'SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS', '3', 'Identify the number of cross-sells to display per row (on the storefront).  Set the value to <em>0</em> to display <em>all</em> products on a single row.  Default: <b>3</b>.', $cgi, 30, now(), NULL, NULL),
                
                ('Cross-Sell - Display prices?', 'XSELL_DISPLAY_PRICE', 'false', 'Cross-Sell &mdash; Do you want to display the product prices too?<br>Default: false', $cgi, 35, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],')"
        );

        // -----
        // Add the plugin's database table.
        //
        zen_define_default('TABLE_PRODUCTS_XSELL', DB_PREFIX . 'products_xsell');
        $this->executeInstallerSql(
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

        $this->executeInstallerSql(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_description = 'Identify the maximum number of cross-sells to display on the storefront (default: <b>6</b>).<br><br>Set the value to <b>0</b> to disable the storefront display.'
              WHERE configuration_key = 'MAX_DISPLAY_XSELL'
              LIMIT 1"
        );
        $this->executeInstallerSql(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_description = 'Identify the number of cross-sells to display per row (on the storefront).  Set the value to <em>0</em> to display <em>all</em> products on a single row.  Default: <b>3</b>.',
                    set_function = NULL
              WHERE configuration_key = 'SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS'
              LIMIT 1"
        );
        $this->executeInstallerSql(
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
        $this->executeInstallerSql(
            "ALTER TABLE " . TABLE_PRODUCTS_XSELL . "
                MODIFY COLUMN `ID` int(11) NOT NULL auto_increment,
                MODIFY COLUMN products_id int(11) NOT NULL,
                MODIFY COLUMN xsell_id int(11) NOT NULL,
                MODIFY COLUMN sort_order int(11) NOT NULL DEFAULT 1"
        );

        // -----
        // Remove duplicate entries in the 'products_xsell' table possibly allowed by previous "Cross Sell" plugins.
        //
        $xsells = $this->executeInstallerSelectQuery(
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
                $this->executeInstallerSql(
                    "DELETE FROM " . TABLE_PRODUCTS_XSELL . "
                      WHERE `ID` = " . $next_xsell['ID'] . "
                      LIMIT 1"
                );
            }
        }
        if ($xsells_removed !== 0) {
            $this->errorContainer->addError('warning', sprintf(MESSAGE_XSELL_DUPLICATES_REMOVED, $xsells_removed), true);
        }

        // -----
        // Now, remove any cross-sell products (and their cross-sells) that no longer exist.
        //
        $this->executeInstallerSql(
            "DELETE FROM " . TABLE_PRODUCTS_XSELL . "
              WHERE products_id NOT IN (SELECT p.products_id FROM " . TABLE_PRODUCTS . " p)
                 OR xsell_id NOT IN (SELECT p.products_id FROM " . TABLE_PRODUCTS . " p)"
        );
        $xsells_removed = $this->dbConn->affectedRows();
        if ($xsells_removed !== 0) {
            $this->errorContainer->addError('warning', sprintf(MESSAGE_XSELL_REMOVED, $xsells_removed), true);
        }

        // -----
        // Remove configuration settings associated with older versions of the plugin.
        //
        $this->executeInstallerSql(
            "DELETE FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key IN ('MIN_DISPLAY_XSELL', 'XSELL_USE_COMMON_SORT_ORDER', 'XSELL_VERSION')"
        );

        parent::executeInstall();

        return true;
    }

    // -----
    // Not used, initially, but included for the possibility of future upgrades!
    //
    // Note: This (https://github.com/zencart/zencart/pull/6498) Zen Cart PR must
    // be present in the base code or a PHP Fatal error is generated due to the
    // function signature difference.
    //
    protected function executeUpgrade($oldVersion)
    {
        parent::executeUpgrade($oldVersion);
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['configVat4Eu']);

        $this->deleteConfigurationGroup($this->configGroupTitle, true);

        parent::executeUninstall();
    }

    protected function purgeOldFiles(): bool
    {
        $log_messages = [];

        $files_to_check = [
            '' => [
                'xsell.php',
            ],
            'includes/auto_loaders/' => [
                'config.xsell.php',
            ],
            'includes/classes/observers/' => [
                'XsellAdminObserver.php',
            ],
            'includes/extra_datafiles/' => [
                'xsell_advanced_definitions.php',
                'xsell_definitions.php',
            ],
            'includes/functions/extra_functions/' => [
                'cross_sell_plus_advanced_page_registration.php',
                'zen_cfg_read_only.php',
            ],
            'includes/init_includes/' => [
                'init_xsell_config.php',
            ],
            'includes/javascript/' => [
                'xsell.php',
            ],
            'includes/languages/english/extra_definitions/' => [
                'advanced_xsell_defs.php',
                'lang.x-sell_defs.php',
                'x-sell_defs.php',
            ],
            'includes/languages/english/' => [
                'lang.xsell.php',
                'xsell.php',
            ],
            'includes/modules/xsell/' => [
                'category_product_selection.php',
            ],
        ];
        $admin_files_removed_ok = $this->removeFiles($files_to_check, 'admin');

        $files_to_check = [
            'includes/extra_datafiles/' => [
                'xsell_definitions.php',
            ],
            'includes/languages/english/extra_definitions/' => [
                'lang.xsell_product_definitions.php',
                'xsell_product_definitions.php',
            ],
            'includes/modules/' => [
                'xsell2_products.php',
            ],
            'includes/templates/bootstrap/templates/' => [
                'tpl_modules_xsell2_products.php',
            ],
            'includes/templates/template_default/templates/' => [
                'tpl_modules_xsell2_products.php',
            ],
        ];
        $catalog_files_removed_ok = $this->removeFiles($files_to_check, 'catalog');

        return ($admin_files_removed_ok && $catalog_files_removed_ok);
    }

    /**
     * Removes, if they exist, a list of files from either the 'admin' or 'catalog' side.
     *
     * The $files_to_remove input is an associative array with each key being
     * an admin/catalog sub-directory and the value(s) being an array of files
     * to be removed from the key directory.
     *
     * If a file-name is identified as '*.*', then **ALL** files and sub-directories
     * as well as the upper, keyed, directory are removed.
     *
     * @since ZC v3.0.0
     */
    protected function removeFiles(array $files_to_remove, string $context): bool
    {
        if (method_exists(get_parent_class($this), 'removeFiles')) {
            return parent::removeFiles($files_to_remove, $context);
        }

        if (!in_array($context, ['admin', 'catalog'])) {
            $error_message = sprintf(ERROR_REMOVE_FILES_CONTEXT, $context);
            $this->errorContainer->addError(0, $error_message, true, $error_message);
            return false;
        }

        $errorOccurred = false;

        $base_dir = ($context === 'admin') ? DIR_FS_ADMIN : DIR_FS_CATALOG;
        foreach ($files_to_remove as $dir => $files) {
            $current_dir = $base_dir . $dir;
            foreach ($files as $next_file) {
                $current_file = $current_dir . $next_file;
                if (str_ends_with($current_file, '*.*')) {
                    if ($this->removeDirectoryAndFilesRecursive(str_replace('/*.*', '', $current_file)) === false) {
                        $errorOccurred = true;
                    }
                    continue;
                }

                if (file_exists($current_file)) {
                    unlink($current_file);
                    if (file_exists($current_file)) {
                        $errorOccurred = true;
                        $this->errorContainer->addError(
                            0,
                            sprintf(ERROR_REMOVE_FILES_CANT_DELETE, $current_file),
                            false,
                            // this str_replace has to do DIR_FS_ADMIN before CATALOG because catalog is contained within admin, so results are wrong.
                            // also, '[admin_directory]' is used to obfuscate the admin dir name, in case the user copy/pastes output to a public forum for help.
                            sprintf(ERROR_REMOVE_FILES_CANT_DELETE, str_replace([DIR_FS_ADMIN, DIR_FS_CATALOG], ['[admin_directory]/', ''], $current_file))
                        );
                    }
                }
            }
        }

        return !$errorOccurred;
    }
}
