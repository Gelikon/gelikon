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
<!DOCTYPE HTML>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7 " lang="{$lang_iso}"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8 ie7" lang="{$lang_iso}"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9 ie8" lang="{$lang_iso}"><![endif]-->
<!--[if gt IE 8]> <html class="no-js ie9" lang="{$lang_iso}"><![endif]-->
<html lang="{$lang_iso}">
    <head>
        <meta charset="utf-8" />
        <title>{$meta_title|escape:'html':'UTF-8'}</title>
{if isset($meta_description) AND $meta_description}
        <meta name="description" content="{$meta_description|escape:'html':'UTF-8'}" />
{/if}
{if isset($meta_keywords) AND $meta_keywords}
        <meta name="keywords" content="{$meta_keywords|escape:'html':'UTF-8'}" />
{/if}
        <meta name="generator" content="PrestaShop" />
        <meta name="robots" content="{if isset($nobots)}no{/if}index,{if isset($nofollow) && $nofollow}no{/if}follow" />
        <meta name="viewport" content="width=device-width, minimum-scale=0.25, maximum-scale=1.6, initial-scale=1.0" /> 
        <meta name="apple-mobile-web-app-capable" content="yes" /> 
        <link rel="icon" type="image/vnd.microsoft.icon" href="{$favicon_url}?{$img_update_time}" />
        <link rel="shortcut icon" type="image/x-icon" href="{$favicon_url}?{$img_update_time}" />
        {*}
         <script type="text/javascript" src="/themes/Gelikon/js/css.js"></script>
         <script type="text/javascript" src="/themes/Gelikon/js/plugins.js"></script>
         <script type="text/javascript" src="/themes/Gelikon/js/main-script.js"></script>
        {*} 
         

{if isset($css_files)}
    {foreach from=$css_files key=css_uri item=media}
        <link rel="stylesheet" href="{$css_uri}" type="text/css" media="{$media}" />
    {/foreach}
{/if}
        {$HOOK_HEADER}
        <link rel="stylesheet" href="http{if Tools::usingSecureMode()}s{/if}://fonts.googleapis.com/css?family=Open+Sans:300,600" type="text/css" media="all" />
        <link rel="stylesheet" href="/js/jquery/plugins/arcticmodal/jquery.arcticmodal-0.3.css" type="text/css" media="all" />
        <link rel="stylesheet" href="/js/jquery/plugins/arcticmodal/themes/simple.css" type="text/css" media="all" />
        <!--[if IE 8]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body{if isset($page_name)} id="{$page_name|escape:'html':'UTF-8'}"{/if} class="{if isset($page_name)}{$page_name|escape:'html':'UTF-8'}{/if}{if isset($body_classes) && $body_classes|@count} {implode value=$body_classes separator=' '}{/if}{if $hide_left_column} hide-left-column{/if}{if $hide_right_column} hide-right-column{/if}{if $content_only} content_only{/if} lang_{$lang_iso}">
    {if !$content_only}
        {if isset($restricted_country_mode) && $restricted_country_mode}
            <div id="restricted-country">
                <p>{l s='You cannot place a new order from your country.'} <span class="bold">{$geolocation_country}</span></p>
            </div>
        {/if}
            <div class="gk-container-main">
                {if isset($best_presetage) && $best_presetage}
                    <div class="gk-panel-msg">
                        <div class="msg msg-error">
                            <div class="gk-container">
                                <span>{l s='Ваша скидка'} <a href="#">{$best_presetage}%</a></span>
                                <a href="#" class="icon icon-delete"></a>
                            </div>
                        </div>
                    </div>
                {/if}
            <div class="gk-panel gk-panel-header">
            <div class="gk-container">
            <div class="gk-g-wrap">
            <div class="gk-col gk-col3">
                    <!--логотип-->
                    <div class="gk-blk-logo" onclick="location.href='{$base_dir}';"></div>

                    <!--о магазине-->
                    <div class="gk-about-shop">
                        <a href="{$link->getCmsLink(8)}">{l s='о магазине'}</a>
                    </div>

                    </div>
                    <div class="gk-col gk-col9">
                        <div class="gk-g-wrap">

                            <!--энциклопедия gelicon-->
                            <div class="gk-col">
                                <div class="gk-border-h gk-encyclopedia">
                                    <a href="{$link->getPageLink('new-products')}">{l s='Новинки'}</a>
                                </div>
                            </div>
                            {hook h="displayNav"}   
                            {if isset($HOOK_TOP)}{$HOOK_TOP}{/if}   
                        </div>

                         {*hook h="displayPaymentTop"*}
                         {hook h='displayTopMenu'}

                               <!--выбор языка предлогаемого контента-->
                                <ul class="nav nav-horizontal gk-lang">
                                    <li><a href="javascript:void(0);" class="btn-lang btn-lang-ru sel" rel="ru">{l s='на русском'}</a></li>
                                    <li><a href="javascript:void(0);" class="btn-lang btn-lang-de" rel="de">{l s='на немецком'}</a></li>
                                </ul>
                                </div>
                                </div>

                                <!--контакты в шапке-->
                                <div class="gk-border-h gk-contacts">
                                    <div class="gk-g-wrap">
                                        <div class="gk-col">
                                            <b>{l s='Телефоны:'}</b> <span> {l s='phone_header'}{*+49 30 3234815*}</span>
                                        </div>
                                        <div class="gk-col">
                                            <b>{l s='Адрес:'}</b> <span> {l s='adress_header'}{*}Kantstraße 84, 10627 Berlin, Germany{*} </span>
                                        </div>
                                        <div class="gk-col gk-float-R">
                                            <b>{l s='Время работы:'}</b> {l s='time_header'} <span>{*10:00 — 18:00*}</span>
                                        </div>
                                    </div>
                                </div>
                </div>
                </div>
                <div class="gk-panel gk-panel-mini-header">
<div class="gk-container">
<div class="gk-g-wrap">

<!--логотип-->
<div class="gk-col">
    <div class="gk-blk-logo"></div>
</div>

<!--основное меню-->
<div class="gk-col">
{hook h='displayTopMenu'}
</div>

<!--энциклопедия gelicon-->
<div class="gk-col">
    <div class="gk-col">
        <div class="gk-border-h gk-encyclopedia">
            <a href="{$link->getPageLink('new-products')}">{l s='Новинки'}</a>
        </div>
    </div>
</div> 

{hook h="displayMaintenance"} 

{if isset($HOOK_TOP)}{$HOOK_TOP}{/if} 


</div>
</div>
</div>
           
                   
                </header>
            </div>
            <div class="gk-container-main">
                    {if $page_name !='index' && $page_name !='pagenotfound'}
                        
                    {/if}
                        {hook h="displayTopColumn"}

                        {if isset($left_column_size) && !empty($left_column_size)}
                            {$HOOK_LEFT_COLUMN}
                        {/if}
                        
    {/if}
                <div class="gk-panel gk-panel-content">
                <div class="gk-container" id="center_column">





