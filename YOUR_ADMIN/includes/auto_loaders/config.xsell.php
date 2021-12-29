<?php
/**
 * Part of the "Cross Sell Advanced II" plugin for Zen Cart 1.5.7+
 *
 * @copyright 2013 C Jones
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: config.xsell.php 1.3 01/20/2014 C Jones
 *
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
} 

$autoLoadConfig[999][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_xsell_config.php'
];

$autoLoadConfig[999][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/XsellAdminObserver.php',
    'classPath' => DIR_WS_CLASSES
];
$autoLoadConfig[999][] = [
    'autoType' => 'classInstantiate',
    'className' => 'XsellAdminObserver',
    'objectName' => 'xsello'
];
