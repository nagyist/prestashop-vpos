<?php

include_once dirname(__FILE__).'/vendor/GarantiVPOS.php';

class VPOSManager {
	private static $vpos_manager;
	private $implementors = array();
	
	private function __construct() {
		$this->implementors[GarantiVPOS::getInstance()->getHash()] = GarantiVPOS::getInstance();
	}
	
	public function getInstance() {
		if($vpos_manager == null)
			$vpos_manager = new VPOSManager();
		return $vpos_manager;
	}
	
	public function getImplementorByVPOS($vpos) {
		foreach($this->implementors as $implementor) {
			if($implementor->isVPOSImplemented($vpos)) {
				return $implementor;
			}
		}
	}
	
	public function getImplementorByHash($hash) {
		return $this->implementors[$hash];
	}
}