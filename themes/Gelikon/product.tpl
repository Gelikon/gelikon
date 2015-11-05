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
{include file="$tpl_dir./errors.tpl"}
{if $errors|@count == 0}
	{if !isset($priceDisplayPrecision)}
		{assign var='priceDisplayPrecision' value=2}
	{/if}
	{if !$priceDisplay || $priceDisplay == 2}
		{assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, $priceDisplayPrecision)}
		{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
	{elseif $priceDisplay == 1}
		{assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, $priceDisplayPrecision)}
		{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
	{/if}
<div class="gk-g-wrap gk-big-col-padding">
    <div class="gk-col gk-col9">
        <div class="gk-content-margin">
            <div class="blk17 gk-elem-first">
                <!--хлебные крошки-->
                <ul class="nav nav-horizontal gk-breadcrumbs">
				<li><a href="#">{$path}</a></li>
                </ul>

            </div>


            <!--информация о продукте-->
            <div class="blk gk-product">
                <h1 itemprop="name">{$product->name|escape:'html':'UTF-8'} </h1>

                <div class="description gk-border-h">
                    <div class="cover">
                 {if $have_image}
					<img src="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'thickbox_default')|escape:'html':'UTF-8'}" alt="cover01"/>
				{else}
					<img src="{$img_prod_dir}{$lang_iso}-default-large_default.jpg" id="bigpic" alt="" title="{$product->name|escape:'html':'UTF-8'}" alt="cover01"/>
				{/if}
                       
                    </div>
                    <div class="text">
                        {$product->description}
                    </div>

                    <div class="gk-clear-fix"></div>
                </div>
                <div class="info">
                    <div class="gk-g-wrap gk-big-col-padding">
                        <div class="gk-col gk-col7">
                            <div class="property">
                                <p class="gk-elem-first"><b>{l s='Артикул товара:'}</b> {$product->reference}</p>
                            	{foreach from=$features item=feature}
								{if isset($feature.value)}
								<p class="gk-elem-first"><b>{$feature.name|escape:'html':'UTF-8'}: </b>{$feature.value|escape:'html':'UTF-8'}</p>			    
								{/if}
								{/foreach}
                                <p class="gk-elem-first"><b>{l s='Количество на складе:'} </b>{if $product->quantity <= 0}{if $allow_oosp}{$product->available_later}{else}{l s='This product is no longer in stock'}{/if}{else}{*$product->available_now*}{$product->quantity}{/if}</p>
                                <p class="gk-elem-first" style="float: left;"><b>{l s='Вес товара:'}</b> <div class="threenull">{$product->weight}</div> {l s='кг'}</p>
                            </div>
                        </div>
                        <div class="gk-col gk-col5">
                            <div class="order">
                                <div class="price">{convertPrice price=$productPrice}<span>{l s='включая  налог с оборота без'} <a href="/index.php?id_cms=9&controller=cms">
                                    {l s='стоимости пересылки'}</a></span></div>
                                <p id="add_to_cart" >
								<button type="submit" name="Submit" class="exclusive">
									<span>{l s='купить'}</span>
								</button>
							</p>
							<!-- add to cart form-->
				<form id="buy_block"{if $PS_CATALOG_MODE && !isset($groups) && $product->quantity > 0} class="hidden"{/if} action="{$link->getPageLink('cart')|escape:'html':'UTF-8'}" method="post">
				<!-- hidden datas -->
				<p class="hidden">
					<input type="hidden" name="token" value="{$static_token}" />
					<input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
					<input type="hidden" name="add" value="1" />
					<input type="hidden" name="id_product_attribute" id="idCombination" value="" />
				</p>
				</form>
                            </div>
                        </div>
                    </div>
                </div>


                {if $PS_STOCK_MANAGEMENT}
                <!-- availability -->
                <p id="availability_statut"{if ($product->quantity <= 0 && !$product->available_later && $allow_oosp) || ($product->quantity > 0 && !$product->available_now) || !$product->available_for_order || $PS_CATALOG_MODE} {*style="display: none;*}"{/if}>
                    {*<span id="availability_label">{l s='Availability:'}</span>*}
                    <span id="availability_value"{if $product->quantity <= 0} class="warning_inline"{/if}>
                        
                        {if $product->quantity <= 0}
                     

                            {l s='This product is no longer in stock and can be brought to order in 2-3 weeks.'}
                          
                        {else}{*$product->available_now*}{l s='Товар есть в наличии. Оправка в течение 2-6 рабочих дней.'}
                        {/if}
                    </span>
                                 
                </p>
                <!--
                <p class="warning_inline" id="last_quantities"{if ($product->quantity > $last_qties || $product->quantity <= 0) || $allow_oosp || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none"{/if} >{l s='Warning: Last items in stock!'}</p>
                {/if}
            -->


            
              
                    <!-- number of item in stock -->
                    {*<p id="pQuantityAvailable"{if $product->quantity <= 0} style="display: none;"{/if}>*}
                        {*
                        <span id="quantityAvailable">{$product->quantity|intval}</span>
                        <span {if $product->quantity > 1} style="display: none;"{/if} id="quantityAvailableTxt">{l s='Item'}</span>
                        <span {if $product->quantity == 1} style="display: none;"{/if} id="quantityAvailableTxtMultiple">{l s='Items'}</span>
                        *}
                        {if $product->quantity > 0}
                         <!--наличие этого товара в других магазинах -->
                        <div class="blk gk-other-shops">
                            <h2>{l s='Так же, вы можете купить эту книгу:'}</h2>
                            <div class="city gk-elem-first">
                                <div class="title">{l s='В Берлине'}</div>
                                {foreach from=$wh_stock key=id_warehouse item=wh_st}
                                    <div class="shop gk-elem-first">
                                        <div class="title">{*l s='Gelikon store'*} {$wh_st.reference}</div>
                                        <p><b>{l s='Address:'}</b> {$wh_st.address1},{$wh_st.postcode}, {$wh_st.city}, {$wh_st.country}</p>
                                        <p><b>{l s='Phone:'}</b> {$wh_st.phone}</p> 
                                        <p><b>{l s='Open:'}</b> {$wh_st.address2}</p>
                                        <p>{l s='Quantity:'} <i>
                                            {$wh_st.real_quantity}
                                            {*
                                            {if $wh_st.real_quantity==1}{l s='the last instance'}{/if}{if $wh_st.real_quantity<=10}{l s='few'}{/if}{if $wh_st.real_quantity>10}{l s='many'}{/if}
                                            *}
                                        </i></p>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        {/if}
                    </p>

         

                


            </div>
{if isset($HOOK_PRODUCT_TAB_CONTENT) && $HOOK_PRODUCT_TAB_CONTENT}{$HOOK_PRODUCT_TAB_CONTENT}{/if}
            
            

        </div>


    </div>
    <div class="gk-col gk-col3">

        <div class="blk gk-elem-first">
            <!--баннер-->
            <div class="gk-banner">
                <!--
                <a href="/index.php?id_cms=12&controller=cms"><img src="images/banner.jpg" alt="banner"/></a>
            -->
            <a href="#"><img src="/images/banner_background_color.png" alt="banner"/></a>
          
            </div>
        </div>

        <div class="blk">
            <!--<div class="gk-subscribe">
                <div class="title">{l s='Подпишитесь на новости'}</div>
               
                <div id="mc_embed_signup">
                <form action="//gelikon.us10.list-manage.com/subscribe/post?u=396b4d0d91483a5207f7b6bdc&amp;id=de8dffa51a" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <div id="mc_embed_signup_scroll">
                    
                <div class="mc-field-group">
                    <label for="mce-EMAIL"></label>
                    <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                </div>
                    <div id="mce-responses" class="clear">
                        <div class="response" id="mce-error-response" style="display:none"></div>
                        <div class="response" id="mce-success-response" style="display:none"></div>
                    </div>    
                    <div style="position: absolute; left: -5000px;"><input type="text" name="b_396b4d0d91483a5207f7b6bdc_de8dffa51a" tabindex="-1" value=""></div>
                    <div class="clear">
                        <input type="submit" value="ok" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                    </div>
                </form>
                </div>-->


 


            </div>
        </div>
    </div>

</div>
{if isset($HOOK_PRODUCT_FOOTER) && $HOOK_PRODUCT_FOOTER}{$HOOK_PRODUCT_FOOTER}{/if}
{strip}
{strip}
{if isset($smarty.get.ad) && $smarty.get.ad}
{addJsDefL name=ad}{$base_dir|cat:$smarty.get.ad|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{if isset($smarty.get.adtoken) && $smarty.get.adtoken}
{addJsDefL name=adtoken}{$smarty.get.adtoken|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{addJsDef allowBuyWhenOutOfStock=$allow_oosp|boolval}
{addJsDef availableNowValue=$product->available_now|escape:'quotes':'UTF-8'}
{addJsDef availableLaterValue=$product->available_later|escape:'quotes':'UTF-8'}
{addJsDef attribute_anchor_separator=$attribute_anchor_separator|addslashes}
{addJsDef attributesCombinations=$attributesCombinations}
{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
{addJsDef currencyRate=$currencyRate|floatval}
{addJsDef currencyFormat=$currencyFormat|intval}
{addJsDef currencyBlank=$currencyBlank|intval}
{addJsDef currentDate=$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}
{if isset($combinations) && $combinations}
	{addJsDef combinations=$combinations}
	{addJsDef combinationsFromController=$combinations}
	{addJsDef displayDiscountPrice=$display_discount_price}
	{addJsDefL name='upToTxt'}{l s='Up to' js=1}{/addJsDefL}
{/if}
{if isset($combinationImages) && $combinationImages}
	{addJsDef combinationImages=$combinationImages}
{/if}
{addJsDef customizationFields=$customizationFields}
{addJsDef default_eco_tax=$product->ecotax|floatval}
{addJsDef displayPrice=$priceDisplay|intval}
{addJsDef ecotaxTax_rate=$ecotaxTax_rate|floatval}
{addJsDef group_reduction=$group_reduction}
{if isset($cover.id_image_only)}
	{addJsDef idDefaultImage=$cover.id_image_only|intval}
{else}
	{addJsDef idDefaultImage=0}
{/if}
{addJsDef img_ps_dir=$img_ps_dir}
{addJsDef img_prod_dir=$img_prod_dir}
{addJsDef id_product=$product->id|intval}
{addJsDef jqZoomEnabled=$jqZoomEnabled|boolval}
{addJsDef maxQuantityToAllowDisplayOfLastQuantityMessage=$last_qties|intval}
{addJsDef minimalQuantity=$product->minimal_quantity|intval}
{addJsDef noTaxForThisProduct=$no_tax|boolval}
{addJsDef oosHookJsCodeFunctions=Array()}
{addJsDef productHasAttributes=isset($groups)|boolval}
{addJsDef productPriceTaxExcluded=($product->getPriceWithoutReduct(true)|default:'null' - $product->ecotax)|floatval}
{addJsDef productBasePriceTaxExcluded=($product->base_price - $product->ecotax)|floatval}
{addJsDef productReference=$product->reference|escape:'html':'UTF-8'}
{addJsDef productAvailableForOrder=$product->available_for_order|boolval}
{addJsDef productPriceWithoutReduction=$productPriceWithoutReduction|floatval}
{addJsDef productPrice=$productPrice|floatval}
{addJsDef productUnitPriceRatio=$product->unit_price_ratio|floatval}
{addJsDef productShowPrice=(!$PS_CATALOG_MODE && $product->show_price)|boolval}
{addJsDef PS_CATALOG_MODE=$PS_CATALOG_MODE}
{if $product->specificPrice && $product->specificPrice|@count}
	{addJsDef product_specific_price=$product->specificPrice}
{else}
	{addJsDef product_specific_price=array()}
{/if}
{if $display_qties == 1 && $product->quantity}
	{addJsDef quantityAvailable=$product->quantity}
{else}
	{addJsDef quantityAvailable=0}
{/if}
{addJsDef quantitiesDisplayAllowed=$display_qties|boolval}
{if $product->specificPrice && $product->specificPrice.reduction && $product->specificPrice.reduction_type == 'percentage'}
	{addJsDef reduction_percent=$product->specificPrice.reduction*100|floatval}
{else}
	{addJsDef reduction_percent=0}
{/if}
{if $product->specificPrice && $product->specificPrice.reduction && $product->specificPrice.reduction_type == 'amount'}
	{addJsDef reduction_price=$product->specificPrice.reduction|floatval}
{else}
	{addJsDef reduction_price=0}
{/if}
{if $product->specificPrice && $product->specificPrice.price}
	{addJsDef specific_price=$product->specificPrice.price|floatval}
{else}
	{addJsDef specific_price=0}
{/if}
{addJsDef specific_currency=($product->specificPrice && $product->specificPrice.id_currency)|boolval} {* TODO: remove if always false *}
{addJsDef stock_management=$stock_management|intval}
{addJsDef taxRate=$tax_rate|floatval}
{addJsDefL name=doesntExist}{l s='This combination does not exist for this product. Please select another combination.' js=1}{/addJsDefL}
{addJsDefL name=doesntExistNoMore}{l s='This product is no longer in stock' js=1}{/addJsDefL}
{addJsDefL name=doesntExistNoMoreBut}{l s='with those attributes but is available with others.' js=1}{/addJsDefL}
{addJsDefL name=fieldRequired}{l s='Please fill in all the required fields before saving your customization.' js=1}{/addJsDefL}
{addJsDefL name=uploading_in_progress}{l s='Uploading in progress, please be patient.' js=1}{/addJsDefL}
{/strip}
{/if}

