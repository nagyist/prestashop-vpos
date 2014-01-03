<?php

/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <jevin9@gmail.com> wrote this module. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return. Jevin O. Sewaruth.
 * ----------------------------------------------------------------------------
 */

if (!defined('_PS_VERSION_'))
	exit;

include_once _PS_MODULE_DIR_.'skeleton/classes/VPOS.php';

class Skeleton extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'skeleton';
		$this->tab = 'payments_gateways';
		$this->version = '1.0';
		$this->author = 'Kabil Akpınar';

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		parent::__construct();

		$this->displayName = $this->l('Virtual POS Module');
		$this->description = $this->l('This is just an empty module. You should modify it to make your own.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

		$this->_checkContent();

		$this->context->smarty->assign('module_name', $this->name);
	}

	public function install()
	{
		if (!Db::getInstance()->execute(
			'DROP TABLE IF EXISTS `'._DB_PREFIX_.'vpos`'))
			return false;
			
		if (!Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vpos` (
				`id_vpos` int(10) NOT NULL AUTO_INCREMENT,
				`desc` varchar(256) NOT NULL,
				`version` varchar(10) NOT NULL,
				`vpos_vendor` int(10) NOT NULL,
				`vpos_bank` int(10) NOT NULL,
				`vpos_method` int(10) NOT NULL,
				`vpos_3D_gateway_url` varchar(256) NOT NULL,
				`vpos_3D_gateway_test_url` varchar(256) NOT NULL,
				`vpos_3D_gateway_cancel_url` varchar(256) NOT NULL,
				`vpos_3D_gateway_refund_url` varchar(256) NOT NULL,
				PRIMARY KEY (`id_vpos`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;'))
			return false;

		if (!Db::getInstance()->execute(
			'DROP TABLE IF EXISTS `'._DB_PREFIX_.'vpos_merchant`'))
			return false;
			
		if (!Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vpos_merchant` (
				`id_vpos_merchant` int(10) NOT NULL AUTO_INCREMENT,
				`id_vpos` int(10) NOT NULL,
				`mode` tinyint(1),
				`description` varchar(256) NOT NULL,
				`is_default` tinyint(1),
				`vpos_3D_term_prov_user_id` varchar(256) NOT NULL,
				`vpos_3D_term_id` varchar(256) NOT NULL,
				`vpos_3D_sale_type` varchar(40) NOT NULL,
				`vpos_3D_term_merc_id` varchar(40) NOT NULL,
				`vpos_3D_store_key` varchar(40) NOT NULL,
				`vpos_3D_provision_password` varchar(40) NOT NULL,
				`vpos_3D_success_url` varchar(256) NOT NULL,
				`vpos_3D_error_url` varchar(256) NOT NULL,
				PRIMARY KEY (`id_vpos_merchant`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;'))
			return false;
			
		if (!Db::getInstance()->execute(
			'DROP TABLE IF EXISTS `'._DB_PREFIX_.'vpos_bank`'))
			return false;
				
		if (!Db::getInstance()->execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vpos_bank` (
				`id_vpos_bank` int(10) NOT NULL AUTO_INCREMENT,
				`name` varchar(100) NOT NULL,
				PRIMARY KEY (`id_vpos_bank`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;'))
			return false;
		
		if (!Db::getInstance()->execute(
			'DROP TABLE IF EXISTS `'._DB_PREFIX_.'vpos_vendor`'))
			return false;
			
		if (!Db::getInstance()->execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vpos_vendor` (
				`id_vpos_vendor` int(10) NOT NULL AUTO_INCREMENT,
				`name` varchar(100) NOT NULL,
				PRIMARY KEY (`id_vpos_vendor`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;'))
			return false;

		if (!Db::getInstance()->execute(
			'DROP TABLE IF EXISTS `'._DB_PREFIX_.'vpos_method`'))
			return false;
				
		if (!Db::getInstance()->execute(
			'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'vpos_method` (
				`id_vpos_method` int(10) NOT NULL AUTO_INCREMENT,
				`name` varchar(100) NOT NULL,
				PRIMARY KEY (`id_vpos_method`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;'))
			return false;

		if (!parent::install() || !$this->installTab() ||
			!$this->registerHook('payment') ||
			!$this->registerHook('paymentReturn') ||
			!$this->_createContent())
			return false;
			
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() ||
			!$this->_deleteContent())
			return false;
		return true;
	}
	
	
	public function installTab()
	{
		$tab = new Tab();
		$tab->active = 1;
		$tab->class_name = "AdminVPOS";
		$tab->name = array();
		foreach (Language::getLanguages(true) as $lang)
			$tab->name[$lang['id_lang']] = "Virtual POS";
		$tab->id_parent = (int)Tab::getIdFromClassName('AdminAdmin');
		$tab->module = $this->name;
		return $tab->add();
	}
	
	public function uninstallTab()
	{
		$id_tab = (int)Tab::getIdFromClassName('AdminVPOS');
		if ($id_tab)
		{
			$tab = new Tab($id_tab);
			return $tab->delete();
		}
		else
			return false;
	}
	
	public function hookPayment($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;


		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'vpos.tpl');
	}
	
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		if ($state == Configuration::get('PS_OS_BANKWIRE') || $state == Configuration::get('PS_OS_OUTOFSTOCK'))
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'bankwireDetails' => Tools::nl2br($this->details),
				'bankwireAddress' => Tools::nl2br($this->address),
				'bankwireOwner' => $this->owner,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}

	public function getContent()
	{
		Tools::redirectAdmin($this->context->link->getAdminLink('AdminVPOS'));
	}
	
	/*
	public function getContent()
	{
		$html = '';
		$message = '';

		$id_vposinfo = (int)Tools::getValue('id_vpos');

		if(Tools::isSubmit('saveVPOSInfo')) {

			if ($id_vposinfo = Tools::getValue('id_vpos'))
				$vposinfo = new VPOSInfoClass((int)$id_vposinfo);
			else
				$vposinfo = new VPOSInfoClass();

			$vposinfo->copyFromPost();

			if ($vposinfo->validateFields(false)) {
				$vposinfo->save();
				//$this->_clearCache('blockreinsurance.tpl');
			}
			else
				$html .= '<div class="conf error">'.$this->l('An error occurred while attempting to save.').'</div>';
		}
		if (Tools::isSubmit('updateskeleton') || Tools::isSubmit('addVPOSInfoskeleton'))
		{
			$helper = $this->initVPOSInfoForm();

			if ($id_vposinfo = Tools::getValue('id_vpos'))
			{
				$vposinfo = new VPOSInfoClass((int)$id_vposinfo);
				$this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_vpos');
				$helper->fields_value['id_vpos'] = (int)$id_vposinfo;
				
				$helper->fields_value['desc'] = $vposinfo->desc;
				$helper->fields_value['version'] = $vposinfo->version;
				$helper->fields_value['vpos_bank'] = $vposinfo->vendor_bank;
				$helper->fields_value['vpos_vendor'] = $vposinfo->vpos_vendor;
				$helper->fields_value['vpos_method'] = $vposinfo->vpos_method;
				$helper->fields_value['vpos_3D_gateway_url'] = $vposinfo->vpos_3D_gateway_url;
				$helper->fields_value['vpos_3D_gateway_test_url'] = $vposinfo->vpos_3D_gateway_test_url;
				$helper->fields_value['vpos_3D_gateway_cancel_url'] = $vposinfo->vpos_3D_gateway_cancel_url;
				$helper->fields_value['vpos_3D_gateway_test_url'] = $vposinfo->vpos_3D_gateway_test_url;
				$helper->fields_value['vpos_3D_gateway_refund_url'] = $vposinfo->vpos_3D_gateway_refund_url;
 			}
				
			return $html.$helper->generateForm($this->fields_form);
		}
		else if (Tools::isSubmit('delete'.$this->name))
		{
			$vposinfo = new VPOSInfoClass((int)$id_vposinfo);
			$vposinfo->delete();
			//$this->_clearCache('blockreinsurance.tpl');
			Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
		}
		else
		{
			$helper = $this->initVPOSInfoList();
			return $html.$helper->generateList($this->getVPOSListContent((int)Configuration::get('PS_LANG_DEFAULT')), $this->fields_list);
		}
	}
	*/
	private function _saveContent()
	{
		$message = '';

		if (Configuration::updateValue('MOD_SKELETON_NAME', Tools::getValue('MOD_SKELETON_NAME')) &&
			Configuration::updateValue('MOD_SKELETON_COLOR', Tools::getValue('MOD_SKELETON_COLOR')))
			$message = $this->displayConfirmation($this->l('Your settings have been saved'));
		else
			$message = $this->displayError($this->l('There was an error while saving your settings'));

		return $message;
	}

	protected function getVPOSListContent($id_lang)
	{
		$sql = new DbQuery();
		$sql->select('v.`id_vpos`, v.`desc`, v.`version`, t.`name` vendor, b.`name` bank, m.`name` method');
		$sql->leftjoin('vpos_vendor', 't', 't.`id_vpos_vendor` = v.`vpos_vendor`');
		$sql->leftjoin('vpos_bank', 'b', 'b.`id_vpos_bank` = v.`vpos_bank`');
		$sql->leftjoin('vpos_method', 'm', 'm.`id_vpos_method` = v.`vpos_method`');
		$sql->from('vpos', 'v');
		return  Db::getInstance()->executeS($sql);
	}
	
	protected function getBankListContent()
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('vpos_bank', 'b');
		return  Db::getInstance()->executeS($sql);
	}
	
	protected function getPOSVendorListContent()
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('vpos_vendor', 'v');
		return  Db::getInstance()->executeS($sql);
	}
	
	protected function getPOSMethodListContent()
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('vpos_method', 'm');
		return  Db::getInstance()->executeS($sql);
	}
	
	protected function getVPOSMerchantListContent($id_lang)
	{
		$sql = new DbQuery();
		$sql->select('v.`id_vpos_merchant`, v.`desc`, t.`desc` vpos_desc');
		$sql->leftjoin('vpos', 't', 't.`id_vpos` = v.`id_vpos`');
		$sql->from('vpos_merchant', 'v');
		return  Db::getInstance()->executeS($sql);
	}
	
	protected function initVPOSInfoForm()
	{
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Virtual POS Settings'),
			),
			'input' => array(
				array(
					'type' => 'select',
					'label' => $this->l('Bank'),
					'name' => 'vpos_bank',
					'options' => array(
        				'query' => $this->getBankListContent(),    
        				'id' => 'id_vpos_bank',
        				'name' => 'name'
      				),
				),
				array(
					'type' => 'select',
					'label' => $this->l('Virtual POS Vendor'),
					'name' => 'vpos_vendor',
					'options' => array(
        				'query' => $this->getPOSVendorListContent(),
        				'id' => 'id_vpos_vendor',
        				'name' => 'name'
      				),
				),
				array(
					'type' => 'select',
					'label' => $this->l('Virtual POS Method:'),
					'name' => 'vpos_method',
					'options' => array(
        				'query' => $this->getPOSMethodListContent(),
        				'id' => 'id_vpos_method',
        				'name' => 'name'
      				),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Description'),
					'name' => 'desc',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Version'),
					'name' => 'version',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Gateway URL'),
					'name' => 'vpos_3D_gateway_url',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Gateway Test URL'),
					'name' => 'vpos_3D_gateway_test_url',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Gateway Cancel URL'),
					'name' => 'vpos_3D_gateway_cancel_url',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Gateway Refund URL'),
					'name' => 'vpos_3D_gateway_refund_url',
					'size' => 40,
					'required' => true,
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);

		
		$helper = new HelperForm();
		$helper->module = $this;
		//$helper->name_controller = 'blockreinsurance';
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		foreach (Language::getLanguages(false) as $lang)
			$helper->languages[] = array(
				'id_lang' => $lang['id_lang'],
				'iso_code' => $lang['iso_code'],
				'name' => $lang['name'],
				'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
			);

		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		$helper->toolbar_scroll = true;
		$helper->title = $this->displayName;
		$helper->submit_action = 'saveVPOSInfo';
		$helper->toolbar_btn =  array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&saveVPOSInfo'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' =>
			array(
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);
		return $helper;
	}
	
	protected function initVPOSMerchantInfoForm()
	{
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Virtual POS  Merchant Settings'),
			),
			'input' => array(
				array(
					'type' => 'select',
					'label' => $this->l('Virtual POS Entry'),
					'name' => 'vpos',
					'options' => array(
        				'query' => $this->getVPOSListContent(),    
        				'id' => 'id_vpos',
        				'name' => 'desc'
      				),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Description'),
					'name' => 'desc',
					'size' => 40
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Is default entry?'),
					'name' => 'default',
					'is_bool'   => true,
					'values'    => array(
										array(
										  'id'    => 'active_on',
										  'value' => 1,
										  'label' => $this->l('Enabled')
										),
										array(
										  'id'    => 'active_off',
										  'value' => 0,
										  'label' => $this->l('Disabled')
										)
									  ),
				),
				array(
					'type' => 'text',
					'label' => $this->l('3D Terminal Provision User ID'),
					'name' => 'vpos_3D_term_prov_user_id',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('3D Terminal ID'),
					'name' => 'vpos_3D_term_id',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('3D Sale Type'),
					'name' => 'vpos_3D_sale_type',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('3D Terminal Merchant ID'),
					'name' => 'vpos_3D_term_merc_id',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('3D Store Key'),
					'name' => 'vpos_3D_store_key',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('3D Provision Password'),
					'name' => 'vpos_3D_provision_password',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('3D Success URL'),
					'name' => 'vpos_3D_success_url',
					'size' => 40,
					'required' => true,
				),
				array(
					'type' => 'text',
					'label' => $this->l('3D Error URL'),
					'name' => 'vpos_3D_error_url',
					'size' => 40,
					'required' => true,
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);
		
		$helper = new HelperForm();
		$helper->module = $this;
		//$helper->name_controller = 'blockreinsurance';
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		foreach (Language::getLanguages(false) as $lang)
			$helper->languages[] = array(
				'id_lang' => $lang['id_lang'],
				'iso_code' => $lang['iso_code'],
				'name' => $lang['name'],
				'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
			);

		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		$helper->toolbar_scroll = true;
		$helper->title = $this->displayName;
		$helper->submit_action = 'saveVPOSMerchantInfo';
		$helper->toolbar_btn =  array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&saveVPOSMerchantInfo'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' =>
			array(
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);
		return $helper;
	}

	private function initVPOSInfoList()
	{
		$this->fields_list = array(
			'id_vpos' => array(
				'title' => $this->l('Id'),
				'width' => 120,
				'type' => 'text',
			),
			'bank' => array(
				'title' => $this->l('Bank'),
				'width' => 140,
				'type' => 'text'
			),
			'desc' => array(
				'title' => $this->l('Description'),
				'width' => 'auto',
				'type' => 'text'
			),
			'version' => array(
				'title' => $this->l('Version'),
				'width' => 140,
				'type' => 'text'
			),
			'vendor' => array(
				'title' => $this->l('POS Vendor'),
				'width' => 140,
				'type' => 'text'
			),
			'method' => array(
				'title' => $this->l('POS Method'),
				'width' => 140,
				'type' => 'text'
			),
		);

		//if (Shop::isFeatureActive())
		//	$this->fields_list['id_shop'] = array('title' => $this->l('ID Shop'), 'align' => 'center', 'width' => 25, 'type' => 'int');

		$helper = new HelperList();
		$helper->shopLinkType = '';
		$helper->simple_header = true;
		$helper->identifier = 'id_vpos';
		$helper->actions = array('edit', 'delete');
		$helper->show_toolbar = true;
		$helper->imageType = 'jpg';
		$helper->toolbar_btn['new'] =  array(
			'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addVPOSInfo'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'desc' => $this->l('Add new')
		);

		$helper->title = $this->displayName;
		$helper->table = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		return $helper;
	}

	private function initVPOSMerchantInfoList()
	{
		$this->fields_list = array(
			'id_vpos' => array(
				'title' => $this->l('Id'),
				'width' => 120,
				'type' => 'text',
			),
			'description' => array(
				'title' => $this->l('Description'),
				'width' => 140,
				'type' => 'text'
			),
			'vpos_desc' => array(
				'title' => $this->l('Virtual POS Info'),
				'width' => 'auto',
				'type' => 'text'
			),
		);

		//if (Shop::isFeatureActive())
		//	$this->fields_list['id_shop'] = array('title' => $this->l('ID Shop'), 'align' => 'center', 'width' => 25, 'type' => 'int');

		$helper = new HelperList();
		$helper->shopLinkType = '';
		$helper->simple_header = true;
		$helper->identifier = 'id_vpos_merchant';
		$helper->actions = array('edit', 'delete', 'details');
		$helper->show_toolbar = true;
		$helper->imageType = 'jpg';
		$helper->toolbar_btn['new'] =  array(
			'href' => AdminController::$currentIndex.'&configure='.$this->name.'&addVPOSMerchantInfo'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'desc' => $this->l('Add')
		);

		$helper->title = $this->displayName;
		$helper->table = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		return $helper;
	}

	private function _displayContent($message)
	{
		$this->context->smarty->assign(array(
			'message' => $message,
			'MOD_SKELETON_NAME' => Configuration::get('MOD_SKELETON_NAME'),
			'MOD_SKELETON_COLOR' => Configuration::get('MOD_SKELETON_COLOR'),
		));
	}

	private function _checkContent()
	{
		if (!Configuration::get('MOD_SKELETON_NAME') &&
			!Configuration::get('MOD_SKELETON_COLOR'))
			$this->warning = $this->l('You need to configure this module.');
	}

	private function _createContent()
	{
		if (!Db::getInstance()->execute(
			'INSERT INTO `'._DB_PREFIX_.'vpos_vendor` (`name`) '.
				'VALUES ("Garanti"), ("EST")'))
			return false;

		if (!Db::getInstance()->execute(
			'INSERT INTO `'._DB_PREFIX_.'vpos_method` (`name`) '.
				'VALUES ("Normal"), ("3D"), ("3D_Pay")'))
			return false;
			
		if (!Db::getInstance()->execute(
			'INSERT INTO `'._DB_PREFIX_.'vpos_bank` (`name`) '.
				'VALUES ("Garanti"), ("Akbank")'))
			return false;

		return true;
	}

	private function _deleteContent()
	{
		//if (!Configuration::deleteByName('MOD_SKELETON_NAME') ||
		//	!Configuration::deleteByName('MOD_SKELETON_COLOR'))
		//	return false;
		return true;

	}

	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
}

?>
