<?php 
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   sample
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: sample.php 4932 2012-12-26 15:42:10Z hellojixian@gmail.com $
 */

//include openbiz initail script
require_once dirname(dirname(__FILE__)).'/bin/app_init.php';

$objectName = "system.view.UserListView";

//get the view object instance
$userView = BizSystem::getViewObject($objectName);	

//render the view to a html string
$viewHTML = $userView->render();	

//output the html string				
echo $viewHTML;										
?>