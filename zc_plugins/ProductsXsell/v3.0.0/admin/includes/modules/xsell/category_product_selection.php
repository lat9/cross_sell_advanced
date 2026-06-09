<?php
// -----
// Part of the "Cross Sell Advanced II" plugin for Zen Carts 1.5.8 and later.
//
// Common 'rendering' of a product selection, uses variables from the main /admin/xsell.php:
//
// - $current_xsells ...... The result of a $db query that gathers the current matching cross-sells (either main or per-product).
// - $xsell_category_id ... The currently-selected category id for a new product/cross-sell.
// - $xsell_main_pid ...... The currently-selected 'main' product to be cross-sold.
// - $xsell_pid ........... The currently-selected product from the products' dropdown.
// - $next_action ......... The 'next' action to perform after a category has been selected.
// - $currentPage .......... The current main 'page' to be displayed.
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
?>
<div class="row">
    <div class="col-sm-6">
        <?= zen_draw_form('new_category', FILENAME_XSELL, $next_action, 'get', 'class="form-horizontal"') ?>
            <?= zen_hide_session_id() .
                zen_draw_hidden_field('action', 'new_cat') .
                zen_draw_hidden_field('page', $currentPage) .
                zen_draw_hidden_field('next_action', $next_action) ?>
<?php
if (!empty($xsell_main_pid)) {
    echo zen_draw_hidden_field('xsell_main_pid', $xsell_main_pid);
}
?>
            <div class="form-group">
                <?= zen_draw_label(LABEL_CHOOSE_CATEGORY, 'xs-cat-id', 'class="col-sm-6 col-md-4 control-label"') ?>
                <div class="col-sm-6 col-md-8">
                    <div class="input-group">
                        <?= zen_draw_pull_down_menu('xsell_category_id', zen_get_category_tree('', '', '0', '', '', true), $xsell_category_id, 'id="xs-cat-id" class="form-control"') ?>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-info"><?= IMAGE_GO ?></button>
                        </span>
                    </div>
                </div>
            </div>
        <?= '</form>' ?>
    </div>
<?php
// -----
// If a product has not been selected for cross-sells, display the list of potential
// cross-sell products.
//
if ($xsell_category_id !== 0 && $xsell_pid === 0) {
?>
    <div class="col-sm-6">
        <?= zen_draw_form('set_xsell_pid', FILENAME_XSELL, 'action=set_xsell_pid', 'post', 'class="form-horizontal"') ?>
            <?= zen_draw_hidden_field('xsell_category_id', $xsell_category_id) .
                zen_draw_hidden_field('page', $currentPage) .
                zen_draw_hidden_field('next_action', $next_action) ?>
<?php
    $excluded_products = [];
    if ($xsell_main_pid !== 0) {
        $excluded_products[] = $xsell_main_pid;
        echo zen_draw_hidden_field('xsell_main_pid', $xsell_main_pid);
    }

    foreach ($current_xsells as $xsell) {
        $excluded_products[] = $xsell['products_id'];
        if (isset($xsell['xsell_id'])) {
            $excluded_products[] = $xsell['xsell_id'];
        }
    }
    $excluded_products = array_unique($excluded_products);

    // -----
    // Set the $current_category_id to reflect the currently-selected xsell category.
    //
    $current_category_id = $xsell_category_id;
?>
            <div class="form-group">
                <?= zen_draw_label(LABEL_CHOOSE_XSELL_PRODUCT, 'xsell_pid', 'class="col-sm-6 col-md-3 control-label"') ?>
                <div class="col-sm-6 col-md-9">
                    <div class="input-group">
                        <?= zen_draw_pulldown_products('xsell_pid', 'class="form-control" id="xsell_pid"', $excluded_products, true, $xsell_pid, true, true, true) ?>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-info"><?= TEXT_BUTTON_ADD ?></button>
                        </span>
                    </div>
                </div>
            </div>
        <?= '</form>' ?>
    </div>
<?php
}
?>
</div>
