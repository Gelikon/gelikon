{if $content_only}
    {if $title}<h2 class="category_title">{$title}</h2>{else}<h2 class="category_title">News</h2>{/if}</h2>
    <div style="text-align:left; padding:10px;" class="rte">
	{foreach from=$news item=news}
	    <p>
	    <a href="cms.php?id_cms={$news.id}" title="{$news.title|escape:htmlall:'UTF-8'}"><b>{$news.title|escape:htmlall:'UTF-8'}</b></a>
		<br>{$news.brief}&#8230; <a href="cms.php?id_cms={$news.id}" title="{$news.title|escape:htmlall:'UTF-8'}">&raquo;{l s='read more' mod='blockcms'}</a>
	    </p>
	{/foreach}
    </div>
    {if $pagin == 1}
	<div class="rte pagination">
	    {if $next <= $max-1}<div style="float:left;width:auto;">&laquo;&nbsp;<a href="news.php?page={$next}">{l s='Previous'}</a></div>{/if}
	    {if $prev >= 0}<div style="float:right;width:auto;"><a href="news.php?page={$prev}">{l s='Next'}</a>&nbsp;&raquo;</div>{/if}
	</div><br class="clear" />
    {/if}
{else}
    {if $title}<h2 class="category_title">{$title}</h2>{else}<h2 class="category_title">News</h2>{/if}</h2>
    <div class="rte">
	{foreach from=$news item=news}
	    <p>
	    <a href="cms.php?id_cms={$news.id}" title="{$news.title|escape:htmlall:'UTF-8'}"><b>{$news.title|escape:htmlall:'UTF-8'}</b></a>
		<br>{$news.brief}&#8230; <a href="cms.php?id_cms={$news.id}" title="{$news.title|escape:htmlall:'UTF-8'}">&raquo;{l s='read more' mod='blockcms'}</a>
	    </p>
	{/foreach}
    </div>
    {if $pagin == 1}
	<div class="rte pagination">
	    {if $next <= $max-1}<div style="float:left;width:auto;">&laquo;&nbsp;<a href="news.php?page={$next}">{l s='Previous'}</a></div>{/if}
	    {if $prev >= 0}<div style="float:right;width:auto;"><a href="news.php?page={$prev}">{l s='Next'}</a>&nbsp;&raquo;</div>{/if}
	</div><br class="clear" />
    {/if}
{/if}    
<br />
{if !$content_only}
<p><a href="{$base_dir}" title="{l s='Home'}"><img src="{$img_dir}icon/home.gif" alt="{l s='Home'}" class="icon" /></a><a href="{$base_dir}" title="{l s='Home'}">{l s='Home'}</a></p>
{/if}

