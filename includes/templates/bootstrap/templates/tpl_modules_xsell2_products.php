<?php
/**
 * Cross Sell Advanced II, integration for the Bootstrap 4 template.
 *
 * Derived from:
 * Original Idea From Isaac Mualem im@imwebdesigning.com
 * Portions Copyright (c) 2002 osCommerce
 * Complete Recoding From Stephen Walker admin@snjcomputers.com
 * Released under the GNU General Public License
 *
 * Adapted to Zen Cart by Merlin - Spring 2005
 * Reworked for Zen Cart v1.3.0  03-30-2006
 * Reworked for Zen Cart v1.5.7+, lat9, January 2022
 */
// calculate whether any cross-sell products are configured for the current product, and display if relevant
require DIR_WS_MODULES . zen_get_module_directory(FILENAME_XSELL);

if (!empty($xsell_data)) {
    // -----
    // Going a bit 'sneaky' here, going back through the cross-sells determined by the base processing and
    // updating each entry's parameters so that each displays as a Bootstrap card.
    //
    for ($row = 0, $max_row = count($xsell_data); $row < $max_row; $row++) {
        for ($col = 0, $max_col = count($xsell_data[$row]); $col < $max_col; $col++) {
            $xsell_data[$row][$col]['params'] = 'class="centerBoxContents card mb-3 p-3 text-center"';
        }
    }

    // -----
    // Record the updated data for the common tabular display.
    //
    $list_box_contents = $xsell_data;
    $title = '<h4 id="xsellCenterbox-card-header" class="centerBoxHeading card-header">' . TEXT_XSELL_PRODUCTS . '</h4>';
?>
<div class="centerBoxWrapper" id="crossSell">
    <?php require $template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php'; ?>
</div>
<?php
}
