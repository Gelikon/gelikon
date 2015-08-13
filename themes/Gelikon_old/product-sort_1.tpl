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
{if isset($orderby) AND isset($orderway)}
{*}<ul class="display hidden-xs">
	<li class="display-title">{l s='View:'}</li>
    <li id="grid"><a rel="nofollow" href="#" title="{l s='Grid'}"><i class="icon-th-large"></i>{l s='Grid'}</a></li>
    <li id="list"><a rel="nofollow" href="#" title="{l s='List'}"><i class="icon-th-list"></i>{l s='List'}</a></li>
</ul>{*}
{* On 1.5 the var request is setted on the front controller. The next lines assure the retrocompatibility with some modules *}
{if !isset($request)}
	<!-- Sort products -->
	{if isset($smarty.get.id_category) && $smarty.get.id_category}
		{assign var='request' value=$link->getPaginationLink('category', $category, false, true)}
	{elseif isset($smarty.get.id_manufacturer) && $smarty.get.id_manufacturer}
		{assign var='request' value=$link->getPaginationLink('manufacturer', $manufacturer, false, true)}
	{elseif isset($smarty.get.id_supplier) && $smarty.get.id_supplier}
		{assign var='request' value=$link->getPaginationLink('supplier', $supplier, false, true)}
	{else}
		{assign var='request' value=$link->getPaginationLink(false, false, false, true)}
	{/if}
{/if}
<form id="productsSortForm{if isset($paginationId)}_{$paginationId}{/if}" action="{$request|escape:'html':'UTF-8'}" class="productsSortForm">
	<div class="select selector1 sort_select">
		<label for="selectProductSort{if isset($paginationId)}_{$paginationId}{/if}" style="float: left;margin-right: 20px;">{l s='Sort by'}</label>
		<select id="selectProductSort{if isset($paginationId)}_{$paginationId}{/if}" class="selectProductSort form-control">
                        
                        <option value="{$orderbydefault|escape:'html':'UTF-8'}:{$orderwaydefault|escape:'html':'UTF-8'}" {if $orderby eq $orderbydefault}selected="selected"{/if}>--</option>
                        
                        {if $orderby eq 'bestsale' AND $orderway eq 'asc'}
                        {else}
                        <option value="bestsale:asc" {if $orderby eq 'bestsale' AND $orderway eq 'asc'}selected="selected"{/if}>{l s='По бестселлерам вперед'}</option>
                        {/if}
                        {if $orderby eq 'bestsale' AND $orderway eq 'desc'}
                        {else}
			<option value="bestsale:desc" {if $orderby eq 'bestsale' AND $orderway eq 'desc'}selected="selected"{/if}>{l s='По бестселлерам назад'}</option>
                        {/if}
                        {if $orderby eq 'date_add' AND $orderway eq 'asc'}
                        {else}
                        <option value="date_add:asc" {if $orderby eq 'date_add' AND $orderway eq 'asc'}selected="selected"{/if}>{l s='По новизне вперед'}</option>
                        {/if}
                        {if $orderby eq 'date_add' AND $orderway eq 'asc'}
                        {else}
			<option value="date_add:desc" {if $orderby eq 'date_add' AND $orderway eq 'desc'}selected="selected"{/if}>{l s='По новизне назад'}</option>
                        {/if}
                        
		</select>
	</div>
</form>
<!-- /Sort products -->
	{if !isset($paginationId) || $paginationId == ''}
		{addJsDef request=$request}
	{/if}
{/if}

