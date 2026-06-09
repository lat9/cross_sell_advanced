<?php
/**
 * Cross Sell Advanced II
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
 * Reworked for Zen Cart v1.5.7+, lat9, December 2021
 */
$override_file = DIR_WS_MODULES . zen_get_module_directory(FILENAME_XSELL2_PRODUCTS);
if (is_file($override_file)) {
    require $override_file;
} else {
    require $xsell_plugin_dir . 'catalog/' . DIR_WS_MODULES . FILENAME_XSELL2_PRODUCTS;
}
if (empty($list_box_contents)) {
    return;
}

$title = '<h2 class="centerBoxHeading card-header h3">' . TEXT_XSELL_PRODUCTS . '</h2>';
?>
<div class="centerBoxWrapper" id="crossSell">
<?php
/**
 * require the list_box_content template to display the cross-sell info. This info was prepared in modules/xsell2_products.php
 */
require $template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php';
?>
</div>
