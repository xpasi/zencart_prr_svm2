<?php

/*
 prr_svm - Suomen verkkomaksut moduli ZenCart Verkkokauppaan
 @copyright Copyright 2013 Projekti Rajala <p@prr.fi>
 @copyright Copyright 2003-2013 Zen Cart Development Team
 @copyright Portions Copyright 2003 osCommerce

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

// Käytä tätä luokkaa maksutavan tietojen hakemiseen.
// Käyttö:
//
// require_once(DIR_FS_CATALOG . '/' . DIR_WS_CLASSES . 'prr_maksutapa.php');
// $maksutapa = new maksutapa;
// $maksutapa->order($tilausnumero)->method (Palauttaa maksutavan nimen)
// $maksutapa->order($tilausnumero)->reference (Palauttaa viitenumeron)

if (!defined('TABLE_SUOMENPANKIT')) {
  define('TABLE_SUOMENPANKIT', DB_PREFIX . 'prr_suomenpankit');
}

class prr_maksutapa {

	public $order_id, $method, $reference, $hasEntry;

	public function __construct() {
		
	}

	public function order($oid) {
		global $db;
		if ($this->order_id != $oid) {
			$this->hasEntry = false;
			$this->method = null;
			$this->reference = null;
			$this->order_id = $oid;
			$data = $db->execute('SELECT * FROM `' . TABLE_SUOMENPANKIT . '` WHERE `orders_id` = ' . (int) $oid);
			if ($data->fields['referid'] != '') {
				$this->hasEntry = true;
				$this->method = $data->fields['method'];
				$this->reference = $data->fields['referid'];
			}
		}
		return $this;
	}

}