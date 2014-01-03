<?php


abstract class VPOSInterface {
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

	public function isVPOSImplemented($vpos) {
		return $this->vendor == $vpos->vpos_vendor->getType() &&
		   	   $this->method == $vpos->vpos_method->getType() &&
		   	   $this->bank == $vpos->vpos_bank->getType();
	}
	
	public abstract function makeProvision($context, $vpos_merchant);
	public abstract function get3DFormData($context, $vpos_merchant);
	public abstract function parse3DResult($data);
}