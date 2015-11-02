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
<div class="gk-lang gk-menu-lang">
    <a href="javascript:void(0);" class="btn-lang btn-lang-ru sel" rel="ru">{l s='на русском' mod='blockfooter'}</a>
    <a href="javascript:void(0);" class="btn-lang btn-lang-de" rel="de">{l s='на немецком' mod='blockfooter'}</a>
</div>
<div class="lang-panel lang-panel-ru">
    <div class="gk-g-wrap">
        <div class="gk-col gk-col6">

            <a href="{$link->getCategoryLink($category1.id_category, $category1.link_rewrite)|escape:'html':'UTF-8'}" class="main-cat">{$category1['name']}</a>
            <!--разделитель-->
            <div class="gk-separator gk-40"></div>
           
            <!--панель категорий, по аналогии делать для немецкого языка -->
            <div class="gk-g-wrap gk-book-cat">

                {if !empty($category1['children'])}
                    {$column=ceil(count($category1['children'])/2)}
                    {foreach from=$category1['children'] item=sub name=sub}
                        {if $smarty.foreach.sub.iteration == 1}
                            <div class="gk-col gk-col6">
                                
                                <ul class="nav nav-vertical gk-sub-cat">
                        {/if}

                                    <li><a href="{$link->getCategoryLink($sub.id_category, $sub.link_rewrite)|escape:'html':'UTF-8'}">{$sub['name']}</a></li>

                        {if $smarty.foreach.sub.iteration == $column}
                                </ul>
                            </div>
                            <div class="gk-col gk-col6">
                                
                                <ul class="nav nav-vertical gk-sub-cat">
                        {/if}
                        {if $smarty.foreach.sub.iteration == count($category1['children'])}
                                </ul>
                            </div>
                        {/if}
                    {/foreach}
                {/if}
                
            </div>
        </div>




        <div class="gk-col gk-col6">
            <div class="gk-g-wrap">
                <div class="gk-col gk-col4">
                    {if !empty($categories2)}
                        <div class="gk-borders-v">
                            {foreach from=$categories2 item=cat2 name=cat2}
                                <a href="{$link->getCategoryLink($cat2.id_category, $cat2.link_rewrite)|escape:'html':'UTF-8'}" class="main-cat">{$cat2['name']}</a>
                                {if !empty($cat2['children']) && $smarty.foreach.cat2.last}
                                    <ul class="nav nav-vertical gk-sub-cat">
                                        {foreach from=$cat2['children'] item=sub2 name=sub2}
                                            <li class="{if $smarty.foreach.sub2.last}gk-border-none{/if}"><a href="{$link->getCategoryLink($sub2.id_category, $sub2.link_rewrite)|escape:'html':'UTF-8'}">{$sub2['name']}</a></li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            {/foreach}
                        </div>
                    {/if}
                </div>
                <div class="gk-col gk-col8">
                    <div class="gk-g-wrap">
                        <div class="gk-col gk-col6">
                            {if $category3 != null}
                                <div class="gk-border-left gk-border-none">
                                    <a href="{$link->getCategoryLink($category3.id_category, $category3.link_rewrite)|escape:'html':'UTF-8'}" class="main-cat">{$category3['name']}</a>
                                    {if isset($category3['children'])}
                                        <ul class="nav nav-vertical gk-sub-cat">
                                            {foreach from=$category3['children'] item=cat3 name=cat3}
                                    
                                                <li class="{if $smarty.foreach.cat3.last}gk-border-none{/if}"><a href="{$link->getCategoryLink($cat3.id_category, $cat3.link_rewrite)|escape:'html':'UTF-8'}">{$cat3['name']}</a></li>
                                            {/foreach}
                                        </ul>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                        <div class="gk-col gk-col6">
                            {if $category4 != null}
                                <!--<div class="gk-border-left bbb">
                                    <a href="{$category4['link']}" class="main-cat">{$category4['name']}</a>
                                    {if !empty($category4['pages'])}
                                        <ul class="nav nav-vertical gk-sub-cat">
                                            {foreach from=$category4['pages'] item=page name='page'}
                                                <li class="{if $smarty.foreach.page.last}gk-border-none{/if}"><a href="{$page['link']}">{$page['meta_title']}</a></li>
                                            {/foreach}
                                        </ul>
                                    {/if}
                                </div>-->
                                <div class="gk-border-left bbb">
                                    <a href="/index.php?id_cms_category=2&amp;controller=cms&amp;id_lang=1" class="main-cat">Геликон</a>
                                                                            <ul class="nav nav-vertical gk-sub-cat">
                                                                                            <li class=""><a href="/index.php?id_cms=7&amp;controller=cms&amp;id_lang=1">Impressum</a></li>
                                                                                            <li class="feedback_form_link" title="{l s='Обратная связь'}"><a href="">{l s='Обратная связь'}</a></li>
                                                                                            <li class=""><a href="/index.php?id_cms=8&amp;controller=cms&amp;id_lang=1">Контакты</a></li>
                                                                                            <li class=""><a href="/index.php?id_cms=9&amp;controller=cms&amp;id_lang=1">Стоимость доставки</a></li>
                                                                                            <li class=""><a href="/index.php?id_cms=14&amp;controller=cms&amp;id_lang=1">AGB</a></li>
                                                                                            <li class=""><a href="/index.php?id_cms=15&amp;controller=cms&amp;id_lang=1">Datenschutz</a></li>
                                                                                            <li class=""><a href="/index.php?id_cms=16&amp;controller=cms&amp;id_lang=1">Widerruf</a></li>
                                                                                            <li class="gk-border-none"><a href="/index.php?id_cms=17&amp;controller=cms&amp;id_lang=1">Zahlung und Versand</a></li>
                                                                                    </ul>
                                                                    </div>
                            {/if}
                        </div>
                    </div>

                    <!--информация о доставке-->
                    <div class="gk-delivery">
                        <div class="image"></div>
                        <div class="description">
                            <p class="title gk-elem-first">{l s='Книжный магазин в Берлине.'  mod='blockfooter'}</p>

                            <p>{l s='Рассылка книг, фильмов, аудиокниг и прессы по Германии.'  mod='blockfooter'}</p>

                            <p><b>{l s='Доставка' mod='blockfooter'}</b> {l s='во Францию, Австрию, Нидерланды, Швейцарию и другие страны Европы.' mod='blockfooter'}
                            </p>
                        </div>
                    </div>
                    <!--<a class="feedback_form_link" title="{l s='Обратная связь'}" href="">{l s='Обратная связь'}</a>-->
                    <div class="gk-subscribe specnew">
                    <div class="title">Подписаться на новости</div>
                    
                    <div id="mc_embed_signup">
                        <form action="http://casetamatic.us3.list-manage.com/subscribe/post?u=35ec21a452d57143a16dce4b2&amp;id=5d8ceaa53a" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate="">
                            <div class="mc-field-group">
                                <label for="mce-EMAIL"></label>
                                <input type="email" value="" name="EMAIL" class="required email" placeholder="мой e-mail" id="mce-EMAIL">
                            </div>
                            <div id="mce-responses" class="clear">
                                <div class="response" id="mce-error-response" style="display:none"></div>
                                <div class="response" id="mce-success-response" style="display:none"></div>
                            </div>
                            

                            <div style="position: absolute; left: -5000px;">
                                <input type="text" name="b_35ec21a452d57143a16dce4b2_5d8ceaa53a" tabindex="-1" value="">
                            </div>
                            <input type="submit" value="ok" name="subscribe" id="mc-embedded-subscribe" class="button">

                            <div class="gk-clear-fix"></div>
                        </form>
                    </div>

                    </div>
                    <div style="display: none;">
                    <div class="box-modal" id="exampleModal">
                        <div class="box-modal_close arcticmodal-close">x</div>
                        <div>
                            <h4>{l s='Форма обратной связи' mod='blockfooter'}</h4>
                            <form action="" method="POST" id="feedback_form">
                                <div>
                                    <label for="feedback_email">{l s='Email' mod='blockfooter'}</label>
                                    <input placeholder="{l s='Email' mod='blockfooter'}" type="text" id="feedback_email" name="feedback_email" required="required"/>
                                </div>
                                <div>
                                    <label for="feedback_name">{l s='Имя' mod='blockfooter'}</label>
                                    <input placeholder="{l s='Имя' mod='blockfooter'}" type="text" id="feedback_name" name="feedback_name" required="required"/>
                                </div>
                                <div>
                                    <label for="feedback_mess">{l s='Сообщение' mod='blockfooter'}</label>
                                    <textarea placeholder="{l s='Сообщение' mod='blockfooter'}" name="feedback_mess" id="feedback_mess"></textarea>
                                </div>
                                <div>
                                    <button type="submit" name="feedbackSubmit" >{l s='Отправить' mod='blockfooter'}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    </div>    
                    
                    
                    {*}<div id="layer_feedback_form" style="display: none">
                        <div class="clearfix">
			<div class="layer_cart_product col-xs-12 col-md-6">
                            <span class="cross" title="{l s='Close window' mod='blockcart'}">X</span>
                            Форма связи
                        </div>    
                    </div>
                    <div class="layer_cart_overlay"></div>    
                    {*}
                </div>
            </div>
        </div>
    </div>
</div>

<!--панель категорий, по аналогии делать для немецкого языка -->
<div class="lang-panel lang-panel-de">
    <div class="gk-g-wrap">
        <div class="gk-col gk-col6">

            <a href="{$link->getCategoryLink($category1.id_category, $category1.link_rewrite)|escape:'html':'UTF-8'}" class="main-cat">{$category1['name']}</a>
            <!--разделитель-->
            <div class="gk-separator gk-40"></div>
            {$_category = $footer_category->getNestedCategories(68, 2, true, null)}
            <div class="gk-g-wrap gk-book-cat">
                {foreach from=$_category item=_cat}
                    {if !empty($_cat['children'])}
                        {$column=ceil(count($_cat['children'])/2)}
                        {foreach from=$_cat['children'] item=sub name=sub}
                            {if $smarty.foreach.sub.iteration == 1}
                                <div class="gk-col gk-col6">
                                    <ul class="nav nav-vertical gk-sub-cat">
                            {/if}

                                        <li><a href="{$link->getCategoryLink($sub.id_category, $sub.link_rewrite)|escape:'html':'UTF-8'}">{$sub['name']}</a></li>

                            {if $smarty.foreach.sub.iteration == $column}
                                    </ul>
                                </div>
                                <div class="gk-col gk-col6">
                                    <ul class="nav nav-vertical gk-sub-cat">
                            {/if}
                            {if $smarty.foreach.sub.iteration == count($_cat['children'])}
                                    </ul>
                                </div>
                            {/if}
                        {/foreach}
                    {/if}
                {/foreach}
            </div>
        </div>




        <div class="gk-col gk-col6">
            <div class="gk-g-wrap">
                <div class="gk-col gk-col4">
                    {if !empty($categories2)}
                        <div class="gk-borders-v">
                            {foreach from=$categories2 item=cat2 name=cat2}
                                <a href="{$link->getCategoryLink($cat2.id_category, $cat2.link_rewrite)|escape:'html':'UTF-8'}" class="main-cat">{$cat2['name']}</a>
                                {if !empty($cat2['children']) && $smarty.foreach.cat2.last}
                                    <ul class="nav nav-vertical gk-sub-cat">
                                        {foreach from=$cat2['children'] item=sub2 name=sub2}
                                            <li class="{if $smarty.foreach.sub2.last}gk-border-none{/if}"><a href="{$link->getCategoryLink($sub2.id_category, $sub2.link_rewrite)|escape:'html':'UTF-8'}">{$sub2['name']}</a></li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            {/foreach}
                        </div>
                    {/if}
                </div>
                <div class="gk-col gk-col8">
                    <div class="gk-g-wrap">
                        <div class="gk-col gk-col6">
                            {if $category3 != null}
                                <div class="gk-border-left gk-border-none">
                                    <a href="{$link->getCategoryLink($category3.id_category, $category3.link_rewrite)|escape:'html':'UTF-8'}" class="main-cat">{$category3['name']}</a>
                                    {if isset($category3['children'])}
                                        <ul class="nav nav-vertical gk-sub-cat">
                                            {foreach from=$category3['children'] item=cat3 name=cat3}
                                    
                                                <li class="{if $smarty.foreach.cat3.last}gk-border-none{/if}"><a href="{$link->getCategoryLink($cat3.id_category, $cat3.link_rewrite)|escape:'html':'UTF-8'}">{$cat3['name']}</a></li>
                                            {/foreach}
                                        </ul>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                        <div class="gk-col gk-col6">
                            {if $category4 != null}
                                <div class="gk-border-left ddd">
                                    <a href="{$category4['link']}" class="main-cat">{$category4['name']}</a>
                                    {if !empty($category4['pages'])}
                                        <ul class="nav nav-vertical gk-sub-cat">
                                            {foreach from=$category4['pages'] item=page name='page'}
                                                <li class="{if $smarty.foreach.page.last}gk-border-none{/if}"><a href="{$page['link']}">{$page['meta_title']}</a></li>
                                            {/foreach}
                                        </ul>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>

                    <!--информация о доставке-->
                    <div class="gk-delivery">
                        <div class="image"></div>
                        <div class="description">
                            <p class="title gk-elem-first">{l s='Книжный магазин в Берлине.' mod='blockfooter'}</p>

                            <p>{l s='Рассылка книг, фильмов, аудиокниг и прессы по Германии.' mod='blockfooter'}</p>

                            <p><b>{l s='Доставка' mod='blockfooter'}</b> {l s='во Францию, Австрию, Нидерланды, Швейцарию и другие страны Европы.' mod='blockfooter'}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
<!--панель копирайтов-->
<div class="gk-panel-copyright">
    <div class="gk-container">
        <a href="{$link->getCMSLink(14,'obsshie-usloviya-prodazhi')}">{l s='Наши общие условия продажи' mod='blockfooter'}</a>

        <p class="gk-float-R gk-elem-alone">© 2014, Gelikon Europe GmbH "Russische Bücher"</p>
    </div>
</div>
</div>
