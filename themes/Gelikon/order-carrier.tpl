
{if !$opc}
	{capture name=path}{l s='Shipping:'}{/capture}
	{assign var='current_step' value='shipping'}
	{*include file="$tpl_dir./errors.tpl"*}
	<div id="carrier_area">
		<h1 class="gk-align-C">{l s='Оформление заказа'}</h1>
		{include file="$tpl_dir./order-steps.tpl"}
                <h2 class="blk gk-align-C">{l s='Выберите способ доставки'}</h2>
		<form id="form" action="{$link->getPageLink('order', true, NULL, "multi-shipping={$multi_shipping}")|escape:'html':'UTF-8'}" method="post" name="carrier_area">
{else}
	<div id="carrier_area" class="opc-main-block">
		<h1 class="page-heading step-num"><span>2</span> {l s='Delivery methods'}</h1>
		<div id="opc_delivery_methods" class="opc-main-block">
			<div id="opc_delivery_methods-overlay" class="opc-overlay" style="display: none;"></div>
{/if}

<div class="gk-blk-delivery-method">
	{if isset($virtual_cart) && $virtual_cart}
		<input id="input_virtual_carrier" class="hidden" type="hidden" name="id_carrier" value="0" />
	{else}
		<div id="HOOK_BEFORECARRIER">
			{if isset($carriers) && isset($HOOK_BEFORECARRIER)}
				{$HOOK_BEFORECARRIER}
			{/if}
		</div>
		{if isset($isVirtualCart) && $isVirtualCart}
			<p class="alert alert-warning">{l s='No carrier is needed for this order.'}</p>
		{else}
			{if $recyclablePackAllowed}
				<div class="checkbox">
					<label for="recyclable">
						<input type="checkbox" name="recyclable" id="recyclable" value="1" {if $recyclable == 1}checked="checked"{/if} />
						{l s='I would like to receive my order in recycled packaging.'}.
					</label>
				</div>
			{/if}

			<div class="gk-g-wrap">
				<div class="gk-col gk-col6">
					<h3 class="gk-elem-first">{l s='Адрес доставки'}</h3>
					
					<div class="control-row">
						<div class="control-label">
							<label>{l s='Адрес достаки'}</label>
						</div>
						<div class="control-widget">
							{$address->firstname} {$address->lastname} <br/> {$address->address1} {$address->address2} <br/> {$address->city}, {$address->country} <br/>{$address->postcode}
						</div>
						<div class="gk-clear-fix"></div>
					</div>
					<!--формы достаки-->
					<div class="gk-blk-delivery gk-blk-open-radio">
						{if isset($delivery_option_list)}
							{foreach $delivery_option_list as $id_address => $option_list}
								{foreach $option_list as $key => $option}
									{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} 
										

										{if $option.total_price_with_tax && !$option.is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))}
											{if $use_taxes == 1}
												{if $priceDisplay == 1}
													{assign var='current_delivery_price' value="{convertPrice price=$option.total_price_without_tax} {l s='(tax excl.)'}"}
												{else}
													{assign var='current_delivery_price' value="{convertPrice price=$option.total_price_with_tax} {l s='(tax incl.)'}"}		
												{/if}
											{else}
												{assign var='current_delivery_price' value={convertPrice price=$option.total_price_without_tax}}
											{/if}
										{else}
													
											{assign var='current_delivery_price' value={l s='Free'}}
										{/if}
									{/if}
									<div class="wrap {if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}sel{/if}">
										<div class="control-row radio">
											<div class="control-widget">
												<input type="radio" name="delivery_option[{$id_address}]" id="delivery_option_{$id_address}_{$option@index}" value="{$key}"{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} checked="checked"{/if}>
												<label for="delivery_option_{$id_address}_{$option@index}">
													{if $option.unique_carrier}
														{foreach $option.carrier_list as $carrier}
															{$carrier.instance->name}
														{/foreach}
													{/if}
												</label>
											</div>

											<div class="price">
												{if $option.total_price_with_tax && !$option.is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))}
													{if $use_taxes == 1}
														{if $priceDisplay == 1}
															<span>{convertPrice price=$option.total_price_without_tax}</span> {l s='(tax excl.)'}
														{else}
															<span>{convertPrice price=$option.total_price_with_tax}</span> {l s='(tax incl.)'}
														{/if}
													{else}
														<span>{convertPrice price=$option.total_price_without_tax}</span>
													{/if}
												{else}
													{l s='Free'}
												{/if}
											</div>
											<p class="description">
												{if $option.unique_carrier}
													{if isset($carrier.instance->delay[$cookie->id_lang])}
														{$carrier.instance->delay[$cookie->id_lang]}
													{/if}
												{/if}
											</p>
										</div>
									</div>
								{/foreach}
							{/foreach}
						{else}
							{assign var='current_delivery_price' value='Нет доставки по выбранному адресу'}
						{/if}
					</div>

                    <div class="gk-align-L">
                        <a class="btn" href="{$link->getPageLink('order', true, null, 'step=1')}">{l s='назад'}</a>
                    </div>
                </div>
				<div class="gk-col gk-col6" style="position: absolute;bottom: 0;right: 0">
                                        {*}
					<h3 class="gk-elem-first">{l s='Ваш заказ:'}</h3>

					<ol class="gk-list-padding">
						{foreach from=$product_list item=item}
							<li>{$item['name']}</li>
						{/foreach}
					</ol>
					<div class="blk gk-separator "></div>
                                        {*}
					<div class="gk-align-R">

						<!--сюда из базы вписывается значение из базы-->
						<input type="hidden" value="{displayPrice price=$global_order_total}" id="value">

						<div class="delivery-price">{l s='Доставка:'} <span>
						{if isset($current_delivery_price)}
							{$current_delivery_price}
						{else}
							{l s='Нет доставки по выбранному адресу'}
						{/if}</span></div>
						<div class="total-price">{l s='Всего:'} <span>{displayPrice price=$global_order_total}</span></div>
						{if !$is_guest}
							{if $back}
							{else}

							{/if}
						{else}

						{/if}
						{if $conditions }
							{if $opc}
								<hr style="" />
							{/if}
							<!--<p class="carrier_title">{l s='Terms of service'}</p>-->
							
							<p class="checkbox termsofservices">
								<input type="checkbox" name="cgv" id="cgv" value="1" {if $checkedTOS}checked="checked"{/if} />
								<label for="cgv">{l s='I agree to the terms of service and will adhere to them unconditionally.'}</label>
								<!--<a href="{$link_conditions|escape:'html':'UTF-8'}" class="iframe" rel="nofollow">{l s='(Read the Terms of Service)'}</a>-->
								{$link_conditions = "?id_cms=14&controller=cms&content_only=1"} {* Gelikon hack here *}
								<br/><a href="{$link_conditions|escape:'html':'UTF-8'}" class="iframe" rel="nofollow">{l s='(Read the Terms of Service)'}</a>
							</p>
						{/if}
						{if isset($virtual_cart) && $virtual_cart || (isset($delivery_option_list) && !empty($delivery_option_list))}
							<button type="submit" class="btn" name="processCarrier">{l s='Далее'}</button>
						{/if}
					</div>
				</div>
			</div>
		{/if}
	{/if}
</div>
{if !$opc}
	<input type="hidden" name="step" value="3" />
	<input type="hidden" name="back" value="{$back}" />
</form>
{else}
	</div> <!-- end opc_delivery_methods -->
{/if}
{strip}
{if !$opc}
	{addJsDef orderProcess='order'}
	{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
	{addJsDef currencyRate=$currencyRate|floatval}
	{addJsDef currencyFormat=$currencyFormat|intval}
	{addJsDef currencyBlank=$currencyBlank|intval}
	{if isset($virtual_cart) && !$virtual_cart && $giftAllowed && $cart->gift == 1}
		{addJsDef cart_gift=true}
	{else}
		{addJsDef cart_gift=false}
	{/if}
	{addJsDef orderUrl=$link->getPageLink("order", true)|addslashes}
	{addJsDefL name=txtProduct}{l s='Product' js=1}{/addJsDefL}
	{addJsDefL name=txtProducts}{l s='Products' js=1}{/addJsDefL}
	{addJsDefL name=msg_order_carrier}{l s='You must agree to the terms of service before continuing.' js=1}{/addJsDefL}
{/if}
{/strip}