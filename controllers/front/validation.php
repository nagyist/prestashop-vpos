<?php

include_once dirname(__FILE__).'/../../classes/vendor/VPOSInterface.php';
include_once dirname(__FILE__).'/../../classes/VPOS.php';
include_once dirname(__FILE__).'/../../classes/VPOSMerchant.php';

class skeletonValidationModuleFrontController extends ModuleFrontController {

	public function initContent() {
		$this->display_column_left = false;
		$this->display_column_right = false;

		parent::initContent();
	}
	

	private function endPaymentAndRedirect($vpos_merchant) {
			$result = $vpos_merchant->vpos->implementor->makeProvision($this->context, $vpos_merchant);

			$currency = $this->context->currency;
			$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
			if($result['result'] == VPOSInterface::VPOS_RESULT_SUCCESS) {
				$this->module->validateOrder($cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, NULL, array(), (int)$currency->id, false, $customer->secure_key);
				Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
			}
			else {
				$this->module->validateOrder($cart->id, Configuration::get('PS_OS_ERROR'), $total, $this->module->displayName, NULL, array(), (int)$currency->id, false, $customer->secure_key);
				Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
			}
	}

	public function postProcess() {
		$vpos_merchant = null;
		$cart = $this->context->cart;
		$customer = $this->context->customer;

		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		/* Last Step For 3D */
		$vpos_hash = Tools::getValue('merchant'); // todo also can check IP
		if(!empty($vpos_hash)) {
			$vpos_merchant = new VPOSMerchant(VPOSMerchant::getVPOSMerchantIdByHash($vpos_hash));
			endPaymentAndRedirect($vpos_merchant);			
		}

		/* 1st Step, Credit Card and Installment info is sent */
		$vpos_merchant_hash_arr = explode(':', Tools::getValue('installment'));
		if(count($vpos_merchant_hash_arr) != 2){
			Tools::redirect('index.php?controller=order&step=1');
		}
		
		/* Find VPOS Merchant Info */
		$vpos_merchant = new VPOSMerchant(VPOSMerchant::getVPOSMerchantIdByHash($vpos_merchant_hash_arr[0]));
		if(empty($vpos_merchant->id)) {			
			Tools::redirect('index.php?controller=order&step=1');
		}

		if($vpos_merchant->vpos->vpos_method->isType3D()) {
			$vpos_merchant->vpos_3D_success_url = $this->context->link->getModuleLink('skeleton', 'validation', ['merchant'=>$vpos_merchant->hash], true);
			$vpos_merchant->vpos_3D_error_url = $this->context->link->getModuleLink('skeleton', 'validation', ['merchant'=>$vpos_merchant->hash], true);

			/* Get 3D Form data */
			$form_data = $vpos_merchant->vpos->implementor->get3DFormData($this->context, $vpos_merchant);
			if(empty($form_data)) {
				Tools::redirect('index.php?controller=order&step=1');
			}
			$_3D_post_data_arr = array();
			foreach ($form_data as $key => $value) {
				$_3D_post_data_arr[] = $key . ' : \'' . $value . '\'';
			}

			$_3D_post_data = implode(', ', $_3D_post_data);
			if(empty($_3D_post_data)) {
				Tools::redirect('index.php?controller=order&step=1');
			}

			/* Get 3D Form target */
			$_3D_post_target = $vpos_merchant->vpos->implementor->get3DFormServer($this->context, $vpos_merchant);
			if(empty($_3D_post_target)) {
				Tools::redirect('index.php?controller=order&step=1');
			}
			
			$this->context->smarty->assign(array(
						'vpos_merchant' => $vpos_merchant,
						'is3D' => 1,
						'_3D_post_target' => $_3D_post_target,
						'_3D_post_data' => $_3D_post_data,
						));

			$this->setTemplate('validation.tpl');
		}
		else {
			/* Make provision */
			endPaymentAndRedirect($vpos_merchant);
		}
	}

}
