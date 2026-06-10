<?php
// -----
// Part of the "Cross-Sell Advanced II" plugin for Zen Cart 2.1.0 and later.
// Copyright (c) 2021-2026, Vinos de Frutas Tropicales.
//
// Note: Requires DbIo v2.2.1 or later; using prior versions can result in PHP Warnings/Errors!
//
if (!defined ('IS_ADMIN_FLAG')) {
    exit ('Illegal access');
}

// -----
// This DbIo class handles the customizations required for the import and export of records in the
// 'products_xsell' table, which provided the mappings used by the "Cross-Sell Advanced II" plugin.
//
class DbIoProductsXsellHandler extends DbIoHandler 
{
    protected const string DBIO_COMMAND_ADD = 'ADD';   //-Forces the current record to be added, so long as the data's valid!

    // -----
    // Return values from the 'importCheckIdModelMatch' method:
    //
    protected const int ID_MODEL_OK             = 0;  //-The supplied products_id and products_model reference the same, present product.
    protected const int ID_MODEL_MISMATCH       = 1;  //-The supplied products_id doesn't match its supplied products_model.
    protected const int MODEL_NO_EXIST          = 2;  //-The supplied products_model doesn't match any records in the 'products' table.
    protected const int MODEL_MULTIPLE_PRODUCTS = 3;  //-The supplied products_model matches multiple products.
    protected const int ID_NO_EXIST             = 4;  //-The supplied products_id doesn't exist.

    public static function getHandlerInformation(): array|false
    {
        global $sniffer;
        if (!defined('TABLE_PRODUCTS_XSELL') || !$sniffer->table_exists(TABLE_PRODUCTS_XSELL)) {
            if (!isset($_SESSION['xsell_warning_reported'])) {
                $_SESSION['xsell_warning_reported'] = true;
                trigger_error('ProductsXsellHandler cannot be used; missing required database elements.', E_USER_WARNING);
            }
            return false;
        }

        DbIoHandler::loadHandlerMessageFile('ProductsXsell');
        return array (
            'version' => '3.0.0',
            'handler_version' => '1.0.0',
            'include_header' => true,
            'export_only' => false,
            'description' => DBIO_PRODUCTSXSELL_DESCRIPTION,
        );
    }

    public function exportInitialize($language = 'all'): bool
    {
        $initialized = parent::exportInitialize($language);
        if ($initialized) {
            if ($this->where_clause != '') {
                $this->where_clause .= ' AND ';
            }
            $this->where_clause .= "p.products_id = x.products_id";
            $this->order_by_clause .= 'x.products_id ASC, x.sort_order ASC';
        }
        return $initialized;
    }

    // -----
    // For each 'products_xsell' row's export, append the cross-sell product's model number to the output.
    //
    public function exportPrepareFields(array $fields): array
    {
        unset($fields['ID']);
        $fields['xsell_model'] = zen_get_products_model($fields['xsell_id']);

        return parent::exportPrepareFields($fields);
    }

// ----------------------------------------------------------------------------------
//             I N T E R N A L / P R O T E C T E D   F U N C T I O N S
// ----------------------------------------------------------------------------------

    // -----
    // This function, called during the overall class construction, is used to set this handler's database
    // configuration for the dbIO operations.
    //
    protected function setHandlerConfiguration(): void
    {
        $this->stats['report_name'] = 'ProductsXsell';
        $this->config = self::getHandlerInformation();
        $this->config['supports_dbio_commands'] = true;
        $this->config['handler_does_import'] = true;  //-Indicate that **all** the import-based database manipulations are performed by this handler
        $this->config['keys'] = [
            TABLE_PRODUCTS => [
                'alias' => 'p',
                'capture_key_value' => true,
               'products_id' => [
                    'type' => self::DBIO_KEY_IS_VARIABLE | self::DBIO_KEY_SELECTED,
                ],
                'products_model' => [
                    'type' => self::DBIO_KEY_IS_VARIABLE | self::DBIO_KEY_SELECTED | self::DBIO_KEY_IS_ALTERNATE,
                ],
            ],
            TABLE_PRODUCTS_XSELL => [
                'alias' => 'x',
                'products_id' => [
                    'type' => self::DBIO_KEY_IS_FIXED,
                    'match_fixed_key' => 'p.products_id',
                ],
            ],
        ];
        $this->config['tables'] = [
            TABLE_PRODUCTS => [
                'alias' => 'p',
                'key_fields_only' => true,
            ],
            TABLE_PRODUCTS_XSELL => [
                'alias' => 'x',
                'io_field_overrides' => [
                    'products_id' => false,
                    'ID' => 'no-header',
                ],
            ], 
        ];
        $this->config['additional_headers'] = [
            'v_xsell_model' => self::DBIO_FLAG_NONE,
        ];
    }

    // -----
    // This handler is controlling all import operations.  Check to see that the current import record
    // has valid 'keys' (i.e. products_id and xsell_id).
    //
    protected function importCheckKeyValue(array $data): bool
    {
        global $db;

        $this->record_status = true;

        // -----
        // Retrieve the 'base' products_id and products-model for the current record.
        //
        $products_model = $this->importGetFieldValue('products_model', $data);
        $products_id = $this->importGetFieldValue('products_id', $data);

        if (empty($products_model) && empty($products_id)) {
            $this->record_status = false;
            $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; products_id and/or products_model must be supplied.", self::DBIO_ERROR);
        } else {
            [$rc, $products_id] = $this->importCheckIdModelMatch($products_id, $products_model);
            switch ($rc) {
                case self::ID_MODEL_OK:
                    break;
                case self::ID_MODEL_MISMATCH:
                    $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; supplied products_id and products_model don't match.", self::DBIO_ERROR);
                    break;
                case self::MODEL_NO_EXIST:
                    $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; products_model does not exist.", self::DBIO_ERROR);
                    break;
                case self::MODEL_MULTIPLE_PRODUCTS:
                    $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; products_model is associated with multiple products.", self::DBIO_ERROR);
                    break;
                case self::ID_NO_EXIST:
                    $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; products_id does not exist.", self::DBIO_ERROR);
                    break;
                default:
                    break;
            }
            if ($rc !== self::ID_MODEL_OK) {
                $this->record_status = false;
            }
        }

        // -----
        // If the 'base' products_id is OK, check that the xsell_model/xsell_id values reference a unique product, too.
        //
        if ($this->record_status === true) {
            $xsell_model = $this->importGetFieldValue('xsell_model', $data);
            $xsell_id = $this->importGetFieldValue('xsell_id', $data);

            if (empty($xsell_model) && empty($xsell_id)) {
                $this->record_status = false;
                $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; xsell_id and/or xsell_model must be supplied.", self::DBIO_ERROR);
            }

            [$rc, $xsell_id] = $this->importCheckIdModelMatch($xsell_id, $xsell_model);
            switch ($rc) {
                case self::ID_MODEL_OK:
                    break;
                case self::ID_MODEL_MISMATCH:
                    $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; supplied xsell_id and xsell_model don't match.", self::DBIO_ERROR);
                    break;
                case self::MODEL_NO_EXIST:
                    $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; xsell_model does not exist.", self::DBIO_ERROR);
                    break;
                case self::MODEL_MULTIPLE_PRODUCTS:
                    $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; xsell_model is associated with multiple products.", self::DBIO_ERROR);
                    break;
                case self::ID_NO_EXIST:
                    $this->debugMessage("Record not imported at line #" . $this->stats['record_count'] . "; xsell_id does not exist.", self::DBIO_ERROR);
                    break;
                default:
                    break;
            }
            if ($rc !== self::ID_MODEL_OK) {
                $this->record_status = false;
            }
        }

        if ($this->record_status === true) {
            $this->saved_data = [
                'products_id' => $products_id,
                'xsell_id' => $xsell_id,
                'sort_order' => (int)$this->importGetFieldValue('sort_order', $data),
            ];

            $check = $db->Execute(
                "SELECT *
                   FROM " . TABLE_PRODUCTS_XSELL . "
                  WHERE products_id = $products_id
                    AND xsell_id = $xsell_id
                  LIMIT 1"
            );
            $this->import_is_insert = $check->EOF;
            $this->debugMessage('Key values: ' . json_encode($this->saved_data) . ', is_insert(' . $this->import_is_insert . ').', self::DBIO_STATUS);
        }

        return $this->record_status;
    }

    // -----
    // Stubbed-out for this handler, the record's checking has been performed in importCheckKeyValue.
    //
    protected function importProcessField(string $table_name, string $field_name, string $language_id, ?string $field_value): void
    {
    }

    // -----
    // For this handler, any finishing-up is to update the sort order for a pre-existing
    // cross-sell.
    //
    protected function importFinishProcessing(): void
    {
        global $db;

        if ($this->import_is_insert === true) {
            $this->record_status = false;
            $this->debugMessage("Import disallowed at line #" . $this->stats['record_count'] . "; use the 'ADD' command to add new cross-sell records.", self::DBIO_ERROR);
        } else {
            $this->debugMessage("Updating cross-sell at line #" . $this->stats['record_count'], self::DBIO_STATUS);
            if ($this->operation !== 'check') {
                $db->Execute(
                    "UPDATE " . TABLE_PRODUCTS_XSELL . "
                        SET sort_order = " . $this->saved_data['sort_order'] . "
                      WHERE products_id = " . $this->saved_data['products_id'] . "
                        AND xsell_id = " . $this->saved_data['xsell_id'] . "
                      LIMIT 1"
                );
            }
        }
    }

    // -----
    // This function, called by the base DbIoHandler class when a non-blank v_dbio_command field is found in the
    // current import-record:
    //
    // - ADD:    Forces the current xsell-record to be inserted.
    // - REMOVE: Removes a product-xsell from the database.
    //
    protected function importHandleDbIoCommand(string $command, array $data): bool
    {
        global $db;

        $continue_line_import = false;
        $command = dbio_strtoupper($command);

        // -----
        // Operation performed based on the command requested.
        //
        switch ($command) {
            // -----
            // ADD: The current CSV record's import can continue, forced as an insert operation ... so long as
            // a valid 'products_model' and 'xsell_model' value have been included in the record.
            //
            case self::DBIO_COMMAND_ADD:
                if ($this->import_is_insert === false) {
                    $this->debugMessage("ADD disallowed at line #" . $this->stats['record_count'] . '; the associated cross-sell already exists.', self::DBIO_ERROR);
                    $this->record_status = false;
                    break;
                }

                if ($this->operation !== 'check') {
                    $db->Execute(
                        "INSERT INTO " . TABLE_PRODUCTS_XSELL . "
                            (products_id, xsell_id, sort_order)
                         VALUES
                            (" . $this->saved_data['products_id'] . ", " . $this->saved_data['xsell_id'] . ", " . $this->saved_data['sort_order'] . ")"
                    );
                }
                break;

            // -----
            // REMOVE: Removes a product's cross-sell from the database.
            //
            case self::DBIO_COMMAND_REMOVE:
                if ($this->import_is_insert) {
                    $this->debugMessage("Cross-sell not removed at line #" . $this->stats['record_count'] . "; it does not exist.", self::DBIO_WARNING);
                } else {
                    $this->debugMessage("Removing cross-sell for ID #" . $this->saved_data['products_id'] . ", xsell ID #" . $this->saved_data['xsell_id'], self::DBIO_STATUS);
                    if ($this->operation !== 'check') {
                        $db->Execute(
                            "DELETE FROM " . TABLE_PRODUCTS_XSELL . "
                              WHERE products_id = " . $this->saved_data['products_id'] . "
                                AND xsell_id = " . $this->saved_data['xsell_id'] . "
                              LIMIT 1"
                        );
                    }
                }
                break;

            default:
                $this->debugMessage("Unrecognized command ($command) found at line #" . $this->stats['record_count'] . "; the operation was not performed.", self::DBIO_ERROR);
                break;
        }
        return $continue_line_import;
    }

    // -----
    // A common 'checker' to see that either the supplied products_id or products_model matches
    // a single, defined product; called from importHandleDbIoCommand.  The caller has previously
    // verified that at least one of the values isn't "empty".
    //
    protected function importCheckIdModelMatch(string $products_id, string $products_model): array
    {
        global $db;

        $rc = self::ID_MODEL_OK;

        // -----
        // If no products_id is supplied, attempt to locate a unique id based on the model supplied.
        //
        if (empty($products_id)) {
            $check = $db->Execute(
                "SELECT products_id
                   FROM " . TABLE_PRODUCTS . "
                  WHERE products_model = '" . zen_db_input($products_model) . "'
                  LIMIT 2"
            );
            switch ($check->RecordCount()) {
                case 0:
                    $rc = self::MODEL_NO_EXIST;
                    break;
                case 1:
                    $products_id = $check->fields['products_id'];
                    break;
                default:
                    $rc = self::MODEL_MULTIPLE_PRODUCTS;
                    break;
            }
        } else {
            $check = $db->Execute(
                "SELECT products_model
                   FROM " . TABLE_PRODUCTS . "
                  WHERE products_id = " . (int)$products_id . "
                  LIMIT 1"
            );
            if ($check->EOF) {
                $rc = self::ID_NO_EXIST;
            } elseif (!empty($products_model) && dbio_strtoupper($products_model) !== dbio_strtoupper($check->fields['products_model'])) {
                $rc = self::ID_MODEL_MISMATCH;
            }
        }
        return [$rc, $products_id];
    }
}
