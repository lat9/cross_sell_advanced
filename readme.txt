Cross Sell Advanced v1.5
Based on the original Cross Sell add-on:
http://www.zen-cart.com/downloads.php?do=file&id=2 - Cross Sell by Merlin/DrByte
Released for Zen Cart v 1.5.5

This installation is considered a intermediate level Zen Cart module installation.

Based on the original Cross Sell module! It is easy to use, has a graphical interface to quickly find 
products to cross sell and is very light weight. 6 cross sell products can set specifically set for 
each of your Zen Cart product pages.

Cross Sell Advanced expands the original Cross Sell for even more ease of use by allowing you to setup 
6 cross sells using the product model numbers.

Support thread: http://www.zen-cart.com/showthread.php?211884-Cross-Sell-Advanced-Support-Thread


INSTALLATION INSTRUCTIONS
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

1. FIRST MAKE A FULL BACKUP OF YOUR WEBSITE'S FILES AND DATABASE!


2. Rename the /YOUR_ADMIN/ directory to match your own admin directory folder name. 


3. Rename the /YOUR_TEMPLATE/ directory to match your own template's directory folder name. 


4. Log into your store's administration area. 


5. Upload the files to your store.


6. Click ANY LINK in the admin to trigger the installation of the add-on. You will see a success message letting you know the install is complete. 

You can still sort cross sell offers using the original Cross Sell 
admin menu under Catalog->Cross Sell (X-Sell) Admin. Alternately,
if you wish, you may use Common Cross Sell Sort Order to set
the sort order once per product rather than once per offer.

You can get Common Cross Sell Sort Order here: 
http://www.zen-cart.com/downloads.php?do=file&id=2102


OBSOLETE FILES
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
The following files are obsolete and can be safely removed:

~ YOUR_ADMIN/includes/extra_datafiles/xsell_advanced_definitions.php
~ YOUR_ADMIN/includes/functions/extra_functions/cross_sell_plus_advanced_page_registration.php
~ YOUR_ADMIN/includes/languages/english/extra_definitions/advanced_xsell_defs.php
~ YOUR_ADMINincludes/languages/english/extra_definitions/x-sell_defs.php

Each of these files now only contains the text: "This file is obsolete in Cross Sell Advance v1.3. You can remove this file from your store!" 

This way if you forget to remove them they will not execute any obsolete code.


INFORMATION
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Core file Edits: None
Template Override Changes: Yes
Database Changes: Yes, adds additional table products_xsell to store the cross sells


UNINSTALL
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Remove the package files and file edits in overrides. Removal of the database 
entry is not required to remove this module from use.


USING CROSS SELL
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Adding Cross-Sell details:
  Standard Cross Sell Catalog->Cross Sell Products 
     -Pick a product to add products to. Hit edit, add products. Hit Save.
  Advanced Cross Sell Catalog->Advanced Cross-Sell
     - Fill in Product Model and the Product Models you wish to offer, save.

Configuring how many Cross-Sell items are displayed:
  Admin -> Configuration -> Cross-Sell (X-Sell) Configuration -> Display Cross-Sell Products (Enter the Min number of items required to display list)
  Admin -> Configuration -> Cross-Sell (X-Sell) Configuration -> Display Cross-Sell Products (Enter the Max number of cross-sell items to show. 0 to disable site-wide)
  Admin -> Configuration -> Cross-Sell (X-Sell) Configuration -> Cross-Sell Products Columns per Row (Enter the number of Cross-Sell items to show per row)
  Admin -> Configuration -> Cross-Sell (X-Sell) Configuration -> Cross-Sell - Display Prices (Select whether to display prices in the list of cross-sell products)


SUPPORT
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Please use this thread for support
http://www.zen-cart.com/forum/showthread.php?t=193123


VERSION HISTORY
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

1.0 Voltage (http://www.zen-cart.com/member.php?u=10635)
    Initial release Cross Sell Release

1.0 Zen Cart 1.5.0 combo installation creation by PRO-Webs 2-26-2012  (Submitted as Cross Sell Plus Advanced Sell Combo - http://www.zen-cart.com/downloads.php?do=file&id=1364)

1.1 PRO-Webs (http://pro-webs.net) 2-29-2012 (Submitted as Cross Sell Plus Advanced Sell Combo - http://www.zen-cart.com/downloads.php?do=file&id=1364)
    Update SQL upgrade remove, syntax issue
    Resolved misnamed admin_delete.gif
    Fixed language error
    Fixed confusing instructions
    Some housecleaning as well

1.2 RodG Dec 2013
    Updated for Zen Cart v 1.5.2
    Incorporate the original Cross Sell module (also updated) so you don't need to install that first/separately. 
    
1.3 C Jones (http://overthehillweb.com)
    File cleanup (eliminated unecessary files consolidated others)
    Fixed the product image display on the xsell.php file (was displaying the full image size and NOT the small image size)
    Fixed typo in the xsell.php file which prevented list of cross-sell items from displaying correctly in the admin
    Fixed edit and delete buttons on the xsell_advanced.php file
    Added auto installer to simplify installation
    Added support for all default Zen Cart product types
    Cleanup read me (removed outdated instructions)

1.4 jeking (https://www.wheatonwebsiteservices.com)
	Updated for Zen Cart 1.5.5
1.5 swguy (http://www.thatsoftwareguy.com) 
	Added common sort order support
        See https://www.zen-cart.com/downloads.php?do=file&id=2102
