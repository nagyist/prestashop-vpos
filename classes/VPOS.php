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

include_once dirname(__FILE__).'/VPOSManager.php';

class VPOSBank extends ObjectModel
{
	const VPOS_BANK_GARANTI  = 0;
	const VPOS_BANK_AKBANK  = 1;
	public static $BANK_TYPES = array('Garanti' => VPOS_BANK_GARANTI,
				             'Akbank' => VPOS_BANK_AKBANK);
	         
	public $id_vpos_bank;
	public $name;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'vpos_bank',
		'primary' => 'id_vpos_bank',
		'multilang' => false,
		'fields' => array(
			'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
		)
	);
	
	public function getType() {
		return VPOSBank::$BANK_TYPES[$this->name];
	}

	public static function getTypeAsString($type) {
		foreach(VPOSBank::$BANK_TYPES as $k=>$v) {
			if($v == $type)
				return $k;
		}
	}
}

class VPOSVendor extends ObjectModel
{
	const VPOS_VENDOR_EST = 0;
	const VPOS_VENDOR_GARANTI  = 1;
	public static $VENDOR_TYPES = array('EST' => VPOS_VENDOR_EST,
				                        'Garanti'=> VPOS_VENDOR_GARANTI);
	
	
	public $id_vpos_vendor;
	public $name;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'vpos_vendor',
		'primary' => 'id_vpos_vendor',
		'multilang' => false,
		'fields' => array(
			'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
		)
	);
	
	public function getType() {
		return VPOSVendor::$VENDOR_TYPES[$this->name];
	}
	
	public static function getTypeAsString($type) {
		foreach(VPOSVendor::$VENDOR_TYPES as $k=>$v) {
			if($v == $type)
				return $k;
		}
	}
}

class VPOSMethod extends ObjectModel
{
	const VPOS_METHOD_STANDART = 0;
	const VPOS_METHOD_3D       = 1;
	const VPOS_METHOD_3D_PAY   = 2;
	const VPOS_METHOD_3D_FULL  = 3;
	const VPOS_METHOD_3D_HALF  = 4;
	public static $METHOD_TYPES = array('Standart' => VPOS_METHOD_STANDART,
				         				'3D' => VPOS_METHOD_3D,
				         				'3D_PAY' => VPOS_METHOD_3D_PAY,
				         				'3D_FULL' => VPOS_METHOD_3D_FULL,
				         				'3D_HALF' => VPOS_METHOD_3D_HALF,);
				         
	public $id_vpos_method;
	public $name;
	
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'vpos_method',
		'primary' => 'id_vpos_method',
		'multilang' => false,
		'fields' => array(
			'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
		)
	);
	
	public function getType() {
		return VPOSMethod::$METHOD_TYPES[$this->name];
	}
	
	public static function getTypeAsString($type) {
		foreach(VPOSMethod::$METHOD_TYPES as $k=>$v) {
			if($v == $type)
				return $k;
		}
	}
	
	public function isType3D() {
		$type = $this->getType();
		return ( $type == VPOS_METHOD_3D ||
				$type == VPOS_METHOD_3D_PAY ||
				$type == VPOS_METHOD_3D_FULL ||
				$type == VPOS_METHOD_3D_HALF );
	}
}

class VPOS extends ObjectModel
{
	/** @var integer VPOS id*/
	public $id;
	
	/** @var string VPOSInfo text*/
	public $desc;

	/** @var string VPOSInfo text*/
	public $version;

	/** @var integer VPOS Bank */
	public $id_vpos_bank;
	
	public $vpos_bank;
		
	/** @var integer VPOS POS Vendor */
	public $id_vpos_vendor;
	
	public $vpos_vendor;
	
	/** @var integer VPOS POS Method */
	public $id_vpos_method;
	
	public $vpos_method;
	
	/** @var string VPOS Gateway URL */
	public $vpos_3D_gateway_url;
	
	/** @var string VPOS Gateway Test URL */
	public $vpos_3D_gateway_test_url;

	/** @var string VPOS Cancel URL */
	public $vpos_3D_gateway_cancel_url;

	/** @var string VPOS Refund URL */
	public $vpos_3D_gateway_refund_url;

	public function __construct($id = null, $id_lang = null, $id_shop = null) {
		parent::__construct($id, $id_lang, $id_shop);
		
		if($this->id) {
			$this->vpos_bank = new VPOSBank($this->id_vpos_bank);
			$this->vpos_vendor = new VPOSVendor($this->id_vpos_vendor);
			$this->vpos_method = new VPOSMethod($this->id_vpos_method);
			$this->implementor = VPOSManager::getInstance()->getImplementorByVPOS($this);
		}
	}
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'vpos',
		'primary' => 'id_vpos',
		'multilang' => false,
		'fields' => array(
			'desc' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'version' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'id_vpos_bank' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'id_vpos_vendor' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'id_vpos_method' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'vpos_3D_gateway_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'vpos_3D_gateway_test_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'vpos_3D_gateway_cancel_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'vpos_3D_gateway_refund_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),		
		)
	);

	public static function getVPOS($id_lang, $id_vpos)
	{
		return Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'feature` f
			LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl
				ON ( f.`id_feature` = fl.`id_feature` AND fl.`id_lang` = '.(int)$id_lang.')
			WHERE f.`id_feature` = '.(int)$id_feature
		);
	}
	
	public static function getVPOSs()
	{
		$sql = new DbQuery();
		$sql->select('v.`id_vpos`, v.`desc`, v.`version`, t.`name` vendor, b.`name` bank, m.`name` method');
		$sql->leftjoin('vpos_vendor', 't', 't.`id_vpos_vendor` = v.`id_vpos_vendor`');
		$sql->leftjoin('vpos_bank', 'b', 'b.`id_vpos_bank` = v.`id_vpos_bank`');
		$sql->leftjoin('vpos_method', 'm', 'm.`id_vpos_method` = v.`id_vpos_method`');
		$sql->from('vpos', 'v');
		return  Db::getInstance()->executeS($sql);
	}
	
	public static function getBankListContent()
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('vpos_bank', 'b');
		return  Db::getInstance()->executeS($sql);
	}
	
	public static function getPOSVendorListContent()
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('vpos_vendor', 'v');
		return  Db::getInstance()->executeS($sql);
	}
	
	public static function getPOSMethodListContent()
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('vpos_method', 'm');
		return  Db::getInstance()->executeS($sql);
	}
	
	public static function getVPOSMerchantListContent($id_lang)
	{
		$sql = new DbQuery();
		$sql->select('v.`id_vpos_merchant`, v.`desc`, t.`desc` vpos_desc');
		$sql->leftjoin('vpos', 't', 't.`id_vpos` = v.`id_vpos`');
		$sql->from('vpos_merchant', 'v');
		return  Db::getInstance()->executeS($sql);
	}
	
	public function getMethodName()
	{
		$sql = new DbQuery();
		$sql->select('v.`name`');
		$sql->from('vpos_method', 'v');
		$sql->where('v.`id_vpos_method`='.(int)$this->vpos_method);
		return Db::getInstance()->executeS($sql)[0]['name'];
	}
	
	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST AS $key => $value) {
			if (key_exists($key, $this) AND $key != 'id_'.$this->table) {
				$this->{$key} = $value;
			}
		}
	}
}