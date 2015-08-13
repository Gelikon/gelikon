<!-- MODULE News CMS -->
<div id="cms_block" class="block">
    <a href="news.php"><h4>{if $title}{$title}{else}News{/if}</h4></a>
    <div class="block_content">
    <ul class="block_content">
	{foreach from=$news item=news}
	    <li>
	    <a href="cms.php?id_cms={$news.id}" title="{$news.title|escape:htmlall:'UTF-8'}"><b>{$news.title|escape:htmlall:'UTF-8'}</b></a>
	    {if $brief==1}
		<br>{$news.brief}&#8230; <a href="cms.php?id_cms={$news.id}" title="{$news.title|escape:htmlall:'UTF-8'}">&raquo; {l s='read more' mod='blockcms'}</a>

	    {/if}
	    </li>
	    
	{/foreach}
    </ul>
    <p align="right"><a href="news.php">view all news</a></p>
    </div>
</div>
<!-- /MODULE News CMS -->
