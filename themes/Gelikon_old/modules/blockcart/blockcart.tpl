{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- MODULE Block cart -->
{if isset($blockcart_top) && $blockcart_top}
<div class="gk-col">
{/if}
	<div class="gk-cart">
		<a href="{$link->getPageLink($order_process, true)|escape:'html':'UTF-8'}" title="{l s='View my shopping cart' mod='blockcart'}" rel="nofollow">
			{*l s='Cart' mod='blockcart'*}
			<span class="num">{$cart_qties}</span>
			{if $ajax_allowed && isset($blockcart_top) && !$blockcart_top}
				<span class="block_cart_expand{if !isset($colapseExpandStatus) || (isset($colapseExpandStatus) && $colapseExpandStatus eq 'expanded')} unvisible{/if}">&nbsp;</span>
				<span class="block_cart_collapse{if isset($colapseExpandStatus) && $colapseExpandStatus eq 'collapsed'} unvisible{/if}">&nbsp;</span>
			{/if}
		</a>
		{if !$PS_CATALOG_MODE}

		{/if}
	</div>
{if isset($blockcart_top) && $blockcart_top}
</div>
{/if}
{counter name=active_overlay assign=active_overlay}
{if !$PS_CATALOG_MODE && $active_overlay == 1}
	<div id="layer_cart">
		<div class="clearfix">
			<div class="layer_cart_product col-xs-12 col-md-6">
				<span class="cross" title="{l s='Close window' mod='blockcart'}">X</span>
				<h2>
					<i class="icon-ok"></i>{l s='Product successfully added to your shopping cart' mod='blockcart'}
				</h2>
				<div class="product-image-container layer_cart_img">
				</div>
				<div class="layer_cart_product_info">
					<span id="layer_cart_product_title" class="product-name"></span>
					<span id="layer_cart_product_attributes"></span>
					<div>
						<strong class="dark">{l s='Quantity' mod='blockcart'}</strong>
						<span id="layer_cart_product_quantity"></span>
					</div>
					<div>
						<strong class="dark">{l s='Total' mod='blockcart'}</strong>
						<span id="layer_cart_product_price"></span>
					</div>
				</div>
			</div>
			<div class="layer_cart_cart col-xs-12 col-md-6">
				<h2>
					<!-- Plural Case [both cases are needed because page may be updated in Javascript] -->
					<span class="ajax_cart_product_txt_s {if $cart_qties < 2} unvisible{/if}">
						{l s='There are [1]%d[/1] items in your cart.' mod='blockcart' sprintf=[$cart_qties] tags=['<span class="ajax_cart_quantity">']}
					</span>
					<!-- Singular Case [both cases are needed because page may be updated in Javascript] -->
					<span class="ajax_cart_product_txt {if $cart_qties > 1} unvisible{/if}">
						{l s='There is 1 item in your cart.' mod='blockcart'}
					</span>
				</h2>
	
				<div class="layer_cart_row">
					<strong class="dark">
						{l s='Total products' mod='blockcart'}
						{if $priceDisplay == 1}
							{l s='(tax excl.)' mod='blockcart'}
						{else}
							{l s='(tax incl.)' mod='blockcart'}
						{/if}
					</strong>
					<span class="ajax_block_products_total">
						{if $cart_qties > 0}
							{convertPrice price=$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS)}
						{/if}
					</span>
				</div>
	
				{if $show_wrapping}
					<div class="layer_cart_row">
						<strong class="dark">
							{l s='Wrapping' mod='blockcart'}
							{if $priceDisplay == 1}
								{l s='(tax excl.)' mod='blockcart'}
							{else}
								{l s='(tax incl.)' mod='blockcart'}
							{/if}
						</strong>
						<span class="price cart_block_wrapping_cost">
							{if $priceDisplay == 1}
								{convertPrice price=$cart->getOrderTotal(false, Cart::ONLY_WRAPPING)}
							{else}
								{convertPrice price=$cart->getOrderTotal(true, Cart::ONLY_WRAPPING)}
							{/if}
						</span>
					</div>
				{/if}
				<div class="layer_cart_row">
					<strong class="dark">
						{l s='Total shipping' mod='blockcart'}&nbsp;{l s='(tax excl.)' mod='blockcart'}
					</strong>
					<span class="ajax_cart_shipping_cost">
						{if $shipping_cost_float == 0}
							{l s='Free shipping!' mod='blockcart'}
						{else}
							{$shipping_cost}
						{/if}
					</span>
				</div>
				{if $show_tax && isset($tax_cost)}
					<div class="layer_cart_row">
						<strong class="dark">{l s='Tax' mod='blockcart'}</strong>
						<span class="price cart_block_tax_cost ajax_cart_tax_cost">{$tax_cost}</span>
					</div>
				{/if}
				<div class="layer_cart_row">	
					<strong class="dark">
						{l s='Total' mod='blockcart'}
						{if $priceDisplay == 1}
							{l s='(tax excl.)' mod='blockcart'}
						{else}
							{l s='(tax incl.)' mod='blockcart'}
						{/if}
					</strong>
					<span class="ajax_block_cart_total">
						{if $cart_qties > 0}
							{if $priceDisplay == 1}
								{convertPrice price=$cart->getOrderTotal(false)}
							{else}
								{convertPrice price=$cart->getOrderTotal(true)}
							{/if}
						{/if}
					</span>
				</div>
				<div class="button-container">	
					<span class="continue btn btn-default button exclusive-medium" title="{l s='Continue shopping' mod='blockcart'}">
						<span>
							<i class="icon-chevron-left left"></i>{l s='Continue shopping' mod='blockcart'}
						</span>
					</span>
					<a class="btn btn-default button button-medium"	href="{$link->getPageLink("$order_process", true)|escape:"html":"UTF-8"}" title="{l s='Proceed to checkout' mod='blockcart'}" rel="nofollow">
						<span>
							{l s='Proceed to checkout' mod='blockcart'}<i class="icon-chevron-right right"></i>
						</span>
					</a>	
				</div>
			</div>
		</div>
		<div class="crossseling"></div>
	</div> <!-- #layer_cart -->
	<div class="layer_cart_overlay"></div>
{/if}
{strip}
{addJsDef CUSTOMIZE_TEXTFIELD=$CUSTOMIZE_TEXTFIELD}
{addJsDef img_dir=$img_dir|addslashes}
{addJsDef ajax_allowed=$ajax_allowed|boolval}

{addJsDefL name=customizationIdMessage}{l s='Customization #' mod='blockcart' js=1}{/addJsDefL}
{addJsDefL name=removingLinkText}{l s='remove this product from my cart' mod='blockcart' js=1}{/addJsDefL}
{addJsDefL name=freeShippingTranslation}{l s='Free shipping!' mod='blockcart' js=1}{/addJsDefL}
{addJsDefL name=freeProductTranslation}{l s='Free!' mod='blockcart' js=1}{/addJsDefL}
{addJsDefL name=delete_txt}{l s='Delete' mod='blockcart' js=1}{/addJsDefL}
{/strip}
<!-- /MODULE Block cart -->