<?php 
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   example
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: index.php 5185 2013-01-19 15:34:13Z hellojixian@gmail.com $
 */

//include openbiz initail script
require_once dirname(dirname(__FILE__)).'/bin/app_init.php';
 
//output a Openbiz Framework version
echo "Openbiz Framework Version: ".BizSystem::getVersion();
?>