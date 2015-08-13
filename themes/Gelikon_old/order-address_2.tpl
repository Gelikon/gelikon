

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
<div class="gk-blk-delivery-address">

		<div class="gk-g-wrap">
			<div class="gk-col gk-col6">
				<h3 class="gk-elem-first">{l s='Адрес доставки'}</h3>
				<div class="control-row">
					<div class="control-label"><label>{l s='Страна'}</label></div>
					<div class="control-widget">
						<label>{$delivery->country}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<div class="control-row">
					<div class="control-label"><label>{l s='Город'}</label></div>
					<div class="control-widget">
						<label>{$delivery->city}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<div class="control-row">
					<div class="control-label"><label>{l s='Адрес'}</label></div>
					<div class="control-widget">
						<label>{$delivery->address1}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<div class="control-row">
					<div class="control-label"><label>{l s='Индекс'}</label></div>
					<div class="control-widget">
						<label>{$delivery->postcode}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
				<div class="control-row">
					<div class="control-label"><label>{l s='Имя'}</label></div>
					<div class="control-widget">
						<label>{$delivery->firstname}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
				<div class="control-row">
					<div class="control-label"><label>{l s='Фамилия'}</label></div>
					<div class="control-widget">
						<label>{$delivery->lastname}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
				<div class="control-row">
					<div class="control-label"><label>{l s='Телефон'}</label></div>
					<div class="control-widget">
						<label>{$delivery->phone}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<h3>{l s='Адрес выставления счета'}</h3>
				<div class="control-row">
					<div class="control-label"><label>{l s='Страна'}</label></div>
					<div class="control-widget">
						<label>{$invoice->country}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<div class="control-row">
					<div class="control-label"><label>{l s='Город'}</label></div>
					<div class="control-widget">
						<label>{$invoice->city}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<div class="control-row">
					<div class="control-label"><label>{l s='Адрес'}</label></div>
					<div class="control-widget">
						<label>{$invoice->address1}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<div class="control-row">
					<div class="control-label"><label>{l s='Индекс'}</label></div>
					<div class="control-widget">
						<label>{$invoice->postcode}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
				<div class="control-row">
					<div class="control-label"><label>{l s='Имя'}</label></div>
					<div class="control-widget">
						<label>{$invoice->firstname}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
				<div class="control-row">
					<div class="control-label"><label>{l s='Фамилия'}</label></div>
					<div class="control-widget">
						<label>{$invoice->lastname}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
			</div>
			<div class="gk-col gk-col6" style="position: absolute;bottom: 0;right: 0">
				<h3 class="gk-elem-first">{l s='Ваш заказ:'}</h3>
				<ol>
					{foreach from=$product_list item=item}
						<li>{$item['name']}</li>
					{/foreach}
				</ol>
				{*}<div class="gk-separator" style="margin: 278px 0 0 0"></div>{*}
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
