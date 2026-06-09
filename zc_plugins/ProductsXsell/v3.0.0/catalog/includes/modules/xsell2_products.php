<?php
/**
 * Cross Sell Advanced
 *
 * Last updated: v2.1.0
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
 */
// collect information on available cross-sell products for the current product-id
$xsell_max_display = (int)zen_config('MAX_DISPLAY_XSELL');
$xsell_columns = (int)zen_config('SHOW_PRODUCT_INFO_COLUMNS_XSELL_PRODUCTS');
if ($xsell_columns >= 0 && $xsell_max_display > 0 && isset($_GET['products_id'])) {
    // -----
    // Sanitize xsell-related configuration settings.
    //
    $small_image_width = zen_config('SMALL_IMAGE_WIDTH');
    $small_image_height = zen_config('SMALL_IMAGE_HEIGHT');
    $xsell_display_price = zen_config('XSELL_DISPLAY_PRICE') === 'true';

    $xsell_query_sql = 
        "SELECT p.products_id, p.products_image, pd.products_name
           FROM " . TABLE_PRODUCTS_XSELL . " xp
                INNER JOIN " . TABLE_PRODUCTS . " p
                    ON p.products_id = xp.xsell_id
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON pd.products_id = p.products_id
                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
          WHERE xp.products_id = " . (int)$_GET['products_id'] . "
            AND p.products_status = 1
          ORDER BY xp.sort_order, pd.products_name ASC";

    $xsell_query = $db->ExecuteRandomMulti($xsell_query_sql, $xsell_max_display); 
    $num_products_xsell = (int)$xsell_query->RecordCount();

    if ($num_products_xsell === 0) {
        return;
    }

    $row = 0;
    $col = 0;
    $list_box_contents = [];
    while (!$xsell_query->EOF) {
        if ($col === 0) {
            $list_box_contents[$row]['params'] = 'class="centerBoxContentsAlsoPurch xsell-centerbox centeredContent d-flex justify-content-around"';
        }
        $next_xsell = $xsell_query->fields;
        $xsell_image = zen_image(DIR_WS_IMAGES . $next_xsell['products_image'], $next_xsell['products_name'], $small_image_width, $small_image_height); 

        $xsell_query_text =
            '<a href="' . zen_href_link(zen_get_info_page($next_xsell['products_id']), 'products_id=' . $next_xsell['products_id']) . '">' .
                $xsell_image .
            '</a>' .
            '<br>' .
            '<a href="' . zen_href_link(zen_get_info_page($next_xsell['products_id']), 'products_id=' . $next_xsell['products_id']) . '">' .
                zen_output_string_protected($next_xsell['products_name']) .
            '</a>';
        if ($xsell_display_price === true) {
            $xsell_query_text .= '<br>' . zen_get_products_display_price($next_xsell['products_id']);
        }

        $list_box_contents[$row][$col] = [
            'params' => 'class="xsell-centerbox"',
            'text' => $xsell_query_text
        ]; 

        $col++;
        if ($col > ($xsell_columns - 1)) {
            $col = 0;
            $row++;
        }
        $xsell_query->MoveNextRandom();
    }
}
