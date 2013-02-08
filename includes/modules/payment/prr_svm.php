<?php
/*
 prr_svm - Suomen verkkomaksut moduli ZenCart Verkkokauppaan
 @copyright Copyright 2013 Projekti Rajala <p@prr.fi>

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

require_once((IS_ADMIN_FLAG === true ? DIR_FS_CATALOG_MODULES : DIR_WS_MODULES) . '/payment/prr_suomenverkkomaksut/Verkkomaksut_Module_Rest.php');

if (!defined('TABLE_SUOMENPANKIT')) {
  define('TABLE_SUOMENPANKIT', DB_PREFIX . 'prr_suomenpankit');
}

require_once('prr_suomenverkkomaksut/init.php');