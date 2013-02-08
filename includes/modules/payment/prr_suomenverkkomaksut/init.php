<?php

class prr_svm {
	var $code, $title, $description, $enabled, $_check, $sort_order, $config;

	// class constructor
	function prr_svm() {
		global $order;

		$this->code = 'prr_svm';
		$this->title = TEXT_PRR_SVM_PAYMENT_NAME;
		$this->description = TEXT_PRR_SVM_PAYMENT_DESCRIPTION;
		$this->sort_order = CFG_PRR_SVM_SORT_ORDER;
		$this->enabled = ((CFG_PRR_SVM_STATUS == 'On') ? true : false);

		if ((int)CFG_PRR_SVM_ORDER_STATUS_ID > 0) {
			$this->order_status = CFG_PRR_SVM_ORDER_STATUS_ID;
		}

		if (is_object($order)) $this->update_status();
	}

	// class methods
	function update_status() {
		global $order, $db;
		if (($this->enabled == true) && ((int)CFG_PRR_SVM_ZONE > 0) ) {
			$check_flag = false;
			$check = $db->Execute("SELECT `zone_id` FROM `" . TABLE_ZONES_TO_GEO_ZONES . "` WHERE `geo_zone_id` = '" . CFG_PRR_SVM_ZONE . "' AND `zone_country_id` = '" . $order->billing['country']['id'] . "' ORDER BY `zone_id`");
			while (!$check->EOF) {
				if ($check->fields['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
				$check->MoveNext();
			}
			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}

	function javascript_validation() {
		return false;
	}

	function selection() {
		return array('id' => $this->code,
										'module' => $this->title);
	}

	function pre_confirmation_check() {
		return false;
	}

	function confirmation() {
		return false;
	}

	function process_button() {
		// Maksua ei lähetetty
		$_SESSION['prr']['svm']['payment_sent'] = false;
		return false;
	}

	function p($i) {
		// Format price
		return number_format(round($i,2),2);
	}

	// Reserves an ID form the DB
	function order_id() {
		global $db;
		if (empty($this->order_id)) {
			$id = $db->Execute('SELECT `id` FROM `' . TABLE_SUOMENPANKIT . '` WHERE `referid`="0" AND `session_id`="' . session_id() . '"'); // Check if we already have an ID field
			$tid = $id->fields['id'];
			if ($tid == '') {  // If not, we create a new one
				$db->Execute("INSERT INTO `" . TABLE_SUOMENPANKIT . "` (`session_id`) VALUES ('" . session_id() . "')"); // Make the reservation
				$tid = $db->insert_ID();
			}
			$this->order_id = $tid;
		}
		return $this->order_id;
	}

	function before_process() {
		global $messageStack, $order;

		$Lang = CFG_PRR_SVM_DEFAULT_LANGUAGE;
		$zl = strtoupper($_SESSION['languages_code']);
		if ($zl == 'FIN' || $zl == 'FI') $Lang = "fi_FI";
		if ($zl == 'SWE' || $zl == 'SW' || $zl == 'SE'  || $zl == 'SV') $Lang = "sv_SE";
		if ($zl == 'ENG' || $zl == 'EN') $Lang = "en_US";

		if ($_SESSION['prr']['svm']['payment_sent'] != true) {
			// Lähetä maksu

			$link = (ENABLE_SSL == 'false') ? HTTP_SERVER . '/' . DIR_WS_CATALOG : HTTPS_SERVER . '/' . DIR_WS_HTTPS_CATALOG;

			// Luodaan olio mallintamaan kaikkia maksun paluuosoitteita
			$urlset = new Verkkomaksut_Module_Rest_Urlset(
				zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),		// onnistuneen maksun paluuosoite
				zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),		// epäonnistuneet maksun paluuosoite
				$link . "/prr_svm_handler.php",																// osoite, johon lähetetään maksuvarmistus SV:n palvelimelta
				"" // pending-osoite ei käytössä
			);

			// Luodaan olio mallintamaan maksun suorittavan kuluttajan tietoja
			$contact = new Verkkomaksut_Module_Rest_Contact(
				$order->billing['firstname'],             // etunimi
				$order->billing['lastname'],              // sukunimi
				$order->customer['email_address'],         // sähköpostiosoite
				$order->billing['street_address'],        // katuosoite
				$order->billing['postcode'],              // postinumero
				$order->billing['city'],                  // postitoimipaikka
				$order->billing['country']['iso_code_2'], // maa (ISO-3166)
				$order->customer['telephone'],             // puhelinnumero
				"",                                        // matkapuhelinnumero
				$order->billing['company']                // yrityksen nimi
			);

			// Luodaan maksu
			$orderNumber = $this->order_id();                     // Käytä yksikäsitteistä tilausnumeroa
			$payment = new Verkkomaksut_Module_Rest_Payment_E1($orderNumber, $urlset, $contact);

			// Lisätään maksulle yksi tai useampia tuoterivejä
			foreach ($order->products as $p) {
				$id = explode(':',$p['id']);
				// Jos mallia ei ole asetettu, käytä sisäistä ID:tä
				$model = ($p['model'] != '') ? $p['model'] : 'ID#' . $id[0];
				$payment->addProduct(
					$p['name'],     // tuotteen otsikko
					$model,         // tuotekoodi
					$p['qty'],      // tuotteiden määrä
					$this->p($p['price']),    // tuotteen hinta (/kappale)
					$p['tax'],      // Veroprosentti
					"0.00",         // Alennusprosentti
					Verkkomaksut_Module_Rest_Product::TYPE_NORMAL	// Tuotetyyppi			
				);
				// Pidä kirjaa tilauksen arvosta
				$total += ($p['qty'] * $this->p($p['price']));
			}

			$shipping_cost = ($order->info['shipping_cost'] - $order->info['shipping_tax']);
			$payment->addProduct(
				$order->info['shipping_method'],     // tuotteen otsikko
				TEXT_PRR_SVM_SHIPPING_MODEL,         // tuotekoodi
				1,                                   // tuotteiden määrä
				$this->p($shipping_cost),       // tuotteen hinta (/kappale)
				round((($order->info['shipping_cost'] / $shipping_cost) - 1) * 100,2),    // Veroprosentti
				"0.00",         // Alennusprosentti
				Verkkomaksut_Module_Rest_Product::TYPE_POSTAL	// Tuotetyyppi			
			);
			// Pidä kirjaa tilauksen arvosta
			$total += $this->p($shipping_cost);

			// Yhdistä jäljelle jäänyt summa yhdeksi käsittleykuluksi
			$total = $this->p(($order->info['total'] - $order->info['tax']) - $total);
			if ($total <> 0) {
				$extra_tax = 0;
				if (CFG_PRR_SVM_GENERIC_VAT > 0) {
					$extra_tax = zen_get_tax_rate(CFG_PRR_SVM_GENERIC_VAT, $order->delivery['country']['id'], $order->delivery['zone_id']);
				}

				$payment->addProduct(
					TEXT_PRR_SVM_HANDLING_NAME,     				// tuotteen otsikko
					TEXT_PRR_SVM_HANDLING_MODEL,							// tuotekoodi
					1,																								// tuotteiden määrä
					$total,																					// tuotteen hinta (/kappale)
					$extra_tax,																		// Veroprosentti
					"0.00",																					// Alennusprosentti
					Verkkomaksut_Module_Rest_Product::TYPE_HANDLING	// Tuotetyyppi			
				);
			}	
			// Muutetaan maksun oletusasetuksia, tässä vaihdetaan maksutavan valintasivun
			// kieli englanniksi. Oletuksena kieli on suomi. Katso muut mahdollisuudet
			// PHP-luokasta.
			$payment->setLocale($Lang);

			$payment->setVatMode(0);

			// Lähetetään maksu Suomen Verkkomaksujen palveluun ja käsitellään mahdolliset virheet
			$module = new Verkkomaksut_Module_Rest(CFG_PRR_SVM_SELLER_ID, CFG_PRR_SVM_PASSWORD);

			try {
				$result = $module->processPayment($payment);
			} catch (Verkkomaksut_Exception $e) {
				// Jos virhe, mene maksutapa sivulle
				$_SESSION['prr']['svm']['payment_sent'] = false;
				$messageStack->add_session('checkout_payment', TEXT_PRR_SVM_ERROR_SENDING_PAYMENT . $e->getMessage(), 'error');
				header('location: ' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
				die();
			}

			// Kerro että maksu on lähetetty
			$_SESSION['prr']['svm']['payment_sent'] = true;
			// Mutta ei mennyt vielä läpi
			$_SESSION['prr']['svm']['payment_recieved'] = false;
			// Käytetään Suomen Verkkomaksujen palauttamaa url-osoitetta maksun suorittamiselle
			// halutulla tavalla. Tässä käyttäjä ohjataan välittömästi saatuun osoitteeseen.
			header("Location: {$result->getUrl()}");
			die();
		} else {
			// Toinen läpivienti, tällä kertaa maksu on lähetetty ja nyt tarkastetaan onko se oikeasti mennyt läpi
			$module = new Verkkomaksut_Module_Rest(CFG_PRR_SVM_SELLER_ID, CFG_PRR_SVM_PASSWORD);
			if ($module->confirmPayment($_GET["ORDER_NUMBER"], $_GET["TIMESTAMP"], $_GET["PAID"], $_GET["METHOD"], $_GET["RETURN_AUTHCODE"])) {
				// On mennyt läpi!
				$_SESSION['prr']['svm']['payment_recieved'] = true;
			} else {
				$_SESSION['prr']['svm']['payment_recieved'] = false;
				// Maksukuittaus ei ollut validi, mahdollinen huijausyritys
				$messageStack->add_session('checkout_payment', TEXT_PRR_SVM_ERROR_NOT_VALID, 'error');
				// Ohjaa selain maksutavan valintaan.
				header('location: ' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
				die();
			}
		}
		return false;
	}

	function after_process() {
		global $messageStack, $db, $insert_id;
		if ($_SESSION['prr']['svm']['payment_recieved'] == true) {
			// Maksukuittaus on validi
			// Yhdistä väliaikainen tilaus no. varsinaiseen tilausnumeroon
			$order_id = zen_db_input($_GET["ORDER_NUMBER"]);
			$db->Execute('UPDATE ' . TABLE_SUOMENPANKIT . ' SET `orders_id`="' . $insert_id . '", `referid`="' . $order_id . '", `session_id`="" WHERE `session_id`="' . session_id() . '"');
			// Resetoi käsittelyn jälkeen tieto maksun lähetyksestä
			$_SESSION['prr']['svm']['payment_sent'] = false;
		}
		return false;
	}

	function get_error() {
		return false;
	}

	function check() {
		global $db;
			if (!isset($this->_check)) {
				$check_query = $db->Execute("SELECT `configuration_value` FROM `" . TABLE_CONFIGURATION . "` WHERE `configuration_key` = 'CFG_PRR_SVM_STATUS'");
				$this->_check = $check_query->RecordCount();
			}
		return $this->_check;
	}

	function _config() {

		if (!count($this->config)) {
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_STATUS',
				'configuration_title' => 'Tila',
				'configuration_description' => 'Suomen verkkomaksut modulin tila',
				'configuration_value' => 'On',
				'configuration_group_id' => 6,
				'sort_order' => count($cfg),
				'set_function' => "zen_cfg_select_option(array('On','Off'), "
			);
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_SELLER_ID',
				'configuration_title' => 'Kauppiastunnus',
				'configuration_description' => 'Suomen verkkomaksuilta saamanne kauppiastunnus (Testaus: 13466)',
				'configuration_value' => '13466',
				'configuration_group_id' => 6,
				'sort_order' => count($cfg)
			);
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_PASSWORD',
				'configuration_title' => 'Kauppiasvarmenne',
				'configuration_description' => 'Suomen verkkomaksuilta saamanne kauppiasvarmenne (Testaus: 6pKF4jkv97zmqBJ3ZL8gUw5DfT2NMQ)',
				'configuration_value' => '6pKF4jkv97zmqBJ3ZL8gUw5DfT2NMQ',
				'configuration_group_id' => 6,
				'sort_order' => count($cfg)
			);
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_DEFAULT_LANGUAGE',
				'configuration_title' => 'Oletuskieli',
				'configuration_description' => 'Asiakkaan käyttämä kieli tunnistetaan automaattisesti, mutta jos Suomen Verkkomaksut eivät tue kyseistä kieltä, aseta kieli oletuksena tähän arvoon',
				'configuration_value' => 'en_US',
				'configuration_group_id' => 6,
				'sort_order' => count($cfg),
				'set_function' => "zen_cfg_select_option(array('fi_FI','sv_SE','en_US'), "
			);
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_GENERIC_VAT',
				'configuration_title' => 'Käsittelykulujen verokanta',
				'configuration_description' => 'Yleinen verokanta jota käytetään order_total modulien verojen laskemiseen (tätä tietoa on mahdotonta selvittää automaattisesti, johtuen tavasta jolla ZenCart tallentaa order_total modulien tiedot tietokantaan!)',
				'configuration_value' => 0,
				'configuration_group_id' => 6,
				'use_function' => 'zen_get_tax_class_title',
				'set_function' => 'zen_cfg_pull_down_tax_classes(',
				'sort_order' => count($cfg)
			);
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_SORT_ORDER',
				'configuration_title' => 'Järjestys maksutapavalikossa',
				'configuration_description' => 'Lajittelujärjestys maksutapavalikossa',
				'configuration_value' => 0,
				'configuration_group_id' => 6,
				'sort_order' => count($cfg)
			);
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_ZONE',
				'configuration_title' => 'Maksualue',
				'configuration_description' => 'Millä alueella haluat tämän maksutavan olevan käytössä?',
				'configuration_value' => 0,
				'configuration_group_id' => 6,
				'sort_order' => count($cfg),
				'use_function' => 'zen_get_zone_class_title',
				'set_function' => 'zen_cfg_pull_down_zone_classes('
			);
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_ORDER_STATUS_ID',
				'configuration_title' => 'Uuden tilauksen tila',
				'configuration_description' => 'Siirrä uusi tilaus tähän tilaan, heti kun tilaus on vastaanotettu',
				'configuration_value' => 0,
				'configuration_group_id' => 6,
				'sort_order' => count($cfg),
				'use_function' => 'zen_get_order_status_name',
				'set_function' => 'zen_cfg_pull_down_order_statuses('
			);
			$cfg[] = array(
				'configuration_key' => 'CFG_PRR_SVM_ORDER_STATUS_ON_CONFIRM',
				'configuration_title' => 'Maksuvahvistettu tila',
				'configuration_description' => 'Siirrä tilaus tähän tilaan kun maksukuittaus on vastaanotettu',
				'configuration_value' => 0,
				'configuration_group_id' => 6,
				'sort_order' => count($cfg),
				'use_function' => 'zen_get_order_status_name',
				'set_function' => 'zen_cfg_pull_down_order_statuses('
			);
			$this->config = $cfg;
		}
		return $this->config;
	}

	function install() {
		global $db;
		foreach ($this->_config() as $k => $a) {
			$sql = '';
			foreach ($a as $n => $v) {
				$sql['names'] .= "`" . $n . "`, ";
				$sql['values'] .= '"' . addslashes($v) . '", ';
			}
			$sql['names'] .= "`date_added`";
			$sql['values'] .= 'NOW()';
			$db->Execute('INSERT INTO `' . TABLE_CONFIGURATION . '` (' . $sql['names'] . ') VALUES (' . $sql['values'] . ')');
		}
	}

	function remove() {
		global $db;
		$db->Execute('DELETE FROM `' . TABLE_CONFIGURATION . '` WHERE `configuration_key` LIKE "CFG_PRR_SVM_%"');
	}

	function keys() {
		foreach ($this->_config() as $k) $r[] = $k['configuration_key'];
		return $r;
	}

}