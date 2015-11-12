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
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


{if $page_name =='index'}
<!--промо-->
<!--
<div class="gk-panel gk-panel-promo">

    <!--промо-слайдер-->
<!--    <div class="gk-promo-slider">
        <div class="gk-container">
            <div class="general-info">
                <div class="title">
                    {l s='новинки' mod='homeslider'}
                    <b>{l s='геликона' mod='homeslider'}</b>     
                </div>
                <div class="gk-control">
                    <a href="#" class="arrow arrow-left"></a>
                    <ul class="nav nav-horizontal gk-dots">

                    </ul>
                    <a href="#" class="arrow arrow-right"></a>
                </div>
            </div>

        </div>


        {foreach from=$homeslider_slides item=slide}
           {if $slide.active}
        <!--слайд-->
 <!--       <a href="{$slide.url|escape:'html':'UTF-8'}" class="slide" style="background-image: url({$link->getMediaLink("`$smarty.const._MODULE_DIR_`homeslider/images/`$slide.image|escape:'htmlall':'UTF-8'`")})">
            <div class="gk-container">
                <div class="slide-info">
                    <div class="description">
                        
                        <div class="title">{$slide.description}</div>
                        <div class="signature">{$slide.legend|escape:'htmlall':'UTF-8'}</div>
                    </div>
                    <div class="image">


                        <img src="/modules/homeslider/images/imgbg{$slide.id_slide}.jpg" alt="book">
                    </div>
                </div>
            </div>
            
        </a>
        {/if}
        {/foreach}
        <div class="gk-clear-fix"></div>
    </div>
</div>
-->
{/if}

