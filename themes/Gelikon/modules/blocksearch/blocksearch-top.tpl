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
<!-- Block search module TOP -->
<div class="gk-col">
	    <div class="gk-search-form">
        <div class="gk-borders">
            <a href="#" class="icon icon-search btn-open"></a>
        </div>

        <div class="hide-panel">
			<form id="searchbox" method="get" action="{$link->getPageLink('search')|escape:'html':'UTF-8'}" >
				<div class="gk-borders">
                    <div class="control-row">
                        <div class="control-widget">
                            <div class="icon icon-keyboard"></div>
								<input type="hidden" name="controller" value="search" />
								<input type="hidden" name="orderby" value="quantity" />
								<input type="hidden" name="orderway" value="desc" />
								<input autofocus class="search-input" type="text" id="search_query_top" name="search_query" placeholder="{l s='Search' mod='blocksearch'}" value="{$search_query|escape:'htmlall':'UTF-8'|stripslashes}" />
						</div>
                    </div>
                    <div class="control-row">
                        <div class="control-widget">
                            <select name="id_category_search" id="id_category_search">
                                <option value="all">{l s='Все категории' mod='blocksearch'}</option>
                                {foreach from=$start_cats item=start_cat}
                                    <option value="{$start_cat.id}">{$start_cat.name}</option>
                                {/foreach}
                                <option value="authors">{l s='В авторах' mod='blocksearch'}</option>
                            </select>
                        </div>
                    </div>
								<button type="submit" name="submit_search" class="icon icon-search">
								</button>
				</div>

	</form>
</div>
{include file="$self/blocksearch-instantsearch.tpl"}
</div>
</div>
<!-- /Block search module TOP -->