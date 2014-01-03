
<form id="test" method="POST" action="{$gateway_url}" style="display: none;">
	<input type="hidden" name="cardnumber" value="{$cardnumber}">
	<input type="hidden" name="cardexpiredatemonth" value="{$cardexpiredatemonth}">
	<input type="hidden" name="cardexpiredateyear" value="{$cardexpiredateyear}">
	<input type="hidden" name="cardcvv2" value="{$cardcvv2}">
	<input type="hidden" name="mode" value="{$mode}">
	<input type="hidden" name="apiversion" value="{$apiversion}">
	<input type="hidden" name="terminalprovuserid" value="{$terminalprovuserid}">
	<input type="hidden" name="terminaluserid" value="{$terminaluserid}">
	<input type="hidden" name="terminalmerchantid" value="{$terminalmerchantid}">
	<input type="hidden" name="txntype" value="{$txntype}">
	<input type="hidden" name="txnamount" value="{$txnamount}">
	<input type="hidden" name="txncurrencycode" value="{$txncurrencycode}">
	<input type="hidden" name="txninstallmentcount" value="{$txninstallmentcount}">
	<input type="hidden" name="orderid" value="{$orderid}">
	<input type="hidden" name="terminalid" value="{$terminalid}">
	<input type="hidden" name="successurl" value="{$successurl}">
	<input type="hidden" name="errorurl" value="{$errorurl}">
	<input type="hidden" name="customeripaddress" value="{$customeripaddress}">
	<input type="hidden" name="customeremailaddress" value="{$customeremailaddress}">
	<input type="hidden" name="secure3dsecuritylevel" value="{$secure3dsecuritylevel}">
	<input type="hidden" name="secure3dhash" value="{$secure3dhash}">
</form>