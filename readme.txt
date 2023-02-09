Cross Sell Advanced v2.0.2
Supports Zen Cart 1.5.7 and 1.5.8

Current Zen Cart download: https://www.zen-cart.com/downloads.php?do=file&id=2334
Support thread: https://www.zen-cart.com/showthread.php?211884-Cross-Sell-Advanced-Support-Thread

----------------

Based on the original Cross Sell add-on:
https://www.zen-cart.com/downloads.php?do=file&id=2 - Cross Sell by Merlin/DrByte
Released for Zen Cart v 1.5.5

----------------

This installation is considered a intermediate level Zen Cart module installation.

Based on the original Cross Sell module! It is easy to use, has a graphical interface to quickly find 
products to cross sell and is very light weight. 6 cross sell products can set specifically set for 
each of your Zen Cart product pages.

Cross Sell Advanced expands the original Cross Sell for even more ease of use by allowing you to setup 
6 cross sells using the product model numbers.


INSTALLATION INSTRUCTIONS
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

1. FIRST MAKE A FULL BACKUP OF YOUR WEBSITE'S FILES AND DATABASE!

2. Rename the /YOUR_ADMIN/ directory to match your own admin directory folder name. 

3. Rename the /YOUR_TEMPLATE/ directory to match your own template's directory folder name. 

4. Log into your store's administration area. 

5. Upload the files to your store.

6. Click ANY LINK in the admin to trigger the installation of the add-on. You will see a success message letting you know the install is complete. 


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

The Cross Sell Advanced II documentation is available on-line:

1) Admin Interfaces:   https://github.com/lat9/cross_sell_advanced/blob/main/pages/admin_interfaces.md
2) Storefront Changes: https://github.com/lat9/cross_sell_advanced/blob/main/pages/storefront_interfaces.md


VERSION HISTORY
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

2.0.2 lat9, swguy 2023-02-09
    - Corrects integration with Zen Cart 1.5.8, now supported.

2.0.1 lat9 2022-01-10
    - This release corrects (see GitHub issue #5) the MySQL error received on a 'fresh' install
      and also corrects the plugin's uninstall SQL script.

2.0.0 lat9 2022-01-01
    - Requires Zen Cart 1.5.7 (fully-patched) or later.
    - Is updated to support now-current versions of PHP and strict MySQL installations.
    - Combines the processing of the previous version's two admin tools into one.
    - Includes a Database I/O Manager (DbIo) handler, enabling import/export of the cross-sells via a .csv file.
    - No more auto-deleting admin installation!
    - Removes associated cross-sells when a product is removed.
    - Includes integration with the ZCA Bootstrap template.

1.5 swguy (http://www.thatsoftwareguy.com) 
    Added common sort order support
        See https://www.zen-cart.com/downloads.php?do=file&id=2102

1.4 jeking (https://www.wheatonwebsiteservices.com)
    Updated for Zen Cart 1.5.5

1.3 C Jones (http://overthehillweb.com)
    File cleanup (eliminated unecessary files consolidated others)
    Fixed the product image display on the xsell.php file (was displaying the full image size and NOT the small image size)
    Fixed typo in the xsell.php file which prevented list of cross-sell items from displaying correctly in the admin
    Fixed edit and delete buttons on the xsell_advanced.php file
    Added auto installer to simplify installation
    Added support for all default Zen Cart product types
    Cleanup read me (removed outdated instructions)

1.2 RodG Dec 2013
    Updated for Zen Cart v 1.5.2
    Incorporate the original Cross Sell module (also updated) so you don't need to install that first/separately. 
    
1.1 PRO-Webs (http://pro-webs.net) 2-29-2012 (Submitted as Cross Sell Plus Advanced Sell Combo - http://www.zen-cart.com/downloads.php?do=file&id=1364)
    Update SQL upgrade remove, syntax issue
    Resolved misnamed admin_delete.gif
    Fixed language error
    Fixed confusing instructions
    Some housecleaning as well
    
1.0 Zen Cart 1.5.0 combo installation creation by PRO-Webs 2-26-2012  (Submitted as Cross Sell Plus Advanced Sell Combo - http://www.zen-cart.com/downloads.php?do=file&id=1364)

1.0 Voltage (http://www.zen-cart.com/member.php?u=10635)
    Initial release Cross Sell Release