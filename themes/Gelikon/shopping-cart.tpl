{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}{l s='Your shopping cart'}{/capture}

<h1 id="cart_title" class="gk-align-C">{l s='Оформление заказа'}</h1>

{if isset($account_created)}
	<p class="alert alert-success">
		{l s='Your account has been created.'}
	</p>
{/if}

{assign var='current_step' value='summary'}
{include file="$tpl_dir./order-steps.tpl"}
{include file="$tpl_dir./errors.tpl"}

{if isset($empty)}
	<p class="alert alert-warning">{l s='Your shopping cart is empty.'}</p>
{elseif $PS_CATALOG_MODE}
	<p class="alert alert-warning">{l s='This store has not accepted your new order.'}</p>
{else}
	<p style="display:none" id="emptyCartWarning" class="alert alert-warning">{l s='Your shopping cart is empty.'}</p>

	{assign var='total_discounts_num' value="{if $total_discounts != 0}1{else}0{/if}"}
	{assign var='use_show_taxes' value="{if $use_taxes && $show_taxes}2{else}0{/if}"}
	{assign var='total_wrapping_taxes_num' value="{if $total_wrapping != 0}1{else}0{/if}"}

	<div id="order-detail-content" class="blk gk-blk-cart">
		<table id="cart_summary" class="table table-bordered">
			<thead>
				<tr>
					<th></th>
					<th>{l s='Description'}</th>
						<th>{l s=''}</th>
					<th class="cart_quantity item">{l s='Qty'}</th>
					<th class="cart_total item">{l s='Total'}</th>
					<th class="cart_delete last_item">&nbsp;</th>
				</tr>
			</thead>
			<tfoot>
				{if $use_taxes}
					{if $priceDisplay}
						<tr class="cart_total_price">
							<td rowspan="{3+$total_discounts_num+$use_show_taxes+$total_wrapping_taxes_num}" colspan="2" id="cart_voucher" class="cart_voucher">
								{if $voucherAllowed}
									{if isset($errors_discount) && $errors_discount}
										<ul class="alert alert-danger">
											{foreach $errors_discount as $k=>$error}
												<li>{$error|escape:'html':'UTF-8'}</li>
											{/foreach}
										</ul>
									{/if}
									<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
										<fieldset>
											<h4>{l s='Vouchers'}</h4>
											<input type="text" class="discount_name form-control" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
											<input type="hidden" name="submitDiscount" />
											<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small"><span>{l s='OK'}</span></button>
										</fieldset>
									</form>
									{if $displayVouchers}
										<p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
										<div id="display_cart_vouchers">
											{foreach $displayVouchers as $voucher}
												{if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
											{/foreach}
										</div>
									{/if}
								{/if}
							</td>
                                                        {*}
							<td colspan="3" class="text-right">{if $display_tax_label}{l s='Total products (tax excl.)'}{else}{l s='Total products'}{/if}</td>
							<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
                                                        {*}
						</tr>
					{else}
						<tr class="cart_total_price">
							<td rowspan="{3+$total_discounts_num+$use_show_taxes+$total_wrapping_taxes_num}" colspan="2" id="cart_voucher" class="cart_voucher">
								{if $voucherAllowed}
									{if isset($errors_discount) && $errors_discount}
										<ul class="alert alert-danger">
											{foreach $errors_discount as $k=>$error}
												<li>{$error|escape:'html':'UTF-8'}</li>
											{/foreach}
										</ul>
									{/if}
									<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
										<fieldset>
											<h4>{l s='Vouchers'}</h4>
											<input type="text" class="discount_name form-control" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
											<input type="hidden" name="submitDiscount" />
											<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small"><span>{l s='OK'}</span></button>
										</fieldset>
									</form>
									{if $displayVouchers}
										<p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
										<div id="display_cart_vouchers">
											{foreach $displayVouchers as $voucher}
												{if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
											{/foreach}
										</div>
									{/if}
								{/if}
							</td>
							{*}<td colspan="3" class="text-right">{if $display_tax_label}{l s='Total products (tax incl.)'}{else}{l s='Total products'}{/if}</td>
							<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products_wt}</td>{*}
						</tr>
					{/if}
                            {else}
                                <tr class="cart_total_price">
						<td rowspan="{3+$total_discounts_num+$use_show_taxes+$total_wrapping_taxes_num}" colspan="2" id="cart_voucher" class="cart_voucher">
							{if $voucherAllowed}
								{if isset($errors_discount) && $errors_discount}
									<ul class="alert alert-danger">
										{foreach $errors_discount as $k=>$error}
											<li>{$error|escape:'html':'UTF-8'}</li>
										{/foreach}
									</ul>
								{/if}
								<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
									<fieldset>
										<h4>{l s='Vouchers'}</h4>
										<input type="text" class="discount_name form-control" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
										<input type="hidden" name="submitDiscount" />
										<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small">
											<span>{l s='OK'}</span>
										</button>
									</fieldset>
								</form>
								{if $displayVouchers}
									<p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
									<div id="display_cart_vouchers">
										{foreach $displayVouchers as $voucher}
											{if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
										{/foreach}
									</div>
								{/if}
							{/if}
						</td>
                                                {*}
						<td colspan="3" class="text-right">{l s='Total products'}</td>
						<td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
                                                {*}
					</tr>
                            {/if}
			</tfoot>

			<tbody rel="sss">
				{assign var='odd' value=0}
				{assign var='have_non_virtual_products' value=false}
				{foreach $products as $product}
					{if $product.is_virtual == 0}
						{assign var='have_non_virtual_products' value=true}						
					{/if}
					{assign var='productId' value=$product.id_product}
					{assign var='productAttributeId' value=$product.id_product_attribute}
					{assign var='quantityDisplayed' value=0}
					{assign var='odd' value=($odd+1)%2}
					{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($gift_products)}
					{* Display the product line *}
					{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
					{* Then the customized datas ones*}
					{if isset($customizedDatas.$productId.$productAttributeId)}
						{foreach $customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] as $id_customization=>$customization}
							<tr
								id="product_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
								class="product_customization_for_{$product.id_product}_{$product.id_product_attribute}_{$product.id_address_delivery|intval}{if $odd} odd{else} even{/if} customization alternate_item {if $product@last && $customization@last && !count($gift_products)}last_item{/if}">
								<td></td>
								<td colspan="3">
									{foreach $customization.datas as $type => $custom_data}
										{if $type == $CUSTOMIZE_FILE}
											<div class="customizationUploaded">
												<ul class="customizationUploaded">
													{foreach $custom_data as $picture}
														<li><img src="{$pic_dir}{$picture.value}_small" alt="" class="customizationUploaded" /></li>
													{/foreach}
												</ul>
											</div>
										{elseif $type == $CUSTOMIZE_TEXTFIELD}
											<ul class="typedText">
												{foreach $custom_data as $textField}
													<li>
														{if $textField.name}
															{$textField.name}
														{else}
															{l s='Text #'}{$textField@index+1}
														{/if}
														: {$textField.value}
													</li>
												{/foreach}
											</ul>
										{/if}
									{/foreach}
								</td>
								<td class="cart_quantity" colspan="2">
									{if isset($cannotModify) AND $cannotModify == 1}
										<span>{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}</span>
									{else}
										<input type="hidden" value="{$customization.quantity}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}_hidden"/>
										<input type="text" value="{$customization.quantity}" class="cart_quantity_input form-control grey" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"/>
										<div class="cart_quantity_button clearfix">
											{if $product.minimal_quantity < ($customization.quantity -$quantityDisplayed) OR $product.minimal_quantity <= 1}
												<a
													id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
													class="cart_quantity_down btn btn-default button-minus"
													href="{$link->getPageLink('cart', true, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery}&amp;id_customization={$id_customization}&amp;op=down&amp;token={$token_cart}")|escape:'html':'UTF-8'}"
													rel="nofollow"
													title="{l s='Subtract'}">
													<span><i class="icon-minus"></i></span>
												</a>
											{else}
												<a
													id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}"
													class="cart_quantity_down btn btn-default button-minus disabled"
													href="#"
													title="{l s='Subtract'}">
													<span><i class="icon-minus"></i></span>
												</a>
											{/if}
											<a
												id="cart_quantity_up_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
												class="cart_quantity_up btn btn-default button-plus"
												href="{$link->getPageLink('cart', true, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery}&amp;id_customization={$id_customization}&amp;token={$token_cart}")|escape:'html':'UTF-8'}"
												rel="nofollow"
												title="{l s='Add'}">
												<span><i class="icon-plus"></i></span>
											</a>
										</div>
									{/if}
								</td>
								<td class="cart_delete">
									{if isset($cannotModify) AND $cannotModify == 1}
									{else}
										<div>
											<a
												id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
												class="cart_quantity_delete"
												href="{$link->getPageLink('cart', true, NULL, "delete=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;id_address_delivery={$product.id_address_delivery}&amp;token={$token_cart}")|escape:'html':'UTF-8'}"
												rel="nofollow"
												title="{l s='Delete'}">
												<i class=" icon-trash"></i>
											</a>
										</div>
									{/if}
								</td>
							</tr>
							{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
						{/foreach}

						{* If it exists also some uncustomized products *}
						{if $product.quantity-$quantityDisplayed > 0}{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}{/if}
					{/if}
				{/foreach}
				{assign var='last_was_odd' value=$product@iteration%2}
				{foreach $gift_products as $product}
					{assign var='productId' value=$product.id_product}
					{assign var='productAttributeId' value=$product.id_product_attribute}
					{assign var='quantityDisplayed' value=0}
					{assign var='odd' value=($product@iteration+$last_was_odd)%2}
					{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
					{assign var='cannotModify' value=1}
					{* Display the gift product line *}
					{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
				{/foreach}
			</tbody>
			{if sizeof($discounts)}
				<tbody>
					{foreach $discounts as $discount}
						<tr class="cart_discount {if $discount@last}last_item{elseif $discount@first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
							<td class="cart_discount_name" colspan="3">{$discount.name}</td>
							<td class="cart_discount_price">
								<span class="price-discount">
								{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
								</span>
							</td>
							<td class="cart_discount_delete">1</td>
							<td class="cart_discount_price">
								<span class="price-discount price">{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
							</td>
							<td class="price_discount_del text-center">
								{if strlen($discount.code)}
									<a
										href="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}?deleteDiscount={$discount.id_discount}"
										class="price_discount_delete"
										title="{l s='Delete'}">
										<i class="icon-trash"></i>
									</a>
								{/if}
							</td>
						</tr>
					{/foreach}
				</tbody>
			{/if}
		</table>
		                <div class="gk-g-wrap">
                    	<div class="gk-col gk-col6 gk-float-R">
                        <div class="blk gk-separator"></div>
                        {*}
                        <div class="total-price">
                            {l s='Всего:'} 
                            <span>
                                <tr class="cart_total_price">
					<td colspan="3" class="total_price_container text-right">
					</td>
					{if $use_taxes}
						<td colspan="2" class="price" id="total_price_container">
							<span id="total_price">{displayPrice price=$total_price}</span>
						</td>
					{else}
						<td colspan="2" class="price" id="total_price_container">
							<span id="total_price">{displayPrice price=$total_price_without_tax}</span>
						</td>
					{/if}
				</tr>
                            </span>
                        </div>
                        {*}
                        <div class="total-price">
                            {l s='Всего за товары:'} 
                            <span>
                                <tr class="cart_total_price">
					<td colspan="3" class="total_price_container text-right">
					</td>
					{if $use_taxes}
						<td colspan="2" class="price" id="total_price_container">
							<span id="total_product">{displayPrice price=$total_products_wt}</span>
						</td>
					{else}
						<td colspan="2" class="price" id="total_price_container">
							<span id="total_product">{displayPrice price=$total_products}</span>
						</td>
					{/if}
				</tr>
                            </span>
                        </div>
                        

                        	<p class="cart_navigation clearfix">
		{if !$opc}
			<a
				href="{if $back}{$link->getPageLink('order', true, NULL, 'step=1&amp;back={$back}')|escape:'html':'UTF-8'}{else}{$link->getPageLink('order', true, NULL, 'step=1')|escape:'html':'UTF-8'}{/if}"
				class="button btn btn-default standard-checkout button-medium"
				title="{l s='Proceed to checkout'}">
				<span>{l s='Proceed to checkout'}<i class="icon-chevron-right right"></i></span>
			</a>
		{/if}

	</p>

                    </div>
                </div>
							

	</div> <!-- end order-detail-content -->

	{if $show_option_allow_separate_package}
	<p>
		<input type="checkbox" name="allow_seperated_package" id="allow_seperated_package" {if $cart->allow_seperated_package}checked="checked"{/if} autocomplete="off"/>
		<label for="allow_seperated_package">{l s='Send available products first'}</label>
	</p>
	{/if}

	{* Define the style if it doesn't exist in the PrestaShop version*}
	{* Will be deleted for 1.5 version and more *}
	{if !isset($addresses_style)}
		{$addresses_style.company = 'address_company'}
		{$addresses_style.vat_number = 'address_company'}
		{$addresses_style.firstname = 'address_name'}
		{$addresses_style.lastname = 'address_name'}
		{$addresses_style.address1 = 'address_address1'}
		{$addresses_style.address2 = 'address_address2'}
		{$addresses_style.city = 'address_city'}
		{$addresses_style.country = 'address_country'}
		{$addresses_style.phone = 'address_phone'}
		{$addresses_style.phone_mobile = 'address_phone_mobile'}
		{$addresses_style.alias = 'address_title'}
	{/if}
	
	<div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>

	{if !empty($HOOK_SHOPPING_CART_EXTRA)}
		<div class="clear"></div>
		<div class="cart_navigation_extra">
			<div id="HOOK_SHOPPING_CART_EXTRA">{$HOOK_SHOPPING_CART_EXTRA}</div>
		</div>
	{/if}
{strip}
{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
{addJsDef currencyRate=$currencyRate|floatval}
{addJsDef currencyFormat=$currencyFormat|intval}
{addJsDef currencyBlank=$currencyBlank|intval}
{addJsDef deliveryAddress=$cart->id_address_delivery|intval}
{addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
{addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
{/strip}
{/if}
