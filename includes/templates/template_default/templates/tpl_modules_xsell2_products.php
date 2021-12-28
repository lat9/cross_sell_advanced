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
 * Reworked for Zen Cart v1.5.7+, lat9, December 2021
 */
// calculate whether any cross-sell products are configured for the current product, and display if relevant
require DIR_WS_MODULES . zen_get_module_directory(FILENAME_XSELL);

if (!empty($xsell_data)) {
    $list_box_contents = $xsell_data;
    $title = '<h2 class="centerBoxHeading">' . TEXT_XSELL_PRODUCTS . '</h2>';
?>
<div class="centerBoxWrapper" id="crossSell">
<?php
/**
 * require the list_box_content template to display the cross-sell info. This info was prepared in modules/xsell_products.php
 */
require $template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php';
?>
</div>
<?php
}
