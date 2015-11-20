{if $MENU != ''}
	<!-- Menu -->

<ul class="nav nav-horizontal gk-main-menu">
    {if isset($menu_item['cat'])}
        {foreach from=$menu_item['cat'] key=key item=menu name=menu}
            <li class="{if $smarty.foreach.menu.first}sel{/if}">
                <a href="{$link->getCategoryLink($menu.id_category, $menu.link_rewrite)|escape:'html':'UTF-8'}">{$menu.name}</a>
                {if !empty($menu['children'])}
                    <div class="hide-panel">
                        <div class="line" style="left: 50px!important; width: 190px"></div>
                        <div class="gk-g-wrap">
                            <div class="gk-col gk-col9">
                                <div class="list-wrap">
                                    <div class="gk-g-wrap {if $smarty.foreach.menu.first}lang-panel lang-panel-ru{/if}">
                                        {$column=ceil(count($menu['children'])/2)}
                                        {foreach from=$menu['children'] item=sub name=sub}
                                            {if $smarty.foreach.sub.iteration == 1}
                                                <div class="gk-col gk-col6">
                                                    <ul class="nav nav-vertical sub-menu-l1">
                                            {/if}

                                                        <li>
                                                            <a href="{$link->getCategoryLink($sub.id_category, $sub.link_rewrite)|escape:'html':'UTF-8'}">{$sub['name']}</a>
                                                            {if !empty($sub['children'])}
                                                                <ul class="nav nav-vertical sub-menu-l2">
                                                                    {foreach from=$sub['children'] item=item}
                                                                        <li><a href="{$link->getCategoryLink($item.id_category, $item.link_rewrite)|escape:'html':'UTF-8'}">{$item['name']}</a></li>
                                                                    {/foreach}
                                                                </ul>
                                                            {/if}
                                                        </li>

                                            {if $smarty.foreach.sub.iteration == $column}
                                                    </ul>
                                                </div>
                                                <div class="gk-col gk-col6">
                                                    <ul class="nav nav-vertical sub-menu-l1">
                                            {/if}
                                            {if $smarty.foreach.sub.iteration == count($menu['children'])}
                                                    </ul>
                                                </div>
                                            {/if}
                                        {/foreach}
                                    </div>
                                    {if $smarty.foreach.menu.first}
                                        <div class="gk-g-wrap lang-panel lang-panel-de">
                                            {$_category = $category->getNestedCategories(68, 2, true, null)}
                                            {foreach from=$_category item=_cat}
                                                {foreach from=$_cat['children'] item=_sub name=_sub}
                                                    {if $smarty.foreach._sub.iteration == 1}
                                                        <div class="gk-col gk-col6">
                                                            <ul class="nav nav-vertical sub-menu-l1">
                                                    {/if}

                                                                <li>
                                                                    <a href="{$link->getCategoryLink($sub.id_category, $sub.link_rewrite)|escape:'html':'UTF-8'}">{$_sub['name']}</a>
                                                                    {if !empty($_sub['children'])}
                                                                        <ul class="nav nav-vertical sub-menu-l2">
                                                                            {foreach from=$_sub['children'] item=_item}
                                                                                <li><a href="{$link->getCategoryLink($item.id_category, $item.link_rewrite)|escape:'html':'UTF-8'}">{$_item['name']}</a></li>
                                                                            {/foreach}
                                                                        </ul>
                                                                    {/if}
                                                                </li>

                                                    {if $smarty.foreach._sub.iteration == $column}
                                                            </ul>
                                                        </div>
                                                        <div class="gk-col gk-col6">
                                                            <ul class="nav nav-vertical sub-menu-l1">
                                                    {/if}
                                                    {if $smarty.foreach._sub.iteration == count($_cat['children'])}
                                                            </ul>
                                                        </div>
                                                    {/if}
                                                {/foreach}
                                            {/foreach}
                                        </div>
                                    {/if}
                                </div>
                            </div>
                            {if $smarty.foreach.menu.first}
                                <div class="gk-col gk-col3">
                                    <div class="gk-menu-lang-wrap">
                                        <div class="title">Язык:</div>
                                        <div class="gk-menu-lang">
                                            <a href="#" class="btn btn-lang btn-lang-ru sel" rel="ru">{l s='на русском' mod='blocktopmenu'}</a>
                                            <a href="#" class="btn btn-lang btn-lang-de" rel="de">{l s='на немецком' mod='blocktopmenu'}</a>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/if}
            </li>            
        {/foreach}
    {/if}
</ul>

<!--<ul class="nav nav-horizontal gk-main-menu">
    <li class="sel"><a href="#">книги</a>

        <!--скрытыя панел можно по аналогии добавить в любой пунк меню-->
        <!--<div class="hide-panel">
            <div class="line" style="left: 50px!important; width: 190px"></div>

            <div class="gk-g-wrap">
                <div class="gk-col gk-col9">
                    <div class="list-wrap">

                        <!--панель категорий, по аналогии делать для немецкого языка -->
                        <!--<div class="gk-g-wrap lang-panel">
                            <div class="gk-col gk-col6">
                                <ul class="nav nav-vertical sub-menu-l1">
                                    <li><a href="#">Биографии. Воспоминания</a></li>
                                    <li><a href="#">Букинистика</a></li>
                                    <li><a href="#">Естественные науки</a></li>
                                    <li><a href="#">Здоровье. Медицина </a></li>
                                    <li><a href="#">Иностранные языки. Языкознание</a></li>
                                    <li><a href="#">История</a></li>
                                    <li><a href="#">Книги-билингвы</a></li>
                                    <li><a href="#">Компьютерная литература</a></li>
                                    <li><a href="#">Кулинария</a>
                                        <ul class="nav nav-vertical sub-menu-l2">
                                            <li><a href="#">Культурные альбомы</a></li>
                                            <li><a href="#">Альбомы с фотографмями</a></li>
                                            <li><a href="#">Искусство Китая</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="#">Культура. Искусство. Альбомы</a></li>
                                    <li><a href="#">Литературоведение. Языкознание</a></li>
                                </ul>
                            </div>
                            <div class="gk-col gk-col6">
                                <ul class="nav nav-vertical sub-menu-l1">
                                    <li><a href="#">Музыка</a></li>
                                    <li><a href="#">Немецкий язык и Германия</a></li>
                                    <li><a href="#">Подписные книжные издания</a></li>
                                    <li><a href="#">Психология</a></li>
                                    <li><a href="#">Путешествия</a></li>
                                    <li><a href="#">Религия. Религиоведение. Эзотерика</a></li>
                                    <li><a href="#">Справочники. Энциклопедии. Словари</a></li>
                                    <li><a href="#">Философия</a></li>
                                    <li><a href="#">Художественная литература</a>
                                        <ul class="nav nav-vertical sub-menu-l2">
                                            <li><a href="#">Культурные альбомы</a></li>
                                            <li><a href="#">Альбомы с фотографмями</a></li>
                                            <li><a href="#">Искусство Китая</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="#">Хобби. Творчество</a></li>
                                    <li><a href="#">Разное</a></li>
                                </ul>
                            </div>
                        </div>

                        <!--пример для немецкого языка-->
                        <!--<div class="gk-g-wrap lang-panel">
                            <h1 style="color:#000">меню на немецком</h1>

                            <div class="gk-col gk-col6">
                                <ul class="nav nav-vertical sub-menu-l1">
                                    <li><a href="#">Биографии. Воспоминания</a></li>
                                    <li><a href="#">Букинистика</a></li>
                                    <li><a href="#">Книги-билингвы</a></li>
                                    <li><a href="#">Компьютерная литература</a></li>
                                    <li><a href="#">Кулинария</a>
                                        <ul class="nav nav-vertical sub-menu-l2">
                                            <li><a href="#">Культурные альбомы</a></li>
                                            <li><a href="#">Альбомы с фотографмями</a></li>
                                            <li><a href="#">Искусство Китая</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="#">Культура. Искусство. Альбомы</a></li>
                                    <li><a href="#">Литературоведение. Языкознание</a></li>
                                </ul>
                            </div>
                            <div class="gk-col gk-col6">
                                <ul class="nav nav-vertical sub-menu-l1">
                                    <li><a href="#">Музыка</a></li>
                                    <li><a href="#">Немецкий язык и Германия</a></li>
                                    <li><a href="#">Подписные книжные издания</a></li>

                                    <li><a href="#">Художественная литература</a>
                                        <ul class="nav nav-vertical sub-menu-l2">
                                            <li><a href="#">Культурные альбомы</a></li>
                                            <li><a href="#">Альбомы с фотографмями</a></li>
                                            <li><a href="#">Искусство Китая</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="#">Хобби. Творчество</a></li>
                                    <li><a href="#">Разное</a></li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

                <!--меню выбора языков, кол-во пунктов соответствует кол-ву панелей(.lang-panel)-->
                <!--<div class="gk-col gk-col3">
                    <div class="gk-menu-lang-wrap">
                        <div class="title">Язык:</div>
                        <div class="gk-menu-lang">
                            <a href="#" class="btn btn-lang sel">на русском</a>
                            <a href="#" class="btn btn-lang">на немецком</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </li>
    <li><a href="#">периодика</a></li>
    <li><a href="#">музыка</a></li>
    <li><a href="#">кино</a></li>
    <li><a href="#">дети</a></li>
    <li><a href="#">прочее</a></li>
</ul>
	<!--/ Menu -->
{/if}



