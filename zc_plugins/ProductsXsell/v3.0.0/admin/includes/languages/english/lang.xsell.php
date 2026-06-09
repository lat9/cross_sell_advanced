<?php
/**
 * Cross Sell Products
 *
 * Last updated: v3.0.0
 *
 * Derived from:
 * Original Idea From Isaac Mualem im@imwebdesigning.com
 * Portions Copyright (c) 2002 osCommerce
 * Complete Recoding From Stephen Walker admin@snjcomputers.com
 * Released under the GNU General Public License
 *
 * Adapted to Zen Cart by Merlin - Spring 2005
 * Reworked for Zen Cart v1.3.0  03-30-2006
 * Reworked for Zen Cart 1.5.7+, lat9, December 2021
 * Reworked for Zen Cart 1.5.8+, lat9, May 2026
 */
$define = [
    'CROSS_SELL_SUCCESS' => 'Cross-sell items successfully updated for <em>%1$s [%2$u]</em>.',    //-%1$s (product's name), %2%u (product's id).
    'HEADING_TITLE' => 'Cross-Sell Advanced II',

    'ERROR_CROSS_SELL_EXISTS' => 'The requested product is already a cross-sell for the selected main product.',
    'ERROR_INVALID_MAIN_PRODUCT' => 'Invalid main product (%u), no cross-sell can be defined.',
    'ERROR_MISSING_MAIN_PRODUCT' => 'Missing main product to create/update cross-sells; returning to main display.',
    'ERROR_MODEL_NO_EXIST' => 'Model number (%s) does not exist; the multiple cross-sell request was not performed',
    'ERROR_MODEL_MULTIPLE_PRODUCTS' => 'Model number (%s) is associated with multiple products; the multiple cross-sell request was not performed.',
    'ERROR_NO_MAIN_PRODUCT' => 'No main product selected; no cross-sell can be defined.',
    'ERROR_NO_MODELS' => 'At least one model-number must be supplied for multiple cross-sells to be added.',

    'LABEL_CHOOSE_CATEGORY' => 'Choose category:',
    'LABEL_CHOOSE_XSELL_PRODUCT' => 'Choose cross-sell product:',
    'MAIN_CROSS_SELL_REMOVED' => 'All cross-sell items successfully removed for <em>%s</em>.',  //-%s (product's name)
    'MULTI_XSELL_SUCCESS' => '%u cross-sells were successfully added.',
    'NO_MULTI_XSELLS_CREATED' => 'All products are already cross-sold!',

    'SUBHEADING_MAIN_ADD' => 'Create New Cross-Sell Product',
    'SUBHEADING_MAIN_TITLE' => 'Viewing Current Products with Cross-sells',
    'SUBHEADING_MANAGE_EXISTING' => 'Manage Existing Cross-Sells for %s', //-%s is filled in with the main product's name and ID
    'SUBHEADING_MANAGE_EXISTING_INSTR' => 'Change the sort-orders and/or remove existing cross-sells from the current product. Click the <em>Update</em> button to apply any changes.',
    'SUBHEADING_MULTI_ADD' => 'Add Multiple Cross-Sells for %s', //-%s is filled in with the main product's name and ID
    'SUBHEADING_MULTI_ADD_INSTR' => 'Enter the model numbers for products to add to the current product\'s cross-sells. Tick the %1$s box to also add the current product as a cross-sell to the products chosen. Click the <em>Add</em> button to add these cross-sells.',
    'SUBHEADING_NEW_ADD' => 'Add a Single Cross-Sell for %s', //-%s is filled in with the main product's name and ID
    'SUBHEADING_NEW_ADD_INSTR' => 'Choose a category and click <em>Go</em>, then a cross-sell product and click <em>Add</em>.',

    'TABLE_HEADING_ACTION' => 'Action',
    'TABLE_HEADING_CURRENT_SELLS' => 'Current Cross-Sells',
    'TABLE_HEADING_PRODUCT_ID' => 'Product Id',
    'TABLE_HEADING_PRODUCT_IMAGE' => 'Product Image',
    'TABLE_HEADING_PRODUCT_MODEL' => 'Product Model',
    'TABLE_HEADING_PRODUCT_NAME' => 'Product Name',
    'TABLE_HEADING_PRODUCT_PRICE' => 'Product Price',
    'TABLE_HEADING_PRODUCT_SORT' => 'Sort Order',
    'TABLE_HEADING_REMOVE' => 'Remove?',

    'TEXT_BOTH_WAYS' => 'Cross-sell Both Ways?',
    'TEXT_BUTTON_ADD' => 'Add',

    'TEXT_JS_MAIN_DELETE_CONFIRM' => 'Are you sure you want to remove all cross-sell(s) for the above product?',
    'TEXT_MAIN_INSTRUCTIONS' => 'Use the forms below to either create a new cross-sell product or to manage existing cross-sells.',
    'TEXT_NO_CROSS_SELL_PRODUCTS' => 'No cross-sells have been defined for the selected product.',
    'TEXT_NO_CROSS_SELLS' => 'No cross-sell products have been defined.',
];
return $define;
