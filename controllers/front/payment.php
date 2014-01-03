<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
include_once dirname(__FILE__).'/../../classes/VPOS.php';
include_once dirname(__FILE__).'/../../classes/VPOSMerchant.php';
include_once dirname(__FILE__).'/../../classes/VPOSInstallment.php';

class skeletonPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();

		$cart = $this->context->cart;
		$customer = $this->context->customer;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
			
		$vpos_merchants = VPOSMerchant::getVPOSMerchants();
		
		$this->context->smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->module->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
			'vpos_merchants' => $vpos_merchants,
			'cardnumber' => '4282209004348015',
			'cardexpiredateyear' => '15',
			'cardexpiredatemonth' => '02',
			'cardcvv2' => '123'
			//'cardnumber' => '4282209027132016',
			//'cardexpiredateyear' => '15',
			//'cardexpiredatemonth' => '05',
			//'cardcvv2' => '232'
			//'cardnumber' => '4050904902128481',
			//'cardexpiredateyear' => '15',
			//'cardexpiredatemonth' => '05',
			//'cardcvv2' => '232'

		));
		
		$this->setTemplate('payment_execution.tpl');

	}

}
