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
{if count($categoryProducts) > 0 && $categoryProducts !== false}
<div class="blk gk-similar-products">
    <h2 class="gk-content-margin">{l s='Вам могут быть интересны:' mod='productcategory'}</h2>

    <div class="gk-catalog">
        <div class="gk-g-wrap">
            {foreach from=$categoryProducts item='categoryProduct' name=categoryProduct}
            <div class="gk-col gk-col3">
                <div class="gk-item {if $smarty.foreach.categoryProduct.iteration != 0 && ($smarty.foreach.categoryProduct.iteration)%4 == 0}last{/if}">
                    <div class="image">
                        <a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" style="background-image: url('{$link->getImageLink($categoryProduct.link_rewrite, $categoryProduct.id_image, 'home_default')|escape:'html':'UTF-8'}')" title="book"></a>
                    </div>
                    <div class="info">
                        <div class="author">
			            {foreach from=$categoryProduct.features item='feature' name=features}
			            {if $feature.id_feature == 9}
			            {$feature.value|escape:'htmlall':'UTF-8'}
			            {/if}
			            {/foreach}
        				</div>

                        <a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" class="title">{$categoryProduct.name|escape:'html':'UTF-8'}</a>
                        {*$categoryProduct|@var_dump*}
                        <div class="description">
                            {$categoryProduct.description_short}
                        </div>

                    </div>

                    <a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" class="btn">{l s='Узнать больше' mod='productscategory'}</a>
                    <div class="price">{if !$priceDisplay}{convertPrice price=$categoryProduct.price}{else}{convertPrice price=$categoryProduct.price_tax_exc}{/if} <a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" class="icon icon-cart"></a></div>
                    <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$categoryProduct.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить' mod='productscategory'}</a>

                </div>            
            </div>
            {/foreach}
        </div>
    </div>
</div>
{/if}


