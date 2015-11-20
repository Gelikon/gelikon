<div class="gk-g-wrap">
<div class="gk-col gk-col9">

        <!--слайдеп новостей-->
        <div class="gk-news-slider blk gk-elem-alone">
            <div class="title"><a href="{smartblog::GetSmartBlogLink('smartblog')}">{l s='Новости' mod='smartbloghomelatestnews'}</a></div>
            <div class="gk-control">
                <a href="#" class="arrow arrow-left"></a>
                <ul class="nav nav-horizontal gk-dots">

                </ul>
                <a href="#" class="arrow arrow-right"></a>
            </div>
            <div class="gk-clear-fix"></div>

            <div class="slide-wrap">

                 {if isset($view_data) AND !empty($view_data)}

            {assign var='i' value=1}

            {foreach from=$view_data item=post}

        
                    {assign var="options" value=null}

                    {$options.id_post = $post.id}

                    {$options.slug = $post.link_rewrite}

                <!--слайд-->
                <div class="slide">
                    <div class="title"><a href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}">{$post.title}</a></div>
                    <div class="description">
                        <a href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}"  class="text">
                            {$post.short_description|escape:'htmlall':'UTF-8'}
                        </a>
                        <div class="link"><a href="#" class="arrow arrow-right"></a></div>
                    </div>
                </div>

                {$i=$i+1}

            {/foreach}

        {/if}

                <div class="gk-clear-fix"></div>
            </div>
        </div>
    </div>

    <div class="gk-col gk-col3">

        <!--баннер-->
        <div class="gk-banner">
            <a href="#"><img src="images/banner.jpg" alt="banner"/></a>
        </div>
    </div>
</div>
<div class="gk-clear-fix"></div>