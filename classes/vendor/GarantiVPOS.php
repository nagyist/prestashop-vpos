<?php

include_once dirname(__FILE__).'/VPOSInterface.php';

class GarantiVPOS extends VPOSInterface {
	private $instance;
	private $server = array('test' => array('host' =>'sanalposprovtest.garanti.com.tr', 'path' => '/VPServlet'),
							'prod' => array('host' =>'sanalposprov.garanti.com.tr', 'path' => '/VPServlet'));

	private $_3DServer = array('test' => 'https://sanalposprovtest.garanti.com.tr/servlet/gt3dengine',
							'prod' => 'https://sanalposprov.garanti.com.tr/servlet/gt3dengine');
	
	public function __construct() {
		$this->vendor = VPOSVPOS_VENDOR_GARANTI;
		$this->method = VPOS_METHOD_3D;
		$this->bank = VPOS_BANK_GARANTI;

		$this->hash = $this->getHash();
	}

	public function getInstance() {
		if($this->instance == null)
			$this->instance = new GarantiVPOS();

		return $this->instance;
	}

	public function isVPOSImplemented($vpos) {
		return $this->vendor == $vpos->vpos_vendor->getType() &&
			$this->method == $vpos->vpos_method->getType() &&
			$this->bank == $vpos->vpos_bank->getType();
	}

	public function get3DFormServer($context, $vposMerchant) {
		return $vposMerchant->mode == 0 ? $this->_3DServer['prod'] : $this->_3DServer['test'];
	}

	public function get3DFormData($context, $vposMerchant, $extra) {
		$cart = $context->cart;
		$customer = $context->customer;
		$currency = new Currency($cart->id_currency);

		/* Currency Code */
		$vpos_3D_currency_code = "";
		switch($currency->iso_code) {
			case "TRY":
				$vpos_3D_currency_code = "949";
				break;
			default:
				/* Unsupported currency */
				return null;
		}
		
		/* Installment */
		$vpos_installment_count = "";
		if(!empty($vposMerchant->installments[$extra["installment"]])) {
			$vpos_installment_count = $extra["installment";
			$total_amount = int(((float)$cart->getOrderTotal(true, Cart::BOTH)) * (($vposMerchant->installments[$extra["installment"]] + 100) / 100) * 100);
		}
		else {
			$total_amount = int(((float)$cart->getOrderTotal(true, Cart::BOTH)) * 100);
		}

		/* Calculate 3D security code*/
		$securityData = strtoupper(sha1($vposMerchant->vpos_3D_provision_password . sprintf("%09d", $vposMerchant->vpos_3D_term_id)));
		$vposOrderId = Order::generateReference();
		$vpos_3D_hash_data=strtoupper(sha1(
					$vposMerchant->vpos_3D_term_id
					.$vpos_orderId
					.$total_amount
					.$vposMerchant->vpos_3D_success_url
					.$vposMerchant->vpos_3D_error_url
					.$vposMerchant->vpos_3D_sale_type
					.$installment
					.$vposMerchant->vpos_3D_store_key
					.$securityData));

		$form_data = array('cardnumber' => Tools::getValue('cardnumber'),
				'cardexpiredatemonth' => Tools::getValue('cardexpiredatemonth'),
				'cardexpiredateyear' => Tools::getValue('cardexpiredateyear'),
				'cardcvv2' => Tools::getValue('cardcvv2'),
				'mode' => $vposMerchant->mode == 0 ? 'PROD' : 'TEST',
				'apiversion' => $vposMerchant->vpos->version,
				'terminalprovuserid' => $vposMerchant->vpos_3D_term_prov_user_id,
				'terminaluserid' => $customer->firstname. ' ' . $customer->lastname,
				'terminalmerchantid' => $vposMerchant->vpos_3D_term_merc_id,
				'txntype' => $vposMerchant->vpos_3D_sale_type,
				'txnamount' => 	$total_amount,
				'txncurrencycode' => $vpos_3D_currency_code,
				'txninstallmentcount' => $vpos_installment_count,
				'orderid' => $vposOrderId,
				'terminalid' => $vposMerchant->vpos_3D_term_id,
				'successurl' => $vposMerchant->vpos_3D_success_url,
				'errorurl' => $vposMerchant->vpos_3D_error_url,
				'customeripaddress' => $_SERVER['REMOTE_ADDR'],
				'customeremailaddress' => $customer->email,
				'secure3dsecuritylevel' => $vposMerchant->vpos->vpos_method->name,
				'secure3dhash' => $vpos_3D_hash_data,
				);
		return $form_data;
	}

	private function xmlmodel($vposMerchant)
	{
		$customer = $this->context->customer;
		$cart = $this->context->cart;

		$strMode                  = Tools::getValue('mode');
		$strVersion               = Tools::getValue('apiversion');//$vposMerchant->vpos->version;
		$strProvUserID            = Tools::getValue('terminalprovuserid');//$vposMerchant->vpos_3D_term_prov_user_id;
		$strUserID                = Tools::getValue('terminaluserid');//$customer->firstname. ' ' . $customer->lastname;
		$strTerminalID            = Tools::getValue('clientid');//$vposMerchant->vpos_3D_term_id;
		$strMerchantID            = Tools::getValue('terminalmerchantid');//$vposMerchant->vpos_3D_term_merc_id;
		$strIPAddress             = Tools::getValue('customeripaddress');//$_SERVER['REMOTE_ADDR'];
		$strEmailAddress          = Tools::getValue('customeremailaddress');//$customer->email;
		$strOrderID               = Tools::getValue('orderid');
		$strType                  = Tools::getValue('txntype');//$vposMerchant->vpos_3D_sale_type;
		$strInstallmentCnt        = Tools::getValue('txninstallmentcount');//$DataArray['SESSION']['cc_instalment_order'];
		$strAmount                = Tools::getValue('txnamount');//((int)$cart->getOrderTotal(true, Cart::BOTH)*100);
		$strCurrencyCode          = Tools::getValue('txncurrencycode');//949;
		$strCardholderPresentCode = 13; //3D Model işlemde bu değer 13 olmalı
		$strMotoInd               = "N";
		$strAuthenticationCode    = Tools::getValue('cavv');
		$strSecurityLevel         = Tools::getValue('eci');
		$strTxnID                 = Tools::getValue('xid');
		$strMD                    = Tools::getValue('md');
		$SecurityData             = strtoupper(sha1($vposMerchant->vpos_3D_provision_password . sprintf("%09d", $vposMerchant->vpos_3D_term_id)));
		$HashData                 = strtoupper(sha1($strOrderID . $strTerminalID . $strAmount . $SecurityData));
		$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
			<GVPSRequest>
				<Mode>$strMode</Mode>
				<Version>$strVersion</Version>
				<ChannelCode></ChannelCode>
				<Terminal>
					<ProvUserID>$strProvUserID</ProvUserID>
					<HashData>$HashData</HashData>
					<UserID>$strUserID</UserID>
					<ID>$strTerminalID</ID>
					<MerchantID>$strMerchantID</MerchantID>
				</Terminal>
				<Customer>
					<IPAddress>$strIPAddress</IPAddress>
					<EmailAddress>$strEmailAddress</EmailAddress>
				</Customer>
				<Card>
					<Number></Number>
					<ExpireDate></ExpireDate>
				</Card>
				<Order>
					<OrderID>$strOrderID</OrderID>
					<GroupID></GroupID>
					<Description></Description>
				</Order>
				<Transaction>
					<Type>$strType</Type>
					<InstallmentCnt>$strInstallmentCnt</InstallmentCnt>
					<Amount>$strAmount</Amount>
					<CurrencyCode>$strCurrencyCode</CurrencyCode>
					<CardholderPresentCode>$strCardholderPresentCode</CardholderPresentCode>
					<MotoInd>$strMotoInd</MotoInd>
					<Description></Description>
					<Secure3D>
						<AuthenticationCode>$strAuthenticationCode</AuthenticationCode>
						<SecurityLevel>$strSecurityLevel</SecurityLevel>
						<TxnID>$strTxnID</TxnID>
						<Md>$strMD</Md>
					</Secure3D>
				</Transaction>
			</GVPSRequest>";

		return $xml;
	}

	private function xmltohash($data)
	{
		$response = array();
		$parser   = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $data, $values, $tags);
		xml_parser_free($parser);
		$arrQuotes = array();
		foreach ($values as $key => $val) {
			switch ($val['type']) {
				case "open":
					array_push($arrQuotes, $val['tag']);
					break;
				case "close":
					array_pop($arrQuotes);
					break;
				case "complete":
					array_push($arrQuotes, $val['tag']);
					$val['value'] = (array_key_exists('value', $val)) ? $val['value'] : "";
					eval("\$response['" . implode($arrQuotes, "']['") . ("'] = \"" . $val['value'] . "\";"));
					array_pop($arrQuotes);
			}
		}
		return $response;
	}

	public function getProvisionError($status)
	{
		$msg = "";
		switch ($status) {
			case "01":
				$msg = "Kredi kartınız için bankanız provizyon talep etmektedir. İşlem sonuçlanmamıştır.";
				break;
			case "02":
				$msg = "Kredi kartınız için bankanız provizyon talep etmektedir. İşlem sonuçlanmamıştır.";
				break;
			case "04":
				$msg = "Bu kredi kartı ile alışveriş yapamazsınız. Başka bir kartla tekrar deneyiniz.";
				break;
			case "05":
				$msg = "İşlem onaylanmadı. Kredi kartınız ile işlem limitini aşmış olabilirsiniz. Bankanızı arayınız.";
				break;
			case "09":
				$msg = "Kredi kartınız yenilenmiştir. Yenilenmiş kartınız ile tekrar deneyiniz.";
				break;
			case "10":
				$msg = "İşlem onaylanmadı. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
				break;
			case "14":
				$msg = "Kredi kart numaranız hatalıdır. Kart bilgilerinizi kontrol edip tekrar deneyiniz.";
				break;
			case "16":
				$msg = "Kredi kartınızın bakiyesi yetersiz. Başka bir kredi kartı ile tekrar deneyiniz.";
				break;
			case "30":
				$msg = "Bankanıza ulaşılamadı. Tekrar denemenizi tavsiye ediyoruz.";
				break;
			case "36":
				$msg = "Kredi kartınız kayıp veya çalıntı olarak bildirilmiştir.";
				break;
			case "41":
				$msg = "Kredi kartınız kayıp veya çalıntı olarak bildirilmiştir.";
				break;
			case "43":
				$msg = "Kredi kartınız kayıp veya çalıntı olarak bildirilmiştir.";
				break;
			case "51":
				$msg = "Kredi kartınızın bakiyesi yetersiz. Başka bir kredi kartı ile tekrar deneyiniz.";
				break;
			case "54":
				$msg = "İşlem onaylanmadı. Kartınızı kontrol edip tekrar deneyiniz.";
				break;
			case "57":
				$msg = "İşlem onaylanmadı. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
				break;
			case "58":
				$msg = "Yetkisiz bir işlem yapıldı. Örn: Kredi kartınızın ait olduğu banka dışında bir bankadan taksitlendirme yapıyor olabilirsiniz. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
				break;
			case "62":
				$msg = "İşlem onaylanmadı. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
				break;
			case "65":
				$msg = "Kredi kartınızın günlük işlem limiti dolmuştur. Başka bir kredi kartı ile deneyiniz.";
				break;
			case "77":
				$msg = "İşlem onaylanmadı. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
				break;
			case "82":
				$msg = "İşlem onaylanmadı. Kart bilgilerinizi kontrol edip tekrar deneyiniz.";
				break;
			case "91":
				$msg = "Bankanıza ulaşılamıyor. Başka bir kredi kartı ile tekrar deneyiniz.";
				break;
			case "92":
				$msg = "Minimum islem limitinin altinda bir ödeme miktari nedeniyle isleminiz gerçeklesmedi.";
				break;
			case "99":
				$msg = "İşlem onaylanmadı. Kart bilgilerinizi kontrol edip tekrar deneyiniz.";
				break;
			default:
				$msg = "Lütfen bilgilerinizi kontrol ediniz..";
		}

		return $msg;
	}

	private function get3DErrorCode($code) {
		$msg = "";
		switch($code) {
			case "0":
				$msg = "Doğrulama başarısız, 3-D Secure imzası geçersiz.";
				break;
			case "1":
				$msg = "Tam doğrulama";
				break;
			case "2":
				$msg = "Kart Sahibi veya bankası sisteme kayıtlı değil";
				break;
			case "3":
				$msg = "Kartın bankası sisteme kayıtlı değil";
				break;
			case "4":
				$msg = "Doğrulama denemesi, kart sahibi sisteme daha sonra kayıt olmayı seçmiş.";
				break;
			case "5":
				$msg = "Doğrulama yapılamıyor";
				break;
			case "6":
				$msg = "Doğrulama yapılamıyor";
				break;
			case "7":
				$msg = "Sistem Hatası";
				break;
			case "8":
				$msg = "Bilinmeyen Kart No";
				break;
			default:
				$msg = "Bilinmeyen kod";
		}
		return $msg;
	}

	private function processProvision($context, $vposMerchant) {
		$timeout = 90;
		$postdata  = $this->xmlmodel($vposMerchant);

		if(empty($postdata))
			return createMsg(VPOSInterface::VPOS_RESULT_XMLMODEL_ERROR, 'Can not create XML Model');

		if($vposMerchant->vpos->mode == 0) {
			$host = $this->server['test']['host'];
			$path = $this->server['test']['path'];
		}
		else {
			$host = $this->server['prod']['host'];
			$path = $this->server['prod']['path'];
		}

		$strlength = strlen($postdata) + 5;
		$buffer    = "";
		if (!extension_loaded('curl')) {
			$fp = fsockopen("ssl://" . $host, 443, $errno, $errstr, $timeout);
			if (!$fp) {
				return return $this->createResult(VPOSInterface::VPOS_RESULT_CONNECTION_ERROR, "Bağlantı hatası lütfen daha sonra tekrar deneyiniz.");
			}
			fputs($fp, "POST " . $path . " HTTP/1.1\r\n");
			fputs($fp, "Host: $host\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . $strlength . "\r\n\r\n");
			fputs($fp, "data=" . $postdata);
			$buffer = fread($fp, 8192);
			fclose($fp);

			if(empty($buffer))
				return return $this->createResult(VPOSInterface::VPOS_RESULT_CONNECTION_ERROR, "Bağlantı hatası lütfen daha sonra tekrar deneyiniz.");
		} else {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://" . $host . $path);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $postdata);
			$buffer = curl_exec($ch);
			if (curl_errno($ch)) {
				return $this->createResult(VPOSInterface::VPOS_RESULT_CONNECTION_ERROR, "Bağlantı hatası lütfen daha sonra tekrar deneyiniz.");
			} else {
				curl_close($ch);
			}
		}

		$Response      = substr($buffer, strpos($buffer, "<GVPSResponse>"));
		$responseArray = $this->xmltohash($Response);

		if(empty($Response) || empty($responseArray))
			return $this->createResult(VPOSInterface::VPOS_RESULT_PROTOCOL_ERROR, "Bağlantı hatası lütfen daha sonra tekrar deneyiniz.");

		switch ($responseArray['GVPSResponse']['Transaction']['Response']['Message']) {
			case "Approved":
				$msg = $this->createResult(VPOS_RESULT_SUCCESS, $responseArray['GVPSResponse']['Transaction']['AuthCode']);		
				break;
			case "Declined":
			case "Error":
				$msg = $this->createResult(VPOS_RESULT_PROVISION_ERROR, $this->getProvisionError($responseArray['GVPSResponse']['Transaction']['Response']['Code']) . "-" . $responseArray['GVPSResponse']['Transaction']['Response']['ErrorMsg'] . " , " . $responseArray['GVPSResponse']['Transaction']['Response']['SysErrMsg']);
				break;
			default:
				$msg = $this->createResult(VPOSInterface::VPOS_RESULT_PROTOCOL_ERROR, "Bağlantı hatası lütfen daha sonra tekrar deneyiniz.");
		}
		return $msg;
	}

	public function makeProvision($context, $vposMerchant)
	{
		/* Check method and mdstatus */
		_3D_md_status = Tools::getValue('mdstatus');
		if(!empty($_3D_md_status)) {
			if($this->type == VPOSMethod::VPOS_METHOD_3D) {
				switch($_3D_md_status) {
					case "1":
					case "2":
					case "3":
					case "4":
						return processProvision($context, $vposMerchant);
					default:
						return $this->createResult(VPOSInterface::VPOS_RESULT_3D_ERROR, $this->get3DErrorCode($_3D_md_status));
				}
			}
			else
				return $this->createResult(VPOSInterface::VPOS_RESULT_3D_METHOD_NOT_SUPPORTED, "Configuration error");
		}
		
		return processProvision($context, $vposMerchant);
	}

	public function parse3DResult($data) {

	}
}



?>
