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
{counter name=active_ul assign=active_ul}
{if isset($products) && $products}
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
	
<div class="gk-catalog">
        <div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        {if $smarty.foreach.products.iteration <= 3} 

        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}

       
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
                <p class="availability_statut">
                    {*<span id="availability_label">{l s='Availability:'}</span>*}
                    <span id="availability_value"{if $product.quantity <= 0} class="warning_inline"{/if}>
                        {if $product.quantity <= 0}
                            {l s='This product is no longer in stock and can be brought to order in 2-3 weeks.' mod='homefeatured'}

                        {else}{*$product->available_now*}{l s='This product available now '}
                        {/if}
                    </span>
                </p>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее' mod='homefeatured'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"  data-id-product="{$product.id_product|intval}" class="icon icon-cart ajax_add_to_cart_button"></a>
            </div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" data-id-product="{$product.id_product|intval}" class="btn ajax_add_to_cart_button">{l s='Купить' mod='homefeatured'}</a>
        </div>
    </div>
    {/if}
   {/foreach}
   <div class="gk-col gk-col3">
        <div class="gk-subscribe">
            <div class="title">{l s='Subscribe to news' mod='homefeatured'}</div>
            <!--mc_embed_signup-->
            <div id="mc_embed_signup">
                <form action="http://casetamatic.us3.list-manage.com/subscribe/post?u=35ec21a452d57143a16dce4b2&amp;id=5d8ceaa53a" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate="">
                    <div class="mc-field-group">
                        <label for="mce-EMAIL"></label>
                        <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                    </div>
                    <div id="mce-responses" class="clear">
                        <div class="response" id="mce-error-response" style="display:none"></div>
                        <div class="response" id="mce-success-response" style="display:none"></div>
                    </div>
                    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->

                    <div style="position: absolute; left: -5000px;">
                        <input type="text" name="b_35ec21a452d57143a16dce4b2_5d8ceaa53a" tabindex="-1" value="">
                    </div>
                    <input type="submit" value="ok" name="subscribe" id="mc-embedded-subscribe" class="button">

                    <div class="gk-clear-fix"></div>
                </form>
            </div>

        </div>
    </div>
</div>
        <div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        {if $smarty.foreach.products.iteration > 3 }
        {if $smarty.foreach.products.iteration <= 7 }  
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}
      
    <div class="gk-col gk-col3">
        <div class="gk-item {if $smarty.foreach.products.iteration != 4 && ($smarty.foreach.products.iteration + 1)%4 == 0}last{/if}">
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
                <p class="availability_statut">
                {*<span id="availability_label">{l s='Availability:'}</span>*}
                <span id="availability_value"{if $product.quantity <= 0} class="warning_inline"{/if}>
                        {if $product.quantity <= 0}
                            {l s='This product is no longer in stock and can be brought to order in 2-3 weeks.' mod='homefeatured'}

                        {else}{*$product->available_now*}{l s='This product available now '}
                        {/if}
                    </span>
                </p>
            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее' mod='homefeatured'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"  data-id-product="{$product.id_product|intval}" class="icon icon-cart ajax_add_to_cart_button"></a>
            </div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" data-id-product="{$product.id_product|intval}" class="btn ajax_add_to_cart_button">{l s='Купить' mod='homefeatured'}</a>
        </div>
    </div>
    {/if}
    {/if}
   {/foreach}
    </div>
        <div class="gk-g-wrap">
        {foreach from=$products item=product name=products}
        {if $smarty.foreach.products.iteration > 7 }
        {if $smarty.foreach.products.iteration <= 11 }  
        {math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
        {math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
        {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
        {if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
        {if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}
      
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
                <p class="availability_statut">
                    {*<span id="availability_label">{l s='Availability:'}</span>*}
                    <span id="availability_value"{if $product.quantity <= 0} class="warning_inline"{/if}>
                        {if $product.quantity <= 0}
                            {l s='This product is no longer in stock and can be brought to order in 2-3 weeks.' mod='homefeatured'}

                        {else}{*$product->available_now*}{l s='This product available now ' mod='homefeatured'}
                        {/if}
                    </span>
                </p>

            </div>

            <a href="{$product.link|escape:'html':'UTF-8'}" class="btn">{l s='Подробнее' mod='homefeatured'}</a>

            <div class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}"  data-id-product="{$product.id_product|intval}" class="icon icon-cart ajax_add_to_cart_button"></a>
            </div>
            <a href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" class="btn">{l s='Купить' mod='homefeatured'}</a>
        </div>
    </div>
    {/if}
    {/if}
   {/foreach}
</div>

</div>
{else}
<ul id="homefeatured" class="homefeatured tab-pane{if isset($active_ul) && $active_ul == 1} active{/if}">
	<li class="alert alert-info">{l s='No featured products at this time.' mod='homefeatured'}</li>
</ul>
{/if}
{addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
{addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
{addJsDef comparator_max_item=$comparator_max_item}
{addJsDef comparedProductsIds=$compared_products}
{/if}
