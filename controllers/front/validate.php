<?php

class skeletonValidateModuleFrontController extends ModuleFrontController
{
	
	public $ssl = true;
	
	public function initContent()
	{
		$this->display_column_left  = false;
		$this->display_column_right = false;
		
		parent::initContent();
		

//		$strMDStatus = Tools::getValue('mdstatus');

		$this->context->smarty->assign(array(
			'error' => Tools::getValue('mdstatus'). " ". Tools::getValue('mderrormessage'),
		));

		$this->setTemplate('validate.tpl');
	}
	
	public function postProcess()
	{
		$strMDStatus = Tools::getValue('mdstatus');
		//echo Tools::getValue('clientid');
		
		var_dump($_REQUEST);
		
		if ($strMDStatus == "1") {
			
			
			//echo "Tam Doğrulama";
			
			
		}
		if ($strMDStatus == "2") {
			
			
			//echo "Kart Sahibi veya bankası sisteme kayıtlı değil";
			
			
		}
		if ($strMDStatus == "3") {
			
			
			//echo "Kartın bankası sisteme kayıtlı değil";
			
			
		}
		if ($strMDStatus == "4") {
			
			
			//echo "Dogrulama denemesi, kart sahibi sisteme daha sonra kayıt olmayı seçmiş";
			
			
		}
		if ($strMDStatus == "5") {
			
			
			echo "Doğrulama yapılamıyor";
			
			
		}
		if ($strMDStatus == "7") {
			
			
			//echo "Sistem Hatasi";
			
			
		}
		if ($strMDStatus == "8") {
			
			
			echo "Bilinmeyen Kart No";
			
			
		}
		if ($strMDStatus == "0") {
			
			
			echo "Doğrulama Başarısız, 3-D Secure imzası geçersiz.";
			
			
		}
	}
}
