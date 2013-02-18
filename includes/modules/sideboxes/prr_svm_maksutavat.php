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

if (CFG_PRR_SVM_SELLER_ID != '' && CFG_PRR_SVM_PASSWORD != '') {
	$chk = substr(md5(CFG_PRR_SVM_SELLER_ID . CFG_PRR_SVM_PASSWORD),0,16);
	$usr = CFG_PRR_SVM_SELLER_ID;
	$prr_svm_banner = 'https://img.verkkomaksut.fi/index.svm?id=' . $usr . '&type=vertical&cols=5&text=1&auth=' . $chk;
}

require($template->get_template_dir('tpl_prr_svm_maksutavat.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_prr_svm_maksutavat.php');

if (true) {
	$title =  '';
	$title_link = false;
	require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
}
