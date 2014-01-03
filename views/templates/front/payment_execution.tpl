{*
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
*}

<script type="text/javascript">
	$(document).ready(function() {ldelim}
		$("a#popup").fancybox({ldelim}
			'hideOnContentClick': false
		{rdelim});
		
		//$.form('asd', 'bankpopup', {ldelim} {$_3D_post_data} {rdelim}, 'POST');
//$("a#bankpopup").click();
	{rdelim});
</script>

{capture name=path}{l s='Bank-wire payment.' mod='bankwire'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='bankwire'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='skeleton'}</p>
{else}

<h3>{l s='Credit Card Payment.' mod='skeleton'}</h3>
<form action="{$link->getModuleLink('skeleton', 'validation', [], true)}" method="post">
	<p>
		<img src="{$this_path}bankwire.jpg" alt="{l s='Bank wire' mod='bankwire'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
		{l s='You have chosen to pay by bank wire.' mod='bankwire'}
		<br/><br />
		{l s='Here is a short summary of your order:' mod='bankwire'}
	</p>
	<p style="margin-top:20px;">
		- {l s='The total amount of your order is' mod='bankwire'}
		<span id="amount" class="price">{displayPrice price=$total}</span>
		{if $use_taxes == 1}
			{l s='(tax incl.)' mod='bankwire'}
		{/if}
	</p>
	<p>
		Credit Card Number: <input name="cardnumber" type="text" value="{$cardnumber}"/>
		<br />
		Expire Date (mm): <input name="cardexpiredatemonth" type="text" value="{$cardexpiredatemonth}"/>
		<br />
		Expire Date (yy): <input name="cardexpiredateyear" type="text" value="{$cardexpiredateyear}"/>
		<br />
		CVV2: <input name="cardcvv2" type="text" value="{$cardcvv2}"/>
		<br />
		
{if $vpos_merchants|@count > 1}
	{foreach from=$vpos_merchants item=merchant}
		<div class="vpos-bank">
			<div class="vpos-bank-name">
		{$merchant->vpos->vpos_bank->name}
			</div>
		{foreach from=$merchant->installments key=k item=v}
			{if $v != 0}
			<div class="vpos-installment">
				<input name="installment" type="radio" value="{$merchant->hash}:{$k}"/>
				<div class="vpos-installment-count"> {$k} {l s='Taksit' mod='skeleton'} </div>
				<div class="vpos-installment-amount"> {($total * (100 + $v) / 100 / $k)|round:2} </div>
				<div class="vpos-installment-total-amount"> {($total * (100 + $v) / 100)|round:2} </div>
			</div>
			{/if}
		{/foreach}
		</div>
	{/foreach}
{else}
		<input name="installment" type="hidden" value="{$merchant->hash}:0"/>
{/if}

		{if $currencies|@count > 1}
			{l s='We allow several currencies to be sent via bank wire.' mod='bankwire'}
			<br /><br />
			{l s='Choose one of the following:' mod='bankwire'}
			<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
				{foreach from=$currencies item=currency}
					<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
				{/foreach}
			</select>
		{else}
			{l s='We allow the following currency to be sent via bank wire:' mod='bankwire'}&nbsp;<b>{$currencies.0.name}</b>
			<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
		{/if}
	</p>
	<p>
		{l s='Bank wire account information will be displayed on the next page.' mod='bankwire'}
		<br /><br />
		<b>{l s='Please confirm your order by clicking "Place my order."' mod='bankwire'}.</b>
	</p>
	<p class="cart_navigation">
		<input type="submit" name="submit" id="submit" value="{l s='Place my order' mod='skeleton'}" class="exclusive_large" />
		<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='bankwire'}</a>
	</p>
</form>
{/if}
