<?php
// -----
// Part of the "Cross Sell Advanced II" plugin for Zen Carts 1.5.8 and later.
//
// Last updated: v3.0.0
//
use Zencart\Traits\InteractsWithPlugins;

class zcObserverProductsXsell extends base
{
    use Zencart\Traits\InteractsWithPlugins;

    public function __construct()
    {
        $this->attach(
            $this,
            [
                'NOTIFY_FOOTER_END',
            ]
        );
    }

    // -----
    // Adds jQuery script to insert the cross-sell items for the current product
    // as a block in the product's details page, e.g. product_info or product_music_info.
    //
    protected function notify_footer_end(&$class, string $e, string $current_page): void
    {
        // -----
        // If the current page isn't to display the product's details or the centerbox
        // display is disabled via configuration, nothing further to do here.
        //
        if (!str_ends_with($current_page, '_info') || (int)zen_config('MAX_DISPLAY_XSELL') === 0) {
            return;
        }

        // -----
        // Use the base trait to determine this plugin's directory location.
        //
        $this->detectZcPluginDetails(__DIR__);
        $xsell_plugin_dir = $this->pluginManagerInstalledVersionDirectory;

        global $template, $db, $current_page_base, $zco_notifier;
        ob_start();
        require $template->get_template_dir('/tpl_modules_xsell2_products.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_xsell2_products.php';
        $xsell_centerbox = ob_get_clean();
        if ($xsell_centerbox === false) {
            return;
        }
?>
<style id="products-xsell-css">
.d-flex {display: flex;}
</style>
<script id="products-xsell">
$(function() {
    $('form[name="cart_quantity"]').append(<?= json_encode($xsell_centerbox) ?>);
});
</script>
<?php
    }
}
