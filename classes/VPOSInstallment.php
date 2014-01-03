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

class VPOSInstallment extends ObjectModel
{
	/** @var integer VPOS Installment id */
	public $id_installment;

	/** @var integer VPOS Merchant reference */
	public $id_vpos_merchant;

	/** @var integer installment count, unique with merchant reference */
	public $installment_count;
		
	/** @var float installment rate */
	public $installment_rate;
	
	/** @var boolean is rate or amount, default 1 */
	public $is_rate;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'vpos_merchant_installment',
		'primary' => 'id',
		'multilang' => false,
		'fields' => array(
			'id_installment' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'id_vpos_merchant' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'installment_count' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'installment_rate' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
			'is_rate' => array('type' => self::TYPE_BOOL, 'validate' => 'isInt', 'required' => true),
		)
	);

	public static function getVPOSInstallmentsByMerchantId($vpos_merchant)
	{
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from('vpos_merchant_installment', 'i');
		$sql->where('i.`id_vpos_merchant`='. $vpos_merchant);
		return  Db::getInstance()->executeS($sql);
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
