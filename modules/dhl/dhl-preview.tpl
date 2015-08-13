<!-- DHL SHIPPING RATE PREVIEW -->
<script type="text/javascript" src="{$this_dhl_path}js/dhl-preview.js"></script>
<link rel="stylesheet" type="text/css" href="{$this_dhl_path}css/dhl.css" />
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
	var dhl_invalid_zip = "{l s='Invalid Zipcode, if your country does not use zipcodes, enter 11111' mod='dhl' js=1}";
	var dhl_please_wait = "{l s='Please Wait' mod='dhl' js=1}";
	var dhl_hide = "{l s='Hide' mod='dhl' js=1}";
	var dhl_show = "{l s='Shipping Rates' mod='dhl' js=1}";
	
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

<div class="dhl_preview_container{$dhl_is_cart}" {if $psVersion == 1.6}style="margin:10px 0;float: none; {if $dhl_is_cart != ''}padding: 0 0 10px 0;{/if}"{/if}>
	<p class="buttons_bottom_block" style="padding: 5px;{if $psVersion == 1.6 && $dhl_is_cart != ''}margin: 0; text-align: center;{/if}">
		<a href="javascript:void(0)" id="dhl_shipping_rates_button{$dhl_is_cart}" onclick="
			{if $dhl_get_rates != 1}
				$('#dhl_shipping_rates{$dhl_is_cart}').fadeIn(800);
				dhl_city_display(0, '{$dhl_is_cart}');
				dhl_define_hide_button($(this), '{$dhl_is_cart}'); 
			{else}
				dhl_get_rates(false,'{$dhl_is_cart}');
				dhl_city_display(0, '{$dhl_is_cart}');
			{/if}" class="{if $psVersion == 1.6}btn btn-default{else}exclusive{/if}" style="{if $dhl_is_cart != ''}margin: auto;{/if} {if $psVersion == 1.6}color: #333333; width: 100%;{/if}">{l s='Shipping Rates' mod='dhl'}</a>
	</p>
	<div id="dhl_shipping_rates{$dhl_is_cart}" style="{if $dhl_get_rates != 1}display:none;{/if}">
	{if $dhl_get_rates != 1}
		<div id="dhl_address{$dhl_is_cart}">  
			<a href="javascript:void(0)" id="dhl_shipping_rates_button" class="dhl_hide_button">X</a>
			
			<span style="font-weight: bold;">
				{l s='Please' mod='dhl'}
				<a href="{$base_dir}authentication.php" style="text-decoration: underline">{l s='Login' mod='dhl'}</a>{l s=', or enter your' mod='dhl'}
			</span>
		</div>

		<div id="dhl_dest_change{$dhl_is_cart}" style="{if $dhl_get_rates != 1}display:block;{/if}">
		
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
	{/if}
	</div>
</div>
<script type="text/javascript">
dhl_preview_update_state('{$dhl_is_cart}', {if isset($dhl_dest_state)}{$dhl_dest_state|intval}{else}0{/if});
</script>
<!-- /DHL SHIPPNG RATE PREVIEW -->
