

{if !$opc}
	{assign var='current_step' value='address'}
	{capture name=path}{l s='Addresses'}{/capture}
	{assign var="back_order_page" value="order.php"}

	<h1 class="gk-align-C">{l s='ОФОРМЛЕНИЕ ЗАКАЗА'}</h1>

	{include file="$tpl_dir./order-steps.tpl"}

	<h2 class="blk gk-align-C">{l s='Доставка'}</h2>

	{include file="$tpl_dir./errors.tpl"}
		<form action="{$link->getPageLink($back_order_page, true)|escape:'html':'UTF-8'}" method="post">
{else}
	{assign var="back_order_page" value="order-opc.php"}
	<h1 class="page-heading step-num"><span>1</span> {l s='Addresses'}</h1>
	<div id="opc_account" class="opc-main-block">
		<div id="opc_account-overlay" class="opc-overlay" style="display: none;"></div>
{/if}
<div class="col-xs-12 col-sm-6">
    <div class="address_delivery select form-group selector1">
            <label for="id_address_delivery">{if $cart->isVirtualCart()}{l s='Choose a billing address:'}{else}{l s='Choose a delivery address:'}{/if}</label>
            <select name="id_address_delivery" id="id_address_delivery" class="address_select form-control">
                    {foreach from=$addresses key=k item=address}
                            <option value="{$address.id_address|intval}"{if $address.id_address == $cart->id_address_delivery} selected="selected"{/if}>
                                    {$address.alias|escape:'html':'UTF-8'}
                            </option>
                    {/foreach}
            </select><span class="waitimage"></span>
    </div>
    <p class="address_add submit">
        <a href="{$link->getPageLink('address', true, NULL, "back={$back_order_page}?step=1{if $back}&mod={$back}{/if}")|escape:'html':'UTF-8'}" title="{l s='Add'}" class="button button-small btn btn-default">
                <span>{l s='Add a new address'}<i class="icon-chevron-right right"></i></span>
        </a>
    </p>
    <p class="checkbox addressesAreEquals"{if $cart->isVirtualCart()} style="display:none;"{/if}>
            <input type="checkbox" name="same" id="addressesAreEquals" value="1"{if $cart->id_address_invoice == $cart->id_address_delivery || $addresses|@count == 1} checked="checked"{/if} />
            <label for="addressesAreEquals">{l s='Use the delivery address as the billing address.'}</label>
    </p>
</div>


<div class="gk-blk-delivery-address">

		<div class="gk-g-wrap">
			<div class="gk-col gk-col6">
				{*}<h3 class="gk-elem-first">{l s='Адрес доставки'}</h3>{*}
                                <ul class="address item box" id="address_delivery" style="list-style:none;">
                                </ul>
                                {*}<h3>{l s='Адрес выставления счета'}</h3>{*}
                                <ul style="list-style:none;" class="address alternate_item{if $cart->isVirtualCart()} full_width{/if} box" id="address_invoice">
                                </ul>
			</div>
			<div class="gk-col gk-col6" style="position: absolute;bottom: 0;right: 0">
				<h3 class="gk-elem-first">{l s='Ваш заказ:'}</h3>
				<ol>
					{foreach from=$product_list item=item}
						<li>{$item['name']}</li>
					{/foreach}
				</ol>
				{*}<div class="gk-separator" style="margin: 278px 0 0 0"></div>{*}
                                <div id="ordermsg" class="form-group">
                                    <label>{l s='If you would like to add a comment about your order, please write it in the field below.'}</label>
                                    <textarea class="form-control" cols="60" rows="6" name="message">{if isset($oldMessage)}{$oldMessage}{/if}</textarea>
                                </div>
                                
                                
                                
				<div class="gk-align-R">
					<div class="total-price">{l s='Всего:'} <span>{displayPrice price=$global_order_total}</span></div>
					<button type="submit" name="processAddress" class="button btn btn-default button-medium">
						<span>{l s='Далее'}<i class="icon-chevron-right right"></i></span>
					</button>
				</div>
			</div>
		</div>
</div>

{if !$opc}
			<input type="hidden" class="hidden" name="step" value="2" />
			<input type="hidden" name="back" value="{$back}" />
		</form>
{else}
	</div> <!--  end opc_account -->
{/if}
{strip}
{if !$opc}
	{addJsDef orderProcess='order'}
	{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
	{addJsDef currencyRate=$currencyRate|floatval}
	{addJsDef currencyFormat=$currencyFormat|intval}
	{addJsDef currencyBlank=$currencyBlank|intval}
	{addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
	{addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
	{addJsDefL name=CloseTxt}{l s='Submit' js=1}{/addJsDefL}
{/if}
{capture}{if $back}&mod={$back|urlencode}{/if}{/capture}
{capture name=addressUrl}{$link->getPageLink('address', true, NULL, 'back='|cat:$back_order_page|cat:'?step=1'|cat:$smarty.capture.default)|addslashes}{/capture}
{addJsDef addressUrl=$smarty.capture.addressUrl}
{capture}{'&multi-shipping=1'|urlencode}{/capture}
{addJsDef addressMultishippingUrl=$smarty.capture.addressUrl|cat:$smarty.capture.default}
{capture name=addressUrlAdd}{$smarty.capture.addressUrl|cat:'&id_address='}{/capture}
{addJsDef addressUrlAdd=$smarty.capture.addressUrlAdd}
{addJsDef formatedAddressFieldsValuesList=$formatedAddressFieldsValuesList}
{addJsDef opc=$opc|boolval}
{capture}<h3 class="page-subheading">{l s='Your billing address' js=1}</h3>{/capture}
{addJsDefL name=titleInvoice}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{capture}<h3 class="page-subheading">{l s='Your delivery address' js=1}</h3>{/capture}
{addJsDefL name=titleDelivery}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{capture}<a class="button button-small btn btn-default" href="{$smarty.capture.addressUrlAdd}" title="{l s='Update' js=1}"><span>{l s='Update' js=1}<i class="icon-chevron-right right"></i></span></a>{/capture}
{addJsDefL name=liUpdate}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{/strip}
