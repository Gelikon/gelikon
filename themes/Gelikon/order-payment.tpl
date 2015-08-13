
{if !$opc}
	{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
	{addJsDef currencyRate=$currencyRate|floatval}
	{addJsDef currencyFormat=$currencyFormat|intval}
	{addJsDef currencyBlank=$currencyBlank|intval}
	{addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
	{addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
	{capture name=path}{l s='Your payment method'}{/capture}

	<h1 class="gk-align-C">{l s='Оформление заказа'}</h1>
{else}
	<h1 class="page-heading step-num"><span>3</span> {l s='Please choose your payment method'}</h1>
{/if}

{if !$opc}
	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}
	{*include file="$tpl_dir./errors.tpl"*}
{else}
	<div id="opc_payment_methods" class="opc-main-block">
		<div id="opc_payment_methods-overlay" class="opc-overlay" style="display: none;"></div>
{/if}

		<h2 class="blk gk-align-C">{l s='Оплата'}</h2>
		<div class="paiement_block">
			<div id="HOOK_TOP_PAYMENT">{$HOOK_TOP_PAYMENT}</div>
			{if $HOOK_PAYMENT}
				{if !$opc}
					<div class="gk-blk-pay">
						<div class="gk-g-wrap">
							<div class="gk-col gk-col6">
								<h3 class="gk-elem-first">{l s='Выбрать способ оплаты'}</h3>
								<div id="HOOK_PAYMENT" class="gk-blk-open-radio">
									{$HOOK_PAYMENT}
								</div>
								<div class="gk-clear-fix"></div>

								<div class="blk gk-separator "></div>
		                        <div class="gk-align-R">
                                    <div class="delivery-price">
                                        {if $use_taxes}
                                            {if $priceDisplay}
                                                {l s='Доставка:'}<span>{displayPrice price=$total_shipping_tax_exc}</span>
                                            {else}
                                                {l s='Доставка:'} <span>{displayPrice price=$total_shipping}</span>
                                            {/if}
                                        {else}
                                            {l s='Доставка:'} <span>{displayPrice price=$total_shipping}</span>
                                        {/if}
                                    </div>
		                            <div class="total-price">
		                            	{if $use_taxes}
											{if $priceDisplay}
												{if $display_tax_label}{l s='Total products (tax excl.)'}{else}{l s='Total products'}{/if} <span>{displayPrice price=$total_price}</span>
											{else}
												{if $display_tax_label}{l s='Total products (tax incl.)'}{else}{l s='Total products'}{/if} <span>{displayPrice price=$total_price}</span>
											{/if}
										{else}
											{l s='Total products'} <span>{displayPrice price=$total_price_without_tax}</span>
										{/if}
		                            </div>

		                        </div>

                                <div class="gk-align-L">
                                    <a class="btn" href="{$link->getPageLink('order', true, null, 'step=2')}">{l s='назад'}</a>
                                </div>
                            </div>
							<div class="gk-col gk-col6" style="position: absolute;bottom: 0;right: 0">

		                        <h3 class="gk-elem-first">{l s='Ваш заказ:'}</h3>

		                        <ol class="gk-list-padding">
		                        	{foreach from=$products item=product}
		                        		<li>{$product.name|escape:'html':'UTF-8'}</li>
		                        	{/foreach}
		                        </ol>

		                        <!--<h3>{l s='Доставка:'}</h3>-->

		                        <p>{$delivery->firstname} {$delivery->lastname} <br/> {$delivery->address1} {$delivery->address2}<br/> {$delivery->city}, {$delivery->country} <br/>{$delivery->postcode}</p>

		                        {*}<h3>{l s='Срок доставки:'} {$carrier->delay}</h3>{*}
		                        <div class="gk-align-R" style="padding-bottom: 36px;">
                                    <img src="/themes/Gelikon/img/arrow-right-310628_640.png" style="width: 287px;margin: 0px 10px -23px;">
                                    <button type="submit" class="btn" name="processCarrier" id="processOrder">{l s='Купить'}</button>
                                </div>
		                    </div>
						</div>
					</div>
                                        
				{/if}
			{else}
				<p class="alert alert-warning">{l s='No payment modules have been installed.'}</p>
			{/if}
		</div>
{if !$opc}
{else}
	</div> <!-- end opc_payment_methods -->
{/if}