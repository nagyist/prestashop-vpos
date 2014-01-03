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

class VPOSMerchant extends ObjectModel
{
	/** @var integer VPOS Merchant id */
	public $id;
	
	/** @var string Merchant Info description */
	public $description;

	/** @var string Hash */
	public $hash;

	/** @var integer VPOS reference */
	public $id_vpos;
	
	public $vpos;
	
	/** @var integer is test mode */
	public $mode;

	/** @var integer is default? */
	public $is_default;
	
	/** @var integer is enabled? */
	public $is_enabled;	
		
	/** @var integer 3D Terminal Provision User ID */
	public $vpos_3D_term_prov_user_id;
	
	/** @var integer 3D Terminal ID */
	public $vpos_3D_term_id;
	
	/** @var string 3D Sale Type */
	public $vpos_3D_sale_type;
	
	/** @var string 3D Terminal Merchant ID */
	public $vpos_3D_term_merc_id;

	/** @var string 3D Store Key */
	public $vpos_3D_store_key;

	/** @var string 3D Provision Password */
	public $vpos_3D_provision_password;
	
	/** @var string 3D Success URL */
	public $vpos_3D_success_url;
	
	/** @var string 3D Error URL */
	public $vpos_3D_error_url;

	/** @var string 2 Months Installment */
	public $installment_2m;
	
	/** @var string 3 Months Installment */
	public $installment_3m;
	
	/** @var string 4 Months Installment */
	public $installment_4m;
	
	/** @var string 5 Months Installment */
	public $installment_5m;
	
	/** @var string 6 Months Installment */
	public $installment_6m;
	
	/** @var string 7 Months Installment */
	public $installment_7m;
	
	/** @var string 8 Months Installment */
	public $installment_8m;
	
	/** @var string 9 Months Installment */
	public $installment_9m;
	
	/** @var string 10 Months Installment */
	public $installment_10m;
	
	/** @var string 11 Months Installment */
	public $installment_11m;
	
	/** @var string 12 Months Installment */
	public $installment_12m;
	
	public $installments;
	
	public $VPOSImplementor;
	
	public function __construct($id = null, $id_lang = null, $id_shop = null) {
		parent::__construct($id, $id_lang, $id_shop);
		
		if($this->id > 0) {
			$this->vpos = new VPOS($this->id_vpos);
			$this->installments = $this->getVPOSMerchantInstallmentsArray();
		}
	}
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'vpos_merchant',
		'primary' => 'id_vpos_merchant',
		'multilang' => false,
		'fields' => array(
			'description' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'hash' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'id_vpos' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),			
			'mode' => array('type' => self::TYPE_BOOL, 'validate' => 'isInt', 'required' => true),
			'is_default' => array('type' => self::TYPE_BOOL, 'validate' => 'isInt', 'required' => true),
			'is_enabled' => array('type' => self::TYPE_BOOL, 'validate' => 'isInt', 'required' => true),
			'vpos_3D_term_prov_user_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'vpos_3D_term_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'vpos_3D_sale_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'vpos_3D_term_merc_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'vpos_3D_store_key' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'vpos_3D_provision_password' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),		
			'vpos_3D_success_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),		
			'vpos_3D_error_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
			'installment_2m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_3m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_4m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_5m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_6m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_7m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_8m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_9m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_10m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_11m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
			'installment_12m' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false),
		)
	);


	public function save($null_values = false, $autodate = true) {
		if(strlen($this->hash) == 0)
			$this->hash = sha1(floor(time() / (60*60*24)));
		
		parent::save($null_values, $autodate);
	}

	public static function getVPOSMerchantIdByHash($hash)
	{
		$sql = new DbQuery();
		$sql->select('v.`id_vpos_merchant`');
		$sql->where('v.`hash` = \''.$hash.'\'');
		$sql->from('vpos_merchant', 'v');
		return  Db::getInstance()->executeS($sql)[0]['id_vpos_merchant'];
	}

	public static function getDefaultVPOSMerchantId()
	{
		$sql = new DbQuery();
		$sql->select('v.`id_vpos_merchant`');
		$sql->where('v.`is_default` = 1');
		$sql->from('vpos_merchant', 'v');
		return  Db::getInstance()->executeS($sql)[0]['id_vpos_merchant'];
	}

	public static function getVPOSMerchants()
	{
		$merchants = array();
		$sql = new DbQuery();
		$sql->select('v.`id_vpos_merchant`');
		$sql->from('vpos_merchant', 'v');
		$rows = Db::getInstance()->executeS($sql);
		foreach($rows as $row) {
			$merchants[] = new VPOSMerchant($row['id_vpos_merchant']);
		}
		return $merchants;
	}
	
	private function getVPOSMerchantInstallmentsArray()
	{
		$installments = array();
		
		$installments['2'] = $this->installment_2m;
		$installments['3'] = $this->installment_3m;
		$installments['4'] = $this->installment_4m;
		$installments['5'] = $this->installment_5m;
		$installments['6'] = $this->installment_6m;
		$installments['7'] = $this->installment_7m;
		$installments['8'] = $this->installment_8m;
		$installments['9'] = $this->installment_9m;
		$installments['10'] = $this->installment_10m;
		$installments['11'] = $this->installment_11m;
		$installments['12'] = $this->installment_12m;

		return $installments;
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
