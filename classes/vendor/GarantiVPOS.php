<?php

include_once dirname(__FILE__).'/VPOSInterface.php';

class GarantiVPOS extends VPOSInterface {
	private $instance;
	private $server = array('test' => array('host' =>'sanalposprovtest.garanti.com.tr', 'path' => '/VPServlet'),
							'prod' => array('host' =>'sanalposprov.garanti.com.tr', 'path' => '/VPServlet'));

	private $_3DServer = array('test' => 'https://sanalposprovtest.garanti.com.tr/servlet/gt3dengine',
							'prod' => 'https://sanalposprov.garanti.com.tr/servlet/gt3dengine');
	
	public function __construct() {
		$this->vendor = VPOS_VENDOR_GARANTI;
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
	
	public function get3DFormServer($context, $vpos_merchant) {
		return $vpos_merchant->mode == 0 ? $this->_3DServer['prod'] : $this->_3DServer['test'];
	}
	
	public function get3DFormData($context, $vpos_merchant) {	
		$cart = $context->cart;
		$customer = $context->customer;	
		$vpos_3D_currency_code = "949";//todo by cart currency

		$security_data = strtoupper(sha1($vpos_merchant->vpos_3D_provision_password . sprintf("%09d", $vpos_merchant->vpos_3D_term_id)));
		$orderId = Order::generateReference();
		$vpos_3D_hash_data=strtoupper(sha1(
			$vpos_merchant->vpos_3D_term_id
			.$orderId
			.((int)$cart->getOrderTotal(true, Cart::BOTH)*100)
			.$vpos_merchant->vpos_3D_success_url
			.$vpos_merchant->vpos_3D_error_url
			.$vpos_merchant->vpos_3D_sale_type
			."" //$strInstallmentCount
			.$vpos_merchant->vpos_3D_store_key
			.$security_data));

		$form_data = array('cardnumber' => Tools::getValue('cardnumber'),
					'cardexpiredatemonth' => Tools::getValue('cardexpiredatemonth'),
					'cardexpiredateyear' => Tools::getValue('cardexpiredateyear'),
					'cardcvv2' => Tools::getValue('cardcvv2'),
					'mode' => $vpos_merchant->mode == 0 ? 'PROD' : 'TEST',
					'apiversion' => $vpos_merchant->vpos->version,
					'terminalprovuserid' => $vpos_merchant->vpos_3D_term_prov_user_id,
					'terminaluserid' => $customer->firstname. ' ' . $customer->lastname,
					'terminalmerchantid' => $vpos_merchant->vpos_3D_term_merc_id,
					'txntype' => $vpos_merchant->vpos_3D_sale_type,
					'txnamount' => 	((int)$cart->getOrderTotal(true, Cart::BOTH)*100),
					'txncurrencycode' => $vpos_3D_currency_code,
					'txninstallmentcount' => '',
					'orderid' => $orderId,
					'terminalid' => $vpos_merchant->vpos_3D_term_id,
					'successurl' => $vpos_merchant->vpos_3D_success_url,
					'errorurl' => $vpos_merchant->vpos_3D_error_url,
					'customeripaddress' => $_SERVER['REMOTE_ADDR'],
					'customeremailaddress' => $customer->email,
					'secure3dsecuritylevel' => $vpos_merchant->vpos->vpos_method->name,
					'secure3dhash' => $vpos_3D_hash_data,
				);
		return $form_data;
	}
	
    private function xmlmodel($vpos_merchant)
    {
    
		$customer = $this->context->customer;
		$cart = $this->context->cart;
			
        $strMode                  = Tools::getValue('mode');
        $strVersion               = Tools::getValue('apiversion');//$vpos_merchant->vpos->version;
        $strProvUserID            = Tools::getValue('terminalprovuserid');//$vpos_merchant->vpos_3D_term_prov_user_id;
        $strUserID                = Tools::getValue('terminaluserid');//$customer->firstname. ' ' . $customer->lastname;
        $strTerminalID            = Tools::getValue('clientid');//$vpos_merchant->vpos_3D_term_id;
        $strMerchantID            = Tools::getValue('terminalmerchantid');//$vpos_merchant->vpos_3D_term_merc_id;
        $strIPAddress             = Tools::getValue('customeripaddress');//$_SERVER['REMOTE_ADDR'];
        $strEmailAddress          = Tools::getValue('customeremailaddress');//$customer->email;
        $strOrderID               = Tools::getValue('orderid');
        $strType                  = Tools::getValue('txntype');//$vpos_merchant->vpos_3D_sale_type;
        $strInstallmentCnt        = Tools::getValue('txninstallmentcount');//$DataArray['SESSION']['cc_instalment_order'];
        $strAmount                = Tools::getValue('txnamount');//((int)$cart->getOrderTotal(true, Cart::BOTH)*100);
        $strCurrencyCode          = Tools::getValue('txncurrencycode');//949;
        $strCardholderPresentCode = 13; //3D Model işlemde bu değer 13 olmalı
        $strMotoInd               = "N";
        $strAuthenticationCode    = Tools::getValue('cavv');
        $strSecurityLevel         = Tools::getValue('eci');
        $strTxnID                 = Tools::getValue('xid');
        $strMD                    = Tools::getValue('md');
        $SecurityData             = strtoupper(sha1($vpos_merchant->vpos_3D_provision_password . sprintf("%09d", $vpos_merchant->vpos_3D_term_id)));
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
    
    public function grn_error_codes($status)
    {
        switch ($status) {
            case "01":
                $msg = "Kredi kartınız için bankanız provizyon talep etmektedir. İşlem sonuçlanmamıştır.";
                return $msg;
            case "02":
                $msg = "Kredi kartınız için bankanız provizyon talep etmektedir. İşlem sonuçlanmamıştır.";
                return $msg;
            case "04":
                $msg = "Bu kredi kartı ile alışveriş yapamazsınız. Başka bir kartla tekrar deneyiniz.";
                return $msg;
            case "05":
                $msg = "İşlem onaylanmadı. Kredi kartınız ile işlem limitini aşmış olabilirsiniz. Bankanızı arayınız.";
                return $msg;
            case "09":
                $msg = "Kredi kartınız yenilenmiştir. Yenilenmiş kartınız ile tekrar deneyiniz.";
                return $msg;
            case "10":
                $msg = "İşlem onaylanmadı. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
                return $msg;
            case "14":
                $msg = "Kredi kart numaranız hatalıdır. Kart bilgilerinizi kontrol edip tekrar deneyiniz.";
                return $msg;
            case "16":
                $msg = "Kredi kartınızın bakiyesi yetersiz. Başka bir kredi kartı ile tekrar deneyiniz.";
                return $msg;
            case "30":
                $msg = "Bankanıza ulaşılamadı. Tekrar denemenizi tavsiye ediyoruz.";
                return $msg;
            case "36":
                $msg = "Kredi kartınız kayıp veya çalıntı olarak bildirilmiştir.";
                return $msg;
            case "41":
                $msg = "Kredi kartınız kayıp veya çalıntı olarak bildirilmiştir.";
                return $msg;
            case "43":
                $msg = "Kredi kartınız kayıp veya çalıntı olarak bildirilmiştir.";
                return $msg;
            case "51":
                $msg = "Kredi kartınızın bakiyesi yetersiz. Başka bir kredi kartı ile tekrar deneyiniz.";
                return $msg;
            case "54":
                $msg = "İşlem onaylanmadı. Kartınızı kontrol edip tekrar deneyiniz.";
                return $msg;
            case "57":
                $msg = "İşlem onaylanmadı. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
                return $msg;
            case "58":
                $msg = "Yetkisiz bir işlem yapıldı. Örn: Kredi kartınızın ait olduğu banka dışında bir bankadan taksitlendirme yapıyor olabilirsiniz. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
                return $msg;
            case "62":
                $msg = "İşlem onaylanmadı. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
                return $msg;
            case "65":
                $msg = "Kredi kartınızın günlük işlem limiti dolmuştur. Başka bir kredi kartı ile deneyiniz.";
                return $msg;
            case "77":
                $msg = "İşlem onaylanmadı. Başka bir kredi kartı ile işlem yapmayı deneyiniz.";
                return $msg;
            case "82":
                $msg = "İşlem onaylanmadı. Kart bilgilerinizi kontrol edip tekrar deneyiniz.";
                return $msg;
            case "91":
                $msg = "Bankanıza ulaşılamıyor. Başka bir kredi kartı ile tekrar deneyiniz.";
                return $msg;
            case "92":
                $msg = "Minimum islem limitinin altinda bir ödeme miktari nedeniyle isleminiz gerçeklesmedi.";
                return $msg;
            case "99":
                $msg = "İşlem onaylanmadı. Kart bilgilerinizi kontrol edip tekrar deneyiniz.";
                return $msg;
        }
        $msg = "Lütfen bilgilerinizi kontrol ediniz..";
        return $msg;
    }
    
    public function makeProvision($context, $vpos_merchant)
    {
    	// check method and mdstatus
    	$timeout = 90;
        $postdata  = $this->xmlmodel($vpos_merchant);
        echo $postdata;
        if($vpos_merchant->vpos->mode == 0) {
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
                $msg['result'] = -1;
                $msg['msg']    = ":: Bağlantı hatası lütfen daha sonra tekrar deneyiniz.";
                return $msg;
            }
            fputs($fp, "POST " . $path . " HTTP/1.1\r\n");
            fputs($fp, "Host: $host\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: " . $strlength . "\r\n\r\n");
            fputs($fp, "data=" . $postdata);
            $buffer = fread($fp, 8192);
            fclose($fp);
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
                $msg['result'] = -1;
                $msg['msg']    = ":: Bağlantı hatası lütfen daha sonra tekrar deneyiniz.";
                return $msg;
                /* curl_error($ch)  */
            } else {
                curl_close($ch);
            }
        }
        
        $Response      = substr($buffer, strpos($buffer, "<GVPSResponse>"));
        $responseArray = $this->xmltohash($Response);
        
        switch ($responseArray['GVPSResponse']['Transaction']['Response']['Message']) {
            case "Approved":
                $msg['result']    = 1;
                $msg['auth_code'] = $responseArray['GVPSResponse']['Transaction']['AuthCode'];
                break;
            case "Declined":
                $msg['result'] = -1;
                $msg['msg']    = $this->grn_error_codes($responseArray['GVPSResponse']['Transaction']['Response']['Code']) . "-" . $responseArray['GVPSResponse']['Transaction']['Response']['ErrorMsg'] . " , " . $responseArray['GVPSResponse']['Transaction']['Response']['SysErrMsg'];
                break;
            case "Error":
                $msg['result'] = -1;
                $msg['msg']    = ":= " . $this->grn_error_codes($responseArray['GVPSResponse']['Transaction']['Response']['Code']) . "-" . $responseArray['GVPSResponse']['Transaction']['Response']['ErrorMsg'] . " , " . $responseArray['GVPSResponse']['Transaction']['Response']['SysErrMsg'];
        }
        return $msg;
    }
	
	public function parse3DResult($data) {
	
	}
}



?>
