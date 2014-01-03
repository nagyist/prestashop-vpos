<?php


abstract class VPOSInterface {
	const VPOS_RESULT_SUCCESS = 0;
	const VPOS_RESULT_ERROR = 1;
	const VPOS_RESULT_CONNECTION_ERROR = 2;
	const VPOS_RESULT_PROVISION_ERROR = 3;
	const VPOS_RESULT_PROTOCOL_ERROR = 4;
	const VPOS_RESULT_XMLMODEL_ERROR = 5;
	const VPOS_RESULT_3D_ERROR = 6;
	const VPOS_RESULT_3D_METHOD_NOT_SUPPORTED = 7;


	private $instance;
	private $vendor;
	private $method;
	private $bank;
	
	public  $hash;
	
	public abstract function getInstance();
	
	public function getHash() {
		return sha1(VPOSVendor::getTypeAsString($this->vendor).
			VPOSMethod::getTypeAsString($this->method).
			VPOSBank::getTypeAsString($this->bank));
	}

	private function createResult($result, $message) {
		return array('result' => $result, 'msg' => $message );
	}

	public function isVPOSImplemented($vpos) {
		return $this->vendor == $vpos->vpos_vendor->getType() &&
		   	   $this->method == $vpos->vpos_method->getType() &&
		   	   $this->bank == $vpos->vpos_bank->getType();
	}
	
	public abstract function makeProvision($context, $vpos_merchant);
	public abstract function get3DFormData($context, $vpos_merchant);
	public abstract function parse3DResult($data);
}
