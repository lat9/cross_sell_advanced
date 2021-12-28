# Adding the Cross-Sell Block to Your Template

Part of the plugin's installation requires some 'hand-editing' of various `tpl_xxx_info_display.php` files:

1. tpl_document_general_info_display.php
2. tpl_document_product_info_display.php
3. tpl_product_free_shipping_info_display.php
4. tpl_product_info_display.php
5. tpl_product_music_info_display.php

For each product type:

1. If that file exists in your active template's `/templates` sub-directory, make a backup copy of that file.  Otherwise, copy the file from `/includes/templates/template_default/templates` to your active template's `/templates` sub-directory.

2. Find the following section:

   ```php
   <!--eof Product URL -->
   
   <!--bof also purchased products module-->
   ```

3. ... and update to include the cross-sell information for a viewed product:

   ```php
   <!--eof Product URL -->
   <?php
   //-bof-advanced_cross_sell_ii  *** 1 of 1 ***
   include $template->get_template_dir('tpl_modules_xsell2_products.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_xsell2_products.php';
   //-eof-advanced_cross_sell_ii  *** 1 of 1 ***
   ?>
   <!--bof also purchased products module-->
   ```
4. Save the file and you're good-to-go.