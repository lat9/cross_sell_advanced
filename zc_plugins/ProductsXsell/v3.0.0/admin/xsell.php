<?php
/**
 * Cross Sell Advanced
 *
 * Derived from:
 * Original Idea From Isaac Mualem im@imwebdesigning.com
 * Portions Copyright (c) 2002 osCommerce
 * Complete Recoding From Stephen Walker admin@snjcomputers.com
 * Released under the GNU General Public License
 *
 * Adapted to Zen Cart by Merlin - Spring 2005
 * Reworked for Zen Cart v1.3.0  03-30-2006
 * Reworked for ZenCart V1.5.2 by RodG Dec 2013
 * Reworked for Zen Cart v1.5.7+ by lat9, Dec. 2021
 */
require 'includes/application_top.php';

// -----
// Bring in the currencies' class, used by the zen_draw_products_pulldown function within
// /admin/includes/modules/xsell/category_product_selection.php.
//
require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

$currentPage = !empty($_GET['page']) ? (int)$_GET['page'] : 0;
if ($currentPage < 1) {
    $currentPage = 1;
}
$page_param = ($currentPage > 1) ? "page=$currentPage" : '';

// -----
// Initialize the languages-id in use and determine the action/next-action to be performed.
//
$languages_id = $_SESSION['languages_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$next_action = $_POST['next_action'] ?? $_GET['next_action'] ?? '';

// -----
// Initialize variables used by the forms present in /includes/modules/xsell/category_product_selection.php
//
if (!empty($_POST['xsell_pid'])) {
    $xsell_pid = (int)$_POST['xsell_pid'];
} elseif (!empty($_GET['xsell_pid'])) {
    $xsell_pid = (int)$_GET['xsell_pid'];
} else {
    $xsell_pid = 0;
}

$xsell_main_pid = (int)($_POST['xsell_main_pid'] ?? $_GET['xsell_main_pid'] ?? 0);

if (!empty($_POST['xsell_category_id'])) {
    $xsell_category_id = (int)$_POST['xsell_category_id'];
} elseif (!empty($_GET['xsell_category_id'])) {
    $xsell_category_id = (int)$_GET['xsell_category_id'];
} else {
    $xsell_category_id = 0;
}

switch ($action) {
    // -----
    // A new category has been selected via xsell's category_product_selection.php forms, either to choose
    // a new, main product to cross-sell or to add a cross-sell product to a main product.
    //
    case 'new_cat':
        if ($next_action !== '') {
            $next_action = '&action=' . $next_action;
        }
        if ($xsell_main_pid !== 0) {
            $next_action .= '&xsell_main_pid=' . $xsell_main_pid;
        }
        zen_redirect(zen_href_link(FILENAME_XSELL, $page_param . '&xsell_category_id=' . $xsell_category_id . $next_action));
        break;

    // -----
    // A product has been selected from the upper categories/products form, then this is either a request
    // to create a new cross-sell (selecting the main product) or to add a product cross-sell to a selected
    // product.
    //
    case 'set_xsell_pid':
        // -----
        // If selected from the plugin's main-page, then a 'main' product has been selected for cross-sell
        // definitions; the next action will be to create that main cross-sell.
        //
        if ($next_action === '') {
            $next_action = 'new_xsell';
            $main_pid = (int)$xsell_pid;
        // -----
        // Otherwise, a product was selected from the 'new_xsell' action.  That product is a cross-sell for the
        // currently-selected main product.
        //
        } else {
            if ($xsell_main_pid <= 0) {
                $messageStack->add_session(ERROR_NO_MAIN_PRODUCT, 'error');
                zen_redirect(zen_href_link(FILENAME_XSELL));
            } else {
                $check = $db->Execute(
                    "SELECT products_id
                       FROM " . TABLE_PRODUCTS . "
                      WHERE products_id = $xsell_main_pid
                      LIMIT 1"
                );
                if ($check->EOF) {
                    $messageStack->add_session(sprintf(ERROR_INVALID_MAIN_PRODUCT, $xsell_main_pid), 'error');
                    zen_redirect(zen_href_link(FILENAME_XSELL));
                }
                $check = $db->Execute(
                    "SELECT *
                       FROM " . TABLE_PRODUCTS_XSELL . "
                      WHERE products_id = $xsell_main_pid
                        AND xsell_id = $xsell_pid
                      LIMIT 1"
                );
                if (!$check->EOF) {
                    $messageStack->add_session(ERROR_CROSS_SELL_EXISTS, 'error');
                    zen_redirect(zen_href_link(FILENAME_XSELL, $page_param . '&action=new_xsell&xsell_main_pid=' . $xsell_main_pid));
                }
            }
            $sql_data_array = [
                'products_id' => $xsell_main_pid,
                'xsell_id' => (int)$xsell_pid,
                'sort_order' => 1
            ];
            zen_db_perform(TABLE_PRODUCTS_XSELL, $sql_data_array);
            $products_name = zen_get_products_name($xsell_main_pid);
            $messageStack->add_session(sprintf(CROSS_SELL_SUCCESS, $products_name, $xsell_main_pid), 'success');
            $main_pid = $xsell_main_pid;
        }
        $next_action = '&action=' . $next_action;
        zen_redirect(zen_href_link(FILENAME_XSELL, $page_param . '&xsell_main_pid=' . $main_pid . $next_action));
        break;

    // -----
    // The admin has requested that multiple products (by model numbers) be added to the current
    // 'main' product, possibly selling those products "both ways".
    //
    case 'multi_xsell':
        // -----
        // There's got to be a main cross-sell product; if not, head back to the main, listing display.
        //
        if ($xsell_main_pid <= 0) {
            $messageStack->add_session(ERROR_NO_MAIN_PRODUCT, 'error');
            zen_redirect(zen_href_link(FILENAME_XSELL));
        }

        // -----
        // Up to six (6) model numbers can be supplied for the multiple cross-sell additions.  They don't
        // have to be supplied 'in-order', so they'll each be checked to see if any were supplied.
        //
        $models = [];
        for ($i = 1; $i <= 6; $i++) {
            if (($_POST['model' . $i] ?? '') !== '') {
                $models[] = $_POST['model' . $i];
            }
        }
        $models = array_unique($models);
        if (count($models) === 0) {
            $messageStack->add(ERROR_NO_MODELS, 'error');
            $action = 'new_xsell';
            break;
        }

        // -----
        // At this point, at least one model number was supplied.  Make sure that each model
        // number is associated with a single, valid product.  If not, kick back for the admin
        // to correct.
        //
        $error = false;
        $products = [];
        foreach ($models as $next_model) {
            $model_products_ids = $db->Execute(
                "SELECT products_id
                   FROM " . TABLE_PRODUCTS . "
                  WHERE products_model = '" . zen_db_input($next_model) . "'"
            );
            switch ($model_products_ids->RecordCount()) {
                case 0:
                    $error = true;
                    $messageStack->add(sprintf(ERROR_MODEL_NO_EXIST, $next_model), 'error');
                    break;
                case 1:
                    $products[] = $model_products_ids->fields['products_id'];
                    break;
                default:
                    $error = true;
                    $messageStack->add(sprintf(ERROR_MODEL_MULTIPLE_PRODUCTS, $next_model), 'error');
                    break;
            }
        }
        if ($error === true) {
            $action = 'new_xsell';
            break;
        }

        // -----
        // Whew!  At least one valid model number has been supplied.  Create the cross-sells
        // for the main product (and optionally the main product to each each model-number specified).
        //
        $selling_both_ways = ($_POST['both_ways'] ?? '') === '1';
        $xsells_inserted = 0;
        foreach ($products as $xsell_products_id) {
            $check = $db->Execute(
                "SELECT *
                   FROM " . TABLE_PRODUCTS_XSELL . "
                  WHERE products_id = $xsell_main_pid
                    AND xsell_id = $xsell_products_id
                  LIMIT 1"
            );
            if ($check->EOF) {
                $xsells_inserted++;
                $db->Execute(
                    "INSERT INTO " . TABLE_PRODUCTS_XSELL . "
                        (products_id, xsell_id, sort_order)
                     VALUES
                        ($xsell_main_pid, $xsell_products_id, 1)"
                );
            }
            if ($selling_both_ways === true) {
                $check = $db->Execute(
                    "SELECT *
                       FROM " . TABLE_PRODUCTS_XSELL . "
                      WHERE products_id = $xsell_products_id
                        AND xsell_id = $xsell_main_pid
                      LIMIT 1"
                );
                if ($check->EOF) {
                    $xsells_inserted++;
                    $db->Execute(
                        "INSERT INTO " . TABLE_PRODUCTS_XSELL . "
                            (products_id, xsell_id, sort_order)
                         VALUES
                            ($xsell_products_id, $xsell_main_pid, 1)"
                    );
                }
            }
        }
        if ($xsells_inserted === 0) {
            $messageStack->add(NO_MULTI_XSELLS_CREATED, 'warning');
            $action = 'new_xsell';
            break;
        }

        $messageStack->add_session(sprintf(MULTI_XSELL_SUCCESS, $xsells_inserted), 'success');
        zen_redirect(zen_href_link(FILENAME_XSELL, $page_param . '&action=new_xsell&xsell_main_pid=' . $xsell_main_pid));
        break;

    // -----
    // The admin has requested a modification to the currently defined cross-sells for a main
    // product, either updating those cross-sells' sort-orders or removing a cross-sell for the
    // current main product.
    //
    // The following $_POST variables are expected:
    //
    // - xsell_main_pid ... The 'main' cross-sell being modified; used to redirect back after processing.
    // - sort ............. An array of sort_orders, keyed by their products_xsell 'ID' values.
    // - del .............. An (optional) array of cross-sells to be removed, keyed by their products_xsell 'ID' values.
    //
    case 'update':
        if (empty($_POST['xsell_main_pid'])) {
            $messageStack->add_session(ERROR_MISSING_MAIN_PRODUCT, 'error');
            zen_redirect(zen_href_link(FILENAME_XSELL));
        }

        if (!empty($_POST['del']) && is_array($_POST['del'])) {
            $db->Execute(
                "DELETE FROM " . TABLE_PRODUCTS_XSELL . "
                  WHERE `ID` IN (" . implode(',', array_keys($_POST['del'])) . ")"
            );
        }

        if (!empty($_POST['sort']) && is_array($_POST['sort'])) {
            foreach ($_POST['sort'] as $xsell_id => $sort_order) {
                $db->Execute(
                    "UPDATE " . TABLE_PRODUCTS_XSELL . "
                        SET sort_order = " . (int)$sort_order . "
                      WHERE `ID` = " . (int)$xsell_id . "
                      LIMIT 1"
                );
            }
        }

        $products_name = zen_get_products_name((int)$_POST['xsell_main_pid']);
        $messageStack->add_session(sprintf(CROSS_SELL_SUCCESS, $products_name, (int)$_POST['xsell_main_pid']), 'success');
        zen_redirect(zen_href_link(FILENAME_XSELL, $page_param . '&action=new_xsell&xsell_main_pid=' . (int)$_POST['xsell_main_pid']));
        break;

    // -----
    // The admin has requested that a 'main' cross-sell and its cross-sell products be removed.
    //
    case 'delete':
        if (!empty($_POST['xsell_main_delete'])) {
            $products_name = zen_get_products_name((int)$_POST['xsell_main_delete']);
            if (!empty($products_name)) {
                $db->Execute(
                    "DELETE FROM " . TABLE_PRODUCTS_XSELL . "
                      WHERE products_id = " . $_POST['xsell_main_delete']
                );
                $messageStack->add_session(sprintf(MAIN_CROSS_SELL_REMOVED, $products_name), 'success');
            }
        }
        zen_redirect(zen_href_link(FILENAME_XSELL, $page_param));
        break;

    // -----
    // Managing cross-sells for a 'main' product.  If there's no main product, something's
    // gone awry; let the admin know and head back to the main page.
    //
    case 'new_xsell':
        if ($xsell_main_pid === 0) {
            $messageStack->add_session(ERROR_MISSING_MAIN_PRODUCT, 'error');
            zen_redirect(zen_href_link(FILENAME_XSELL));
        }
        break;

    default:
        break;
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
    .smaller { font-size: smaller; }
    .mb-3 { margin-bottom: 1rem; }
    </style>
</head>
<body>
<?php require DIR_WS_INCLUDES . 'header.php'; ?>

<div class="container-fluid">
    <h1><?= HEADING_TITLE ?></h1>
<?php
// -----
// Entry for overall display of current cross-sells with the option to create a new cross-sell ...
//
$max_display_search_results = zen_config('MAX_DISPLAY_SEARCH_RESULTS');
if ($action !== 'new_xsell') {
    $xsells_query_raw =
        "SELECT DISTINCT p.products_id, p.products_image, p.products_model, pd.products_name, p.master_categories_id
           FROM " . TABLE_PRODUCTS . " p
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON pd.products_id = p.products_id
                   AND pd.language_id = $languages_id
                INNER JOIN " . TABLE_PRODUCTS_XSELL . " x
                    ON x.products_id = p.products_id
          ORDER BY p.products_id";   

    // Split Page
    // reset page when page is unknown
    if ($currentPage === 1 && !empty($_GET['xsell_main_pid'])) {
        $check_page = $db->Execute($xsells_query_raw);
        if ($check_page->RecordCount() > $max_display_search_results) {
            $check_count = 0;
            foreach ($check_page as $item) {
                if ((int)$item['xsell_main_pid'] === (int)$_GET['xsell_main_pid']) {
                    break;
                }
                $check_count++;
            }
            $currentPage = (int)round((($check_count / $max_display_search_results) + (fmod_round($check_count, $max_display_search_results) !== 0 ? .5 : 0)));
        }
    }

    $xsells_split = new splitPageResults($currentPage, $max_display_search_results, $xsells_query_raw, $xsells_query_numrows);

    $current_xsells = $db->Execute($xsells_query_raw);
    $no_xsells = $current_xsells->EOF;

    $next_action = '';
?>
    <p><?= TEXT_MAIN_INSTRUCTIONS ?></p>
    <h2><?= SUBHEADING_MAIN_ADD ?></h2>
<?php
    require DIR_WS_MODULES . 'xsell/category_product_selection.php';

    echo zen_draw_form('delete', FILENAME_XSELL, zen_get_all_get_params(['action', 'next_action']) . 'action=delete', 'post', 'id="delete-form"');
    echo zen_draw_hidden_field('xsell_main_delete', '', 'id="main_delete"');
?>
    <h2><?= SUBHEADING_MAIN_TITLE ?></h2>


    <table class="table table-striped table-hover">
        <thead>
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_PRODUCT_ID ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_PRODUCT_IMAGE ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_PRODUCT_NAME ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_PRODUCT_MODEL ?></th>
                <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_CURRENT_SELLS ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_ACTION ?></th>
            </tr>
        </thead>
        <tbody>
<?php
    if ($no_xsells === true) {
?>
            <tr class="dataTableRow text-center">
                <td colspan="6" class="dataTableContent"><?= TEXT_NO_CROSS_SELLS ?></td>
            </tr>
<?php
    } else {
        $small_image_width = zen_config('SMALL_IMAGE_WIDTH');
        $small_image_height = zen_config('SMALL_IMAGE_HEIGHT');
        foreach ($current_xsells as $xsell) {
            $current_xsells = $db->Execute(
                "SELECT COUNT(*) AS count
                   FROM " . TABLE_PRODUCTS_XSELL . "
                  WHERE products_id = " . $xsell['products_id']
            );
?>
            <tr class="dataTableRow">
                <td class="dataTableContent text-center"><?= $xsell['products_id'] ?></td>
                <td class="dataTableContent">
                    <?= zen_image(DIR_WS_CATALOG_IMAGES . $xsell['products_image'], $xsell['products_name'], $small_image_width, $small_image_height, 'class="img-thumbnail"') ?>
                </td>
                <td class="dataTableContent xsell-pname"><?= zen_output_string_protected($xsell['products_name']) ?></td>
                <td class="dataTableContent"><?= zen_output_string_protected($xsell['products_model']) ?></td>
                <td class="dataTableContent text-center"><?= $current_xsells->fields['count'] ?></td>
                <td class="dataTableContent">
                    <a href="<?= zen_href_link(FILENAME_XSELL, $page_param . '&action=new_xsell&xsell_main_pid=' . $xsell['products_id']) ?>" role="button" class="btn btn-primary">
                        <?= IMAGE_EDIT ?>
                    </a>
                    <button type="submit" data-pid="<?= $xsell['products_id'] ?>" class="btn btn-danger xsell-main-delete">
                        <?= IMAGE_DELETE ?>
                    </button>
                </td>
            </tr>
<?php
        }
    }
?>
        </tbody>
    </table>
    <?= '</form>' ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $xsells_split->display_count($xsells_query_numrows, $max_display_search_results, $currentPage, TEXT_DISPLAY_NUMBER_OF_PRODUCTS) ?>
        </div>
        <div class="col-sm-6 text-right">
            <?= $xsells_split->display_links($xsells_query_numrows, $max_display_search_results, zen_config('MAX_DISPLAY_PAGE_LINKS'), $currentPage) ?>
        </div>
    </div>
<?php
    
// -----
// Rendering starts to gather information for a new/edited cross-sell product.
//
} else {
    $main_product = zen_get_products_name($xsell_main_pid) . ' [' . $xsell_main_pid . ']';
    $next_action = $action;
?>
    <p class="h3"><?= sprintf(SUBHEADING_NEW_ADD, $main_product) ?></h3>
    <p><?= SUBHEADING_NEW_ADD_INSTR ?></p>
<?php
    $current_xsells = $db->Execute(
        "SELECT x.*
           FROM " . TABLE_PRODUCTS_XSELL . " x
          WHERE x.products_id = $xsell_main_pid
          ORDER BY x.sort_order, x.xsell_id"
    );
    $no_xsells = $current_xsells->EOF;

    // -----
    // Render the form through which a single product can be selected as a cross-sell for the current
    // 'main' product.
    //
    require DIR_WS_MODULES . 'xsell/category_product_selection.php';

    // -----
    // Render the form through which multiple products_model values can be supplied as cross-sells for
    // the current 'main' product.
    //
?>
    <p class="h3"><?= sprintf(SUBHEADING_MULTI_ADD, $main_product) ?></h3>
    <p><?= sprintf(SUBHEADING_MULTI_ADD_INSTR, '<em>' . TEXT_BOTH_WAYS . '</em>') ?></p>
    <?= zen_draw_form('multi', FILENAME_XSELL, 'action=multi_xsell', 'post', 'class="form-horizontal"') ?>
    <?= zen_draw_hidden_field('xsell_main_pid', $xsell_main_pid) ?>
    <?= zen_draw_hidden_field('page', $currentPage) ?>
    <div class="row mb-3">
<?php
    for ($i = 1; $i <= 6; $i++) {
        $model_field_name = 'model' . $i;
        $model_default = zen_output_string_protected($_POST[$model_field_name] ?? '');
?>
        <div class="col-sm-4 col-md-2"><?= zen_draw_input_field($model_field_name, $model_default, 'class="form-control"') ?></div>
<?php
    }
?>
    </div>
    <div class="row">
        <div class="col-sm-6 text-right">
            <?= zen_draw_label(TEXT_BOTH_WAYS, 'both-ways', 'class="control-label"') . '&nbsp;&nbsp;' . zen_draw_checkbox_field('both_ways', '1', false, '', 'id="both-ways"') ?>
        </div>
        <div class="col-sm-6 text-left">
            <button type="submit" class="btn btn-info"><?= TEXT_BUTTON_ADD ?></button>
        </div>
    </div>
    <?= '</form>' ?>
<?php

    // -----
    // Render the form through which current cross-sells for the selected 'main' product can be removed
    // or their sort-orders updated.
    //
    echo zen_draw_form('update', FILENAME_XSELL, zen_get_all_get_params(['action', 'next_action']) . 'action=update', 'post');
    echo zen_draw_hidden_field('xsell_main_pid', $xsell_main_pid) . zen_draw_hidden_field('page', $currentPage);
?>
    <p class="h3"><?= sprintf(SUBHEADING_MANAGE_EXISTING, $main_product) ?></h3>
    <p><?= SUBHEADING_MANAGE_EXISTING_INSTR ?></p>
    <table class="table table-striped table-hover">
        <thead>
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_PRODUCT_ID ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_PRODUCT_IMAGE ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_PRODUCT_NAME ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_PRODUCT_MODEL ?></th>
                <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_PRODUCT_SORT ?></th>
                <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_REMOVE ?></th>
            </tr>
        </thead>
        <tbody>
<?php
    if ($no_xsells === true) {
?>
            <tr class="dataTableRow text-center">
                <td colspan="6" class="dataTableContent"><?= TEXT_NO_CROSS_SELL_PRODUCTS ?></td>
            </tr>
<?php
    } else {
        $small_image_width = zen_config('SMALL_IMAGE_WIDTH');
        $small_image_height = zen_config('SMALL_IMAGE_HEIGHT');
        foreach ($current_xsells as $xsell) {
            $xsell_id = $xsell['xsell_id'];
            $products_name = zen_get_products_name($xsell_id);
?>
            <tr class="dataTableRow">
                <td class="dataTableContent text-center"><?= $xsell['xsell_id'] ?></td>
                <td class="dataTableContent">
                    <?= zen_image(DIR_WS_CATALOG_IMAGES . zen_get_products_image($xsell_id), $products_name, $small_image_width, $small_image_height, 'class="img-thumbnail"') ?>
                </td>
                <td class="dataTableContent"><?= zen_output_string_protected($products_name) ?></td>
                <td class="dataTableContent"><?= zen_output_string_protected(zen_get_products_model($xsell_id)) ?></td>
                <td class="dataTableContent text-center"><?= zen_draw_input_field('sort[' . $xsell['ID'] . ']', $xsell['sort_order'], 'class="form-control text-right" size="4"') ?></td>
                <td class="dataTableContent text-center"><?= zen_draw_checkbox_field('del[' . $xsell['ID'] . ']', '1', false) ?></td>
            </tr>
<?php
        }
    }
?>
        </tbody>
    </table>

    <div class="row">
        <div class="col-md-6">
            <a href="<?= zen_href_link(FILENAME_XSELL, $page_param) ?>" role="button" class="btn btn-default"><?= IMAGE_BACK ?></a>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-info" type="submit"><?= IMAGE_UPDATE ?></button>
        </div>
    </div>
<?php
    echo '</form>';
}
?>
</div>
<!-- body_eof //-->
<!-- footer //-->
<div class="footer-area"><?php require DIR_WS_INCLUDES . 'footer.php'; ?></div>
<!-- footer_eof //-->
</body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
