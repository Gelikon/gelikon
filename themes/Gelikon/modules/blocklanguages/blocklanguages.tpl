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
<!-- Block languages module -->
{if count($languages) > 1}
	  <div class="gk-col">
		{foreach from=$languages key=k item=language name="languages"}
			{if $language.iso_code == $lang_iso}

			{/if}
		{/foreach}
		<ul class="nav nav-horizontal gk-lang-switch">
			{foreach from=$languages key=k item=language name="languages"}
				<li>

					{assign var=indice_lang value=$language.id_lang}
					{if isset($lang_rewrite_urls.$indice_lang)}

						<a href="{$lang_rewrite_urls.$indice_lang|escape:'html':'UTF-8'}" title="{$language.name}" {if $language.iso_code == $lang_iso}class="sel"{/if}>
					{else}
						<a href="{$link->getLanguageLink($language.id_lang)|escape:'html':'UTF-8'}" title="{$language.name}"{if $language.iso_code == $lang_iso}class="sel"{/if}>
					{/if}
						{$language.name|regex_replace:"/\s.*$/":""}
					</a>

				</li>
			{/foreach}
		</ul>
                {if isset($mod_translit) && $mod_translit}
                    {if $mod_translit == 'no'}
                        <a class="change_translit" rel="on" href="{*}{$smarty.server.REQUEST_URI}&translit=on{*}">{l s='Translit on' mod='blocklanguages'}</a>
                    {else}
                        <a class="change_translit" rel="off"  href="{*}{$smarty.server.REQUEST_URI}&translit=off{*}">{l s='Translit off' mod='blocklanguages'}</a>
                    {/if}
                {/if}
	</div>
{/if}
<!-- /Block languages module -->
