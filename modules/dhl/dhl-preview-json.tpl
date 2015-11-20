<script type="text/javascript" src="{$this_dhl_path}js/dhl-preview.js"></script>
<script type="text/javascript" src="{$this_dhl_path}js/statesManagement.js"></script>
<script type="text/javascript">
	//<![CDATA[
	dhl_countries = new Array();
	{foreach from=$dhl_countries item='country'}
		{if isset($country.states) && $country.contains_states}
			dhl_countries[{$country.id_country|intval}] = new Array();
			{foreach from=$country.states item='state' name='states'}
				dhl_countries[{$country.id_country|intval}].push({ldelim}'id' : '{$state.id_state}', 'name' : '{$state.name|escape:'htmlall':'UTF-8'}'{rdelim});
			{/foreach}
		{/if}
	{/foreach}
	
{if $psVersion == 1.6}
	$(document).ready(function(){
		if(typeof uniform == 'function')
		{
			$("#dhl_dest_country{$dhl_is_cart}, #dhl_dest_state{$dhl_is_cart}, #dhl_dest_city{$dhl_is_cart}, #dhl_dest_zip{$dhl_is_cart}").uniform();
			
			$("#uniform-dhl_dest_country{$dhl_is_cart}, #uniform-dhl_dest_state{$dhl_is_cart}").css('width', '100%');
			$("#uniform-dhl_dest_country{$dhl_is_cart}, #uniform-dhl_dest_state{$dhl_is_cart}").children("span").css('width', '100%');
		}
	});
{/if}
</script>

{** IF DESTINATION COUNTRY IS NOT SELECTED YET *}
{if (!isset($dhl_dest_country) || !$dhl_dest_country) && isset($dhl_default_country)}
	{assign var='dhl_dest_country' value=$dhl_default_country}
{/if}
{***}       
	  
{** GET SHIPMENT VARIALBES *}
{assign var="shippingDetails" value=""}

{* IF COUNTRY FIELD IS DISPLAYING AND IT IS DEFINED *}
{if $dhl_address_display.country && isset($dhl_dest_country) && $dhl_dest_country}
	{* CONCAT COUNTRY TO SHIPPING DETAILS *}
	{assign var="shippingDetails" value="`$shippingDetails` `$dhl_dest_country`"} 
{/if}

{* IF STATE FIELD IS DISPLAYING AND IT IS DEFINED *}
{if $dhl_address_display.state && isset($dhl_dest_state_name) && $dhl_dest_state_name}
	{* IF COUNTRY FIELD IS DISPLAYING AND IT IS DEFINED *}
	{if $dhl_address_display.country && isset($dhl_dest_country) && $dhl_dest_country}
		{* CONCAT STATE TO SHIPPING DETAILS *}
		{assign var="shippingDetails" value="`$shippingDetails`, `$dhl_dest_state_name`"}
	{else}
		{* CONCAT STATE TO SHIPPING DETAILS *}
		{assign var="shippingDetails" value="`$shippingDetails` `$dhl_dest_state_name`"}
	{/if} 
{/if}

{* IF CITY FIELD IS DISPLAYING AND IT IS DEFINED *}
{if $dhl_address_display.city && isset($dhl_dest_city) && $dhl_dest_city}
	{* IF COUNTRY AND STATE FIELDS ARE DISPLAYING AND ARE DEFINED *}
	{if ($dhl_address_display.country && isset($dhl_dest_country) && $dhl_dest_country) || ($dhl_address_display.state && isset($dhl_dest_state_name) && $dhl_dest_state_name)}
		{* CONCAT CITY TO SHIPPING DETAILS *}
		{assign var="shippingDetails" value="`$shippingDetails` - `$dhl_dest_city`"}
	{else}
		{* CONCAT CITY TO SHIPPING DETAILS *}
		{assign var="shippingDetails" value="`$shippingDetails` `$dhl_dest_city`"}
	{/if} 
{/if}

{* IF CITY FIELD IS DISPLAYING AND IT IS DEFINED *}
{if $dhl_address_display.zip && isset($dhl_dest_zip) && $dhl_dest_zip}
	{* IF COUNTRY, STATE AND CITY FIELDS ARE DISPLAYING AND ARE DEFINED *}
	{if ($dhl_address_display.country && isset($dhl_dest_country) && $dhl_dest_country) 
	|| ($dhl_address_display.state && isset($dhl_dest_state_name) && $dhl_dest_state_name)
	|| ($dhl_address_display.city && isset($dhl_dest_city) && $dhl_dest_city)}
		{* CONCAT ZIP CODE TO SHIPPING DETAILS *}
		{assign var="shippingDetails" value="`$shippingDetails`, `$dhl_dest_zip`"}
	{else}
		{* CONCAT ZIP CODE TO SHIPPING DETAILS *}
		{assign var="shippingDetails" value="`$shippingDetails` `$dhl_dest_zip`"}
	{/if} 
{/if}
{***}

<div class="dhl_preview_container{$dhl_is_cart}" {if $psVersion == 1.6}style="margin:10px 0;float: none; {if $dhl_is_cart != ''}background-color: #f6f6f6; padding: 0 0 10px 0;{/if}"{/if}>
						  
	<div id="dhl_address{$dhl_is_cart}">
		<a href="javascript:void(0)" id="dhl_shipping_rates_button" class="dhl_hide_button">X</a>
		
		<span style="font-weight: bold;">
			{$shippingDetails}
		</span>
		<span class="dhl_pointer" onclick="     
			$(this).hide();
			$('#dhl_rate_content{$dhl_is_cart}').css('display','none');
			$('#dhl_dest_change{$dhl_is_cart}').fadeIn(800);
		">({l s='change' mod='dhl'})</span>
	</div>

	<div id="dhl_dest_change{$dhl_is_cart}">
	
		{assign var='inLine' value=0}  
		<div id="dhl_line" {if $psVersion == 1.6}style="padding: 0 5px;"{/if}>
			
			{if $dhl_address_display.country}
				{assign var='inLine' value=$inLine+1}
				{*<div style="float: {if (!$dhl_is_cart && $inLine == 1)}right{else}left{/if};">*}
					<span>
						<select onchange="dhl_preview_update_state('{$dhl_is_cart}', 0)" name="dhl_dest_country{$dhl_is_cart}" id="dhl_dest_country{$dhl_is_cart}">
							{foreach from=$dhl_countries item=dhl_country}
								<option value="{$dhl_country.id_country}" {if isset($dhl_dest_country) && $dhl_dest_country == $dhl_country.iso_code} selected="selected"{/if}>{$dhl_country.name|escape:'htmlall':'UTF-8'}</option>
							{/foreach}
						</select>
					</span>
				{*</div>*}
			{/if}
			
		{if (!$dhl_is_cart && $inLine == 1) || ($dhl_is_cart && $inLine == 1)}
			</div>
			{assign var='inLine' value=0}
			<div id="dhl_line" {if $psVersion == 1.6}style="padding: 0 5px;"{/if}>
		{/if}
			
			{if $dhl_address_display.state}
				{assign var='inLine' value=$inLine+1}
				{*<div style="float: {if (!$dhl_is_cart && $inLine == 1)}right{else}left{/if};">*}
					<span>
						<select name="dhl_dest_state{$dhl_is_cart}" id="dhl_dest_state{$dhl_is_cart}">
							<option value="">-- {l s='State' mod='dhl'} --</option>
						</select>
					</span>
				{*</div>*}
			{/if}
				
		{if (!$dhl_is_cart && $inLine == 1) || ($dhl_is_cart && $inLine == 1)}
			</div>
			{assign var='inLine' value=0}
			<div id="dhl_line" {if $psVersion == 1.6}style="padding: 0 5px;"{/if}>
		{/if}

			{if $dhl_address_display.city}
				{assign var='inLine' value=$inLine+1}
				{*<div style="float: {if (!$dhl_is_cart && $inLine == 1)}right{else}left{/if};">*}
					<span>
						<input placeholder="{l s='City Name' mod='dhl'}" type="text" name="dhl_dest_city{$dhl_is_cart}" id="dhl_dest_city{$dhl_is_cart}"
							{if isset($dhl_dest_city) && $dhl_dest_city != ''}
								value="{$dhl_dest_city}"
							{elseif $dhl_is_cart != ''}
								value="{l s='City' mod='dhl'}" 
								onclick="$(this).val('')"
							{/if} 
						/>
					</span>
				{*</div>*}
			{/if}
				
		{if (!$dhl_is_cart && $inLine == 1) || ($dhl_is_cart && $inLine == 1)}
			</div>
			{assign var='inLine' value=0}
			<div id="dhl_line" {if $psVersion == 1.6}style="padding: 0 5px;"{/if}>
		{/if}
				
			{if $dhl_address_display.zip}
				{assign var='inLine' value=$inLine+1}
				{*<div style="float: {if (!$dhl_is_cart && $inLine == 1)}right{else}left{/if};">*}
					<span>
						<input placeholder="{l s='Zip Code' mod='dhl'}" type="text" {if !$dhl_address_display.zip}hide="1"{/if} name="dhl_dest_zip{$dhl_is_cart}" id="dhl_dest_zip{$dhl_is_cart}"
							{if isset($dhl_dest_zip) && $dhl_dest_zip != ''}
								value="{$dhl_dest_zip}"
							{elseif $dhl_is_cart != ''}
								value="{l s='Zip' mod='dhl'}" 
								onclick="$(this).val('')"
							{/if}
						/>
					</span>
				{*</div>*}
			{/if}
			
		</div>                   

		<span class="submit_button">
			<a href="javascript:void(0)" id="dhl_submit_location{$dhl_is_cart}" onclick="dhl_get_rates(true,'{$dhl_is_cart}')" class="{if $psVersion == 1.6}btn btn-default{else}exclusive{/if}" style="{if $dhl_is_cart != ''}margin: auto;{/if} {if $psVersion == 1.6}color: #333333;{/if}">{l s='Submit' mod='dhl'}</a>
		</span>
			
	</div>
	<div id="dhl_rate_content{$dhl_is_cart}" {if $psVersion == 1.6}style="padding: 0 5px;"{/if}>
		{if $dhl_is_cart != '' && $dhl_cart_products == 0}
			<div><b>{l s='Your cart is empty!' mod='dhl'}</b></div>
		{elseif $is_downloadable > 0}
			<div>{l s='This product is available for download and does not require shipping' mod='dhl'}.</div>
		{elseif isset($dhl_not_available) || $dhl_product_rates|@count == 0}
			<div>{l s='Shipping rates preview is not available in your area' mod='dhl'}.</div>
		{else}
			{foreach from=$dhl_product_rates key=dhl_product_carrier item=dhl_product_rate}
			<div class="dhl_carrier_name{$dhl_is_cart}">
				<span class="dhl_carrier_radio">
					<input id="id_carrier_new_{$dhl_product_rate.1}{$dhl_is_cart}" name="id_carrier_new" class="carrier_selection_radio" type="radio" {if $ps_carrier_default == $dhl_product_rate.1}checked="checked"{/if} onclick="dhl_change_carrier({$dhl_product_rate.1}, $(this))" />
					<label class="dhl_carrier_label" class="carrier_selection_name" for="id_carrier_new_{$dhl_product_rate.1}{$dhl_is_cart}">{$dhl_product_carrier}:</label>
				</span>
			</div>
			<div class="dhl_carrier_rate">&nbsp;&nbsp;{$dhl_product_rate.0}</div>
			<div class="dhl_carrier_buffer"></div>
			{/foreach}
		{/if}
	</div>
</div>

<script type="text/javascript">
	var dhl_cart_empty = "{l s='Your shopping cart is empty, add an item before selecting a shipping method' mod='dhl'}.";
	dhl_preview_update_state('{$dhl_is_cart}', {$dhl_dest_state|intval});         
</script>