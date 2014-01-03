<?php

include_once dirname(__FILE__).'/../../classes/VPOS.php';
include_once dirname(__FILE__).'/../../classes/VPOSMerchant.php';

class skeletonValidationModuleFrontController extends ModuleFrontController {

	public function initContent() {
		$this->display_column_left = false;
		$this->display_column_right = false;

		parent::initContent();
	}
	
	public function postProcess() {
		
		$cart = $this->context->cart;

		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		$vpos_hash = Tools::getValue('merchant');
		if(isset($vpos_hash) && strlen($vpos_hash)) {
			//var_dump($_REQUEST);
			$vpos_merchant = new VPOSMerchant(VPOSMerchant::getVPOSMerchantIdByHash($vpos_hash));
			die(print_r($vpos_merchant->vpos->implementor->makeProvision($this->context, $vpos_merchant)));
			//todo get result and redirect to validation.tpl
			
		}

		$customer = $this->context->customer;
		$vpos_merchant_hash_arr = explode(':', Tools::getValue('installment'));

		if(count($vpos_merchant_hash_arr) != 2){
			Tools::redirect('index.php?controller=order&step=1');
		}
		
		// Find VPOS Implementation Class and $vpos_merchant
		$vpos_merchant = new VPOSMerchant(VPOSMerchant::getVPOSMerchantIdByHash($vpos_merchant_hash_arr[0]));
		if(!$vpos_merchant->id) {			
			Tools::redirect('index.php?controller=order&step=1');
		}

		if($vpos_merchant->vpos->vpos_method->isType3D()) {
			$vpos_merchant->vpos_3D_success_url = $this->context->link->getModuleLink('skeleton', 'validation', ['merchant'=>$vpos_merchant->hash], true);
			$vpos_merchant->vpos_3D_error_url = $this->context->link->getModuleLink('skeleton', 'validation', ['merchant'=>$vpos_merchant->hash], true);

			$form_data = $vpos_merchant->vpos->implementor->get3DFormData($this->context, $vpos_merchant);
			if(!isset($form_data)) {
				Tools::redirect('index.php?controller=order&step=1');
			}

			$_3D_post_target = $vpos_merchant->vpos->implementor->get3DFormServer($this->context, $vpos_merchant);			
			
			$_3D_post_data = array();
			foreach ($form_data as $key => $value) {
				$_3D_post_data[] = $key . ' : \'' . $value . '\'';
			}

			$this->context->smarty->assign(array(
						'vpos_merchant' => $vpos_merchant,
						'is3D' => 1,
						'_3D_post_target' => $_3D_post_target,
						'_3D_post_data' => implode(', ', $_3D_post_data),
						));
		}
		else {
			$result = $implementor->makeProvision($this->context, $vpos_merchant);
			$this->context->smarty->assign(array(
					'vpos_merchant' => $vpos_merchant,
					'is3D' => 0,
					'result' => $result
					));
		}

		$this->setTemplate('validation.tpl');
	}

}
