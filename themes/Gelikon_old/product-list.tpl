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
{if isset($products) && $products}
	{*define numbers of product per line in other page for desktop*}
	{if $page_name !='index' && $page_name !='product'}
		{assign var='nbItemsPerLine' value=3}
		{assign var='nbItemsPerLineTablet' value=2}
		{assign var='nbItemsPerLineMobile' value=3}
	{else}
		{assign var='nbItemsPerLine' value=4}
		{assign var='nbItemsPerLineTablet' value=3}
		{assign var='nbItemsPerLineMobile' value=2}
	{/if}
	{*define numbers of product per line in other page for tablet*}
	{assign var='nbLi' value=$products|@count}
	{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
	{math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
	<!-- Products list -->
	
{addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
{addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
{addJsDef comparator_max_item=$comparator_max_item}
{addJsDef comparedProductsIds=$compared_products}
{/if}




<div class="gk-catalog">



<div class="gk-g-wrap">
        {foreach from=$products item=product name=products}

        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

        
    <!--пункт-->
    <div class="gk-col gk-col3">
        <div class="gk-item {if $smarty.foreach.products.iteration != 0 && ($smarty.foreach.products.iteration)%4 == 0}last{/if}">
            <div class="image">
                <a href="{$product.link|escape:'html':'UTF-8'}" style="background-image: url({$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'})" title="{$product.name|escape:'html':'UTF-8'}"></a>
            </div>
            <div class="info">
            <div class="author">
            {foreach from=$product.features item=feature name=features}
            {if $feature.id_feature == 9}
            {$feature.value|escape:'htmlall':'UTF-8'}
            {/if}
            {/foreach}</div>
                <a href="{$product.link|escape:'html':'UTF-8'}" class="title">{$product.name|escape:'html':'UTF-8'}</a>

                <div class="description">
                    {$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
                </div>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}<a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="icon icon-cart"></a></div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить'}</a>
        </div>
    </div>
    {if $smarty.foreach.products.iteration != 0 && $smarty.foreach.products.iteration%4 == 0}</div><div class="gk-g-wrap">{/if}
    
   {/foreach}
</div>
{*}
<div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        {if $smarty.foreach.products.iteration > 4} 
        {if $smarty.foreach.products.iteration <= 7 }  
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

        
    <!--пункт-->
    <div class="gk-col gk-col3">
        <div class="gk-item">
            <div class="image">
                <a href="{$product.link|escape:'html':'UTF-8'}" style="background-image: url({$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'})" title="{$product.name|escape:'html':'UTF-8'}"></a>
            </div>
            <div class="info">
            <div class="author">
            {foreach from=$product.features item=feature name=features}
            {if $feature.id_feature == 9}
            {$feature.value|escape:'htmlall':'UTF-8'}
            {/if}
            {/foreach}</div>
                <a href="{$product.link|escape:'html':'UTF-8'}" class="title">{$product.name|escape:'html':'UTF-8'}</a>

                <div class="description">
                    {$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
                </div>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}<a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="icon icon-cart"></a></div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить'}</a>
        </div>
    </div>
    {/if} 
    {/if} 
   {/foreach}
</div>

<div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

        
    <!--пункт-->
    <div class="gk-col gk-col3">
        <div class="gk-item">
            <div class="image">
                <a href="{$product.link|escape:'html':'UTF-8'}" style="background-image: url({$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'})" title="{$product.name|escape:'html':'UTF-8'}"></a>
            </div>
            <div class="info">
            <div class="author">
            {foreach from=$product.features item=feature name=features}
            {if $feature.id_feature == 9}
            {$feature.value|escape:'htmlall':'UTF-8'}
            {/if}
            {/foreach}</div>
                <a href="{$product.link|escape:'html':'UTF-8'}" class="title">{$product.name|escape:'html':'UTF-8'}</a>

                <div class="description">
                    {$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
                </div>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}<a href="#" class="icon icon-cart"></a></div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить'}</a>
        </div>
    </div>
  
   {/foreach}
</div>

<div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        {if $smarty.foreach.products.iteration > 11} 
        {if $smarty.foreach.products.iteration <= 15 }  
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

        
    <!--пункт-->
    <div class="gk-col gk-col3">
        <div class="gk-item">
            <div class="image">
                <a href="{$product.link|escape:'html':'UTF-8'}" style="background-image: url({$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'})" title="{$product.name|escape:'html':'UTF-8'}"></a>
            </div>
            <div class="info">
            <div class="author">
            {foreach from=$product.features item=feature name=features}
            {if $feature.id_feature == 9}
            {$feature.value|escape:'htmlall':'UTF-8'}
            {/if}
            {/foreach}</div>
                <a href="{$product.link|escape:'html':'UTF-8'}" class="title">{$product.name|escape:'html':'UTF-8'}</a>

                <div class="description">
                    {$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
                </div>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}<a href="#" class="icon icon-cart"></a></div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить'}</a>
        </div>
    </div>
    {/if} 
    {/if} 
   {/foreach}
</div>

<div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        {if $smarty.foreach.products.iteration > 15} 
        {if $smarty.foreach.products.iteration <= 19 }  
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

        
    <!--пункт-->
    <div class="gk-col gk-col3">
        <div class="gk-item">
            <div class="image">
                <a href="{$product.link|escape:'html':'UTF-8'}" style="background-image: url({$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'})" title="{$product.name|escape:'html':'UTF-8'}"></a>
            </div>
            <div class="info">
            <div class="author">
            {foreach from=$product.features item=feature name=features}
            {if $feature.id_feature == 9}
            {$feature.value|escape:'htmlall':'UTF-8'}
            {/if}
            {/foreach}</div>
                <a href="{$product.link|escape:'html':'UTF-8'}" class="title">{$product.name|escape:'html':'UTF-8'}</a>

                <div class="description">
                    {$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
                </div>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}<a href="#" class="icon icon-cart"></a></div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить'}</a>
        </div>
    </div>
    {/if} 
    {/if} 
   {/foreach}
</div>

<div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        {if $smarty.foreach.products.iteration > 19} 
        {if $smarty.foreach.products.iteration <= 23 }  
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

        
    <!--пункт-->
    <div class="gk-col gk-col3">
        <div class="gk-item">
            <div class="image">
                <a href="{$product.link|escape:'html':'UTF-8'}" style="background-image: url({$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'})" title="{$product.name|escape:'html':'UTF-8'}"></a>
            </div>
            <div class="info">
            <div class="author">
            {foreach from=$product.features item=feature name=features}
            {if $feature.id_feature == 9}
            {$feature.value|escape:'htmlall':'UTF-8'}
            {/if}
            {/foreach}</div>
                <a href="{$product.link|escape:'html':'UTF-8'}" class="title">{$product.name|escape:'html':'UTF-8'}</a>

                <div class="description">
                    {$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
                </div>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}<a href="#" class="icon icon-cart"></a></div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить'}</a>
        </div>
    </div>
    {/if} 
    {/if} 
   {/foreach}
</div>

<div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        {if $smarty.foreach.products.iteration > 23} 
        {if $smarty.foreach.products.iteration <= 27 }  
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

        
    <!--пункт-->
    <div class="gk-col gk-col3">
        <div class="gk-item">
            <div class="image">
                <a href="{$product.link|escape:'html':'UTF-8'}" style="background-image: url({$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'})" title="{$product.name|escape:'html':'UTF-8'}"></a>
            </div>
            <div class="info">
            <div class="author">
            {foreach from=$product.features item=feature name=features}
            {if $feature.id_feature == 9}
            {$feature.value|escape:'htmlall':'UTF-8'}
            {/if}
            {/foreach}</div>
                <a href="{$product.link|escape:'html':'UTF-8'}" class="title">{$product.name|escape:'html':'UTF-8'}</a>

                <div class="description">
                    {$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
                </div>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}<a href="#" class="icon icon-cart"></a></div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить'}</a>
        </div>
    </div>
    {/if} 
    {/if} 
   {/foreach}
</div>
{*}
</div>