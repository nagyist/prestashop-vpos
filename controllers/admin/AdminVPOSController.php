<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once dirname(__FILE__).'/../../classes/VPOS.php';
include_once dirname(__FILE__).'/../../classes/VPOSMerchant.php';

class AdminVPOSController extends AdminController
{
	protected $position_identifier = 'id_vpos';

	public function __construct()
	{
	 	$this->table = 'vpos';
		$this->className = 'VPOS';
	 	$this->lang = false;
		$this->context = Context::getContext();
		$this->multiple_fieldsets = true;
		
		$this->fields_options = array(
			'general' => array(
				'title' => $this->l('Parameters'),
				'fields' => array(
					'PS_VPOS_DEFAULT_BANK_OTHER_CARDS' => array(
						'title' => $this->l('General Settings'),
						'desc' => $this->l('Choose between Yes and No.'),
						'cast' => 'intval',
						'type' => 'select',
						'list' => array(),
						'empty' => $this->l('Select a bank')
					),
				)
			)
		);

		$this->fields_list = array(
			'id_vpos' => array(
				'title' => $this->l('Id'),
				'width' => 120,
				'type' => 'text',
			),
			'desc' => array(
				'title' => $this->l('Description'),
				'width' => 'auto',
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
			'version' => array(
				'title' => $this->l('Version'),
				'width' => 140,
				'type' => 'text'
			),
			'bank' => array(
				'title' => $this->l('Bank'),
				'width' => 140,
				'type' => 'text'
			),
		);
		
	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 
	 								'confirm' => $this->l('Delete selected items?')));

		parent::__construct();
	}

	/**
	 * AdminController::setMedia() override
	 * @see AdminController::setMedia()
	 */
	public function setMedia()
	{
		parent::setMedia();

		$this->addJqueryPlugin('fieldselection');
	} 

	/**
	 * AdminController::renderList() override
	 * @see AdminController::renderList()
	 */
	public function renderList()
	{
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->addRowAction('details');
	 	//$this->_defaultOrderBy = 'position';

	 	// Added specific button in toolbar
	 	$this->toolbar_btn['newAttributes'] = array(
			'href' => self::$currentIndex.'&amp;addvpos_merchant&amp;token='.$this->token,
			'desc' => $this->l('Add VPOS Merchant')
		);

	 	$this->toolbar_btn['new'] = array(
			'href' => self::$currentIndex.'&amp;addvpos&amp;token='.$this->token,
			'desc' => $this->l('Add VPOS')
		);

		return parent::renderList();
	}

	public function renderOptions()
	{
		return parent::renderOptions();
	}
	
	/**
	 * Change object type to feature value (use when processing a feature value)
	 */
	protected function setTypeVPOSMerchant()
	{
		$this->table = 'vpos_merchant';
		$this->className = 'VPOSMerchant';
		$this->identifier = 'id_vpos_merchant';
		
		$this->_select = 'a.`id_vpos_merchant`, a.`description`, a.`vpos_3D_term_prov_user_id`, v.`desc` vpos_desc';
		$this->_join = 'LEFT JOIN '._DB_PREFIX_.'vpos v on v.`id_vpos` = a.`id_vpos` ';
	}

	/**
	 * Change object type to feature (use when processing a feature)
	 */
	protected function setTypeVPOS()
	{
		$this->table = 'vpos';
		$this->className = 'VPOS';
		$this->identifier = 'id_vpos';
		
		$this->_select = 'a.`id_vpos`, a.`desc`, a.`version`, t.`name` vendor, n.`name` bank, m.`name` method';
		$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'vpos_vendor t on t.`id_vpos_vendor` = a.`id_vpos_vendor` ';
		$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'vpos_bank n on n.`id_vpos_bank` = a.`id_vpos_bank` ';
		$this->_join .= 'LEFT JOIN '._DB_PREFIX_.'vpos_method m on m.`id_vpos_method` = a.`id_vpos_method`';
	}

	/**
	 * method call when ajax request is made with the details row action
	 * @see AdminController::postProcess()
	 */
	public function ajaxProcessDetails()
	{
		if (($id = Tools::getValue('id')))
		{
		
			$this->setTypeVPOSMerchant();
			$this->lang = false;

			// override attributes
			$this->display = 'list';

			// Action for list
			$this->addRowAction('edit');
			$this->addRowAction('delete');

			if (!Validate::isLoadedObject($obj = new VPOS((int)$id)))
				$this->errors[] = Tools::displayError('An error occurred while updating the status for an object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');

			$this->fields_list = array(
				'id_vpos_merchant' => array(
					'title' => $this->l('ID'),
					'width' => 25
				),
				'description' => array(
					'title' => $this->l('Description')
				)
			);

			$this->_where = sprintf('AND a.`id_vpos` = %d', (int)$id);

			// get list and force no limit clause in the request
			$this->getList($this->context->language->id);

			// Render list
			$helper = new HelperList();
			$helper->actions = $this->actions;
			$helper->no_link = true;
			$helper->shopLinkType = '';
			$helper->identifier = $this->identifier;
			$helper->toolbar_scroll = false;
			$helper->orderBy = 'position';
			$helper->orderWay = 'ASC';
			$helper->currentIndex = self::$currentIndex;
			$helper->token = $this->token;
			$helper->table = $this->table;
			$helper->simple_header = true;
			$helper->show_toolbar = false;
			$helper->bulk_actions = $this->bulk_actions;
			$content = $helper->generateList($this->_list, $this->fields_list);

			echo Tools::jsonEncode(array('use_parent_structure' => false, 'data' => $content));
			exit;
		}
	}

	/**
	 * AdminController::renderForm() override
	 * @see AdminController::renderForm()
	 */
	public function renderForm()
	{
		$this->toolbar_title = $this->l('Add a new Virtual POS Setting');
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Virtual POS Settings'),
			),
			'input' => array(
				array(
					'type' => 'select',
					'label' => $this->l('Bank'),
					'name' => 'vpos_bank',
					'options' => array(
        				'query' => VPOS::getBankListContent(),    
        				'id' => 'id_vpos_bank',
        				'name' => 'name'
      				),
				),
				array(
					'type' => 'select',
					'label' => $this->l('Virtual POS Vendor'),
					'name' => 'vpos_vendor',
					'options' => array(
        				'query' => VPOS::getPOSVendorListContent(),
        				'id' => 'id_vpos_vendor',
        				'name' => 'name'
      				),
				),
				array(
					'type' => 'select',
					'label' => $this->l('Virtual POS Method:'),
					'name' => 'vpos_method',
					'options' => array(
        				'query' => VPOS::getPOSMethodListContent(),
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

		return parent::renderForm();
	}

	/**
	 * AdminController::initToolbar() override
	 * @see AdminController::initToolbar()
	 */
	public function initToolbar()
	{
		switch ($this->display)
		{
			case 'editVPOSMerchant':
			case 'add':
			case 'edit':
				$this->toolbar_btn['save'] = array(
					'href' => '#',
					'desc' => $this->l('Save')
				);

				// Default cancel button - like old back link
				$back = Tools::safeOutput(Tools::getValue('back', ''));
				if (empty($back))
					$back = self::$currentIndex.'&token='.$this->token;

				$this->toolbar_btn['back'] = array(
					'href' => $back,
					'desc' => $this->l('Back to the list')
				);
			break;

			default:
				parent::initToolbar();
		}
	}

	/**
	 * AdminController::renderForm() override
	 * @see AdminController::renderForm()
	 */
	protected function initVPOSMerchantInfoForm()
	{
		$this->setTypeVPOSMerchant();
		
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Virtual POS  Merchant Settings'),
			),
			'input' => array(
				array(
					'type' => 'select',
					'label' => $this->l('Virtual POS Entry'),
					'name' => 'id_vpos',
					'options' => array(
        				'query' => VPOS::getVPOSs(),    
        				'id' => 'id_vpos',
        				'name' => 'desc'
      				),
				),
				array(
					'type' => 'text',
					'label' => $this->l('Description'),
					'name' => 'description',
					'size' => 40
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Test Mode On?'),
					'name' => 'mode',
					'is_bool'   => true,
					'class' => 't',
					'required'  => true,
					'values'    => array(
										array(
										  'id'    => 'active_on',
										  'value' => 0,
										  'label' => $this->l('Yes'),
										  'align' => 'left',
										),
										array(
										  'id'    => 'active_off',
										  'value' => 1,
										  'label' => $this->l('No'),
										  'align' => 'left',
										)
									  ),
					'align' => 'left',
				),
				array(
					'type' => 'radio',
					'label' => $this->l('Is default entry?'),
					'name' => 'is_default',
					'is_bool'   => true,
					'class' => 't',
					'values'    => array(
										array(
										  'id'    => 'active_on',
										  'value' => 1,
										  'label' => $this->l('Yes'),
										  'align' => 'left',
										),
										array(
										  'id'    => 'active_off',
										  'value' => 0,
										  'label' => $this->l('No'),
										  'align' => 'left',
										)
									  ),
					'align' => 'left',
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
		
		$this->fields_form[1]['form'] = array(
			'legend' => array(
				'title' => $this->l('Installment Settings'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('2 Months Installment Rate %'),
					'name' => 'installment_2m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('3 Months Installment Rate %'),
					'name' => 'installment_3m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('4 Months Installment Rate %'),
					'name' => 'installment_4m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('5 Months Installment Rate %'),
					'name' => 'installment_5m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('6 Months Installment Rate %'),
					'name' => 'installment_6m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('7 Months Installment Rate %'),
					'name' => 'installment_7m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('8 Months Installment Rate %'),
					'name' => 'installment_8m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('9 Months Installment Rate %'),
					'name' => 'installment_9m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('10 Months Installment Rate %'),
					'name' => 'installment_10m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('11 Months Installment Rate %'),
					'name' => 'installment_11m',
					'size' => 3,
					'required' => false,
				),
				array(
					'type' => 'text',
					'label' => $this->l('12 Months Installment Rate %'),
					'name' => 'installment_12m',
					'size' => 3,
					'required' => false,
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);
		
		$vpos_merchant = new VPOSMerchant(Tools::getValue('id_vpos_merchant'));

		
		$helper = new HelperForm();
		$helper->module = $this;
		$helper->currentIndex = self::$currentIndex;
		$helper->token = $this->token;
		$helper->table = $this->table;
		$helper->identifier = $this->identifier;
		$helper->id = $vpos_merchant->id;
		$helper->toolbar_scroll = false;
		$helper->tpl_vars = $this->tpl_vars;
		$helper->languages = $this->_languages;
		$helper->default_form_language = $this->default_form_language;
		$helper->allow_employee_form_lang = $this->allow_employee_form_lang;
		$helper->fields_value = $this->getFieldsValue($vpos_merchant);
		$helper->toolbar_btn = $this->toolbar_btn;
		$helper->toolbar_btn =  $this->toolbar_btn;
		$helper->title = $this->l('Add a new VPOS Merchant Info');
		$this->content .= $helper->generateForm($this->fields_form);
	}

	/**
	 * AdminController::initContent() override
	 * @see AdminController::initContent()
	 */
	public function initContent()
	{
			// toolbar (save, cancel, new, ..)
			$this->initToolbar();
			if ($this->display == 'edit' || $this->display == 'add')
			{
				if (!$this->loadObject(true))
					return;
				$this->content .= $this->renderForm();
			}
			else if ($this->display == 'view')
			{
				// Some controllers use the view action without an object
				if ($this->className)
					$this->loadObject(true);
				$this->content .= $this->renderView();
			}
			else if ($this->display == 'editVPOSMerchant')
			{
				if (!$this->object = new VPOSMerchant((int)Tools::getValue('id_vpos_merchant')))
					return;
				$this->content .= $this->initVPOSMerchantInfoForm();
			}
			else if (!$this->ajax)
			{
				// If a feature value was saved, we need to reset the values to display the list
				$this->setTypeVPOS();
				$this->content .= $this->renderList();
				$this->content .= $this->renderOptions();
			}
		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,
		));
	}

	public function initProcess()
	{
		// Are we working on VPOS Merchant?
		if (Tools::getValue('id_vpos_merchant')
			|| Tools::isSubmit('deletevpos_merchant')
			|| Tools::isSubmit('submitAddvpos_merchant')
			|| Tools::isSubmit('addvpos_merchant')
			|| Tools::isSubmit('updatevpos_merchant')
			|| Tools::isSubmit('submitBulkvpos_merchant'))
			$this->setTypeVPOSMerchant();

		parent::initProcess();

	}

	public function postProcess()
	{
		parent::postProcess();
		if ($this->table == 'vpos_merchant' && ($this->display == 'edit' || $this->display == 'add'))
			$this->display = 'editVPOSMerchant';
	}

	/**
	 * Override processAdd to change SaveAndStay button action
	 * @see classes/AdminControllerCore::processAdd()
	 */
	public function processAdd()
	{
		$object = parent::processAdd();

		if (Tools::isSubmit('submitAdd'.$this->table.'AndStay') && !count($this->errors))
			$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'=&conf=3&update'.$this->table.'&token='.$this->token;
		elseif (Tools::isSubmit('submitAdd'.$this->table.'AndStay') && count($this->errors))
			$this->display = 'editVPOSMerchant';

		return $object;
	}

	/**
	 * Override processUpdate to change SaveAndStay button action
	 * @see classes/AdminControllerCore::processUpdate()
	 */
	public function processUpdate()
	{
		$object = parent::processUpdate();

		if (Tools::isSubmit('submitAdd'.$this->table.'AndStay') && !count($this->errors))
			$this->redirect_after = self::$currentIndex.'&'.$this->identifier.'=&conf=3&update'.$this->table.'&token='.$this->token;
		
		return $object;
	}

	/**
	 * Call the right method for creating or updating object
	 *
	 * @return mixed
	 */
	public function processSave()
	{
		return parent::processSave();
	}

	/**
	 * AdminController::getList() override
	 * @see AdminController::getList()
	 */
	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
	}

	public function ajaxProcessUpdatePositions()
	{
		if ($this->tabAccess['edit'] === '1')
		{
			$way = (int)Tools::getValue('way');
			$id_feature = (int)Tools::getValue('id');
			$positions = Tools::getValue('feature');

			$new_positions = array();
			foreach ($positions as $k => $v)
				if (!empty($v))
					$new_positions[] = $v;

			foreach ($new_positions as $position => $value)
			{
				$pos = explode('_', $value);

				if (isset($pos[2]) && (int)$pos[2] === $id_feature)
				{
					if ($feature = new Feature((int)$pos[2]))
						if (isset($position) && $feature->updatePosition($way, $position, $id_feature))
							echo 'ok position '.(int)$position.' for feature '.(int)$pos[1].'\r\n';
						else
							echo '{"hasError" : true, "errors" : "Can not update feature '.(int)$id_feature.' to position '.(int)$position.' "}';
					else
						echo '{"hasError" : true, "errors" : "This feature ('.(int)$id_feature.') can t be loaded"}';

					break;
				}
			}
		}
	}
}
