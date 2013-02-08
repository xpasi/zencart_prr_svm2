<?php

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
