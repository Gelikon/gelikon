<div class="gk-blk-delivery-address">
	<form action="{$link->getPageLink('authentication', true, NULL, "back=$back")|escape:'html':'UTF-8'}" method="post" id="new_account_form">
		<input type="hidden" name="auth_type" value="{$auth_type}">
		<input type="hidden" name="display_guest_checkout" value="1" />
		<input type="hidden" name="next_step" value="true">
		<input type="hidden" name="alias_invoice" id="alias_invoice" value="{l s='My Invoice address'}" />
		<input type="hidden" name="alias" id="alias" value="{l s='My address'}" />
					<input type="hidden" name="is_new_customer" id="is_new_customer" value="0" />
		<div class="gk-g-wrap">
			<div class="gk-col gk-col6">
				<h3 class="gk-elem-first">{l s='Адрес доставки'}</h3>
                                
                                <div class="control-row  {if isset($errors.country_address)}error{/if}">
					<div class="control-label">
						<label>{l s='Страна'}</label>
					</div>
					<div class="control-widget">
						<select name="id_country" id="id_country">
							<option value="0">-</option>
							{foreach from=$countries item=v}
								<option value="{$v.id_country}"{if (isset($smarty.post.id_country) AND  $smarty.post.id_country == $v.id_country) OR (!isset($smarty.post.id_country) && $sl_country == $v.id_country)} selected="selected"{/if}>{$v.name}</option>
							{/foreach}
						</select>
						{if isset($errors.country_address)}<div class="signature">{$errors.country_address}</div>{/if}
					</div>
					<div class="gk-clear-fix"></div>
				</div>
                                
                                <div class="control-row {if isset($errors.firstname)}error{/if}">
					<div class="control-label">
						<label>{l s='Имя'}</label>
					</div>
					<div class="control-widget">
						<input class="is_required validate form-control" data-validate="isName" type="text" id="customer_firstname" name="customer_firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{/if}">
						{if isset($errors.firstname)}<div class="signature">{$errors.firstname}</div>{/if}
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<div class="control-row {if isset($errors.lastname)}error{/if}">
					<div class="control-label">
						<label>{l s='Фамилия'}</label>
					</div>
					<div class="control-widget">
						<input class="is_required validate form-control" data-validate="isName" type="text" id="customer_lastname" name="customer_lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{/if}">
						{if isset($errors.lastname)}<div class="signature">{$errors.lastname}</div>{/if}
					</div>
					<div class="gk-clear-fix"></div>
				</div>
                                
                                
				<div class="control-row {if isset($errors.address1)}error{/if}">
					<div class="control-label">
						<label>{l s='Адрес'}</label>
					</div>
					<div class="control-widget">
						<input class="validate form-control" data-validate="isAddress" type="text" name="address1" id="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{/if}">

						<div class="signature">{l s='улица, номер дома, корпус, номер квартиры'}</div>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
                                <div class="control-row {if isset($errors.address1)}error{/if}">
					<div class="control-label">
						<label>{l s='Адрес 2'}</label>
					</div>
					<div class="control-widget">
						<input class="validate form-control" data-validate="isAddress" type="text" name="address2" id="address2" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{/if}">

						<div class="signature">{l s='улица, номер дома, корпус, номер квартиры'}</div>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
                                
                                <div class="control-row {if isset($errors.other_invoice)}error{/if}">
                                        <div class="control-label">
                                                <label>{l s='Дополнительная информация'}</label>
                                        </div>
                                        <div class="control-widget">
                                            <textarea class="form-control" name="other_invoice" id="other_invoice" cols="26" rows="3">{if isset($smarty.post.other_invoice)}{$smarty.post.other_invoice}{/if}</textarea>
                                        </div>
                                        <div class="gk-clear-fix"></div>
                                </div>
                                
                                <div class="control-row {if isset($errors.postcode_address)}error{/if}">
					<div class="control-label">
						<label>{l s='Индекс'}</label>
					</div>
					<div class="control-widget">
						<input data-validate="isPostCode" type="text" class="lil-control is_required validate form-control" name="postcode" id="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{/if}" />

						{if isset($errors.postcode_address)}<div class="signature">{$errors.postcode_address}</div>{/if}
					</div>
					<div class="gk-clear-fix"></div>
				</div>

			
				<div class="control-row {if isset($errors.city)}error{/if}">
					<div class="control-label">
						<label>{l s='Город'}</label>
					</div>
					<div class="control-widget">
						<input class="validate form-control" data-validate="isCityName" type="text" name="city" id="city" value="{if isset($smarty.post.city)}{$smarty.post.city}{/if}">
						{if isset($errors.city)}<div class="signature">{$errors.city}</div>{/if}
					</div>
					<div class="gk-clear-fix"></div>
				</div>

                                <div class="control-row {if isset($errors.company)}error{/if}">
					<div class="control-label">
						<label>{l s='Организация'}</label>
					</div>
					<div class="control-widget">
                                            <input type="text" class="form-control" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{/if}" />
					</div>
					<div class="gk-clear-fix"></div>
				</div>
                                 {*}
                                <div class="control-row {if isset($errors.other)}error{/if}">
					<div class="control-label">
						<label>{l s='Дополнительная информация'}</label>
					</div>
					<div class="control-widget">
                                            <textarea class="form-control" name="other" id="other" cols="26" rows="3">{if isset($smarty.post.other)}{$smarty.post.other}{/if}</textarea>
					</div>
					<div class="gk-clear-fix"></div>
				</div>
                                
                              
                                <p class="textarea form-group">
					<label for="other">{l s='Additional information'}</label>
					<textarea class="form-control" name="other" id="other" cols="26" rows="3">{if isset($smarty.post.other)}{$smarty.post.other}{/if}</textarea>
				</p>
                                {*}
                                
				
                                <div class="control-row {if isset($errors.phone) || isset($errors.phone_mobile)}error{/if}">
					<div class="control-label">
						<label>{l s='Мобильный телефон'}</label>
					</div>
					<div class="control-widget">
						<input class="is_required validate form-control" data-validate="isPhoneNumber" type="text" name="phone_mobile" id="phone_mobile" value="{if isset($smarty.post.phone_mobile)}{$smarty.post.phone_mobile}{/if}">
						{if isset($errors.phone) || isset($errors.phone_mobile)}
							<div class="signature">{if isset($errors.phone)}{$errors.phone}{elseif isset($errors.phone_mobile)}{$errors.phone_mobile}{/if}</div>
						{/if}
					</div>
					<div class="gk-clear-fix"></div>
				</div>
                                
                                <div class="control-row {if isset($errors.phone) || isset($errors.phone_mobile)}error{/if}">
					<div class="control-label">
						<label>{l s='Домашний телефон'}</label>
					</div>
					<div class="control-widget">
						<input class="is_required validate form-control" data-validate="isPhoneNumber" type="text" name="phone" id="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{/if}">
						{if isset($errors.phone) || isset($errors.phone_mobile)}
							<div class="signature">{if isset($errors.phone)}{$errors.phone}{elseif isset($errors.phone_mobile)}{$errors.phone_mobile}{/if}</div>
						{/if}
					</div>
					<div class="gk-clear-fix"></div>
				</div>
			        
                                <div class="control-row {if isset($errors.email)}error{/if}">
					<div class="control-label">
						<label>E-mail</label>
					</div>
					<div class="control-widget">
						{if $auth_type === '1'}
							<input class="is_required validate form-control" data-validate="isEmail" name="guest_email" value="{if isset($smarty.post.guest_email)}{$smarty.post.guest_email}{/if}">
						{else}
							<input class="is_required validate form-control" data-validate="isEmail" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email}{/if}">
						{/if}
						{if isset($errors.email)}<div class="signature">{$errors.email}</div>{/if}
					</div>
					<div class="gk-clear-fix"></div>
				</div>
				{if $auth_type === '2'}
					<div class="control-row {if isset($errors.passwd)}error{/if}">
						<div class="control-label">
							<label>{l s='Пароль'}</label>
						</div>
						<div class="control-widget">
							<input class="is_required validate form-control" data-validate="isPasswd" name="passwd" value="{if isset($smarty.post.passwd)}{$smarty.post.passwd}{/if}">
							{if isset($errors.passwd)}<div class="signature">{$errors.passwd}</div>{/if}
						</div>
						<div class="gk-clear-fix"></div>
					</div>
				{/if}

				<h3>{l s='Адрес выставления счета'}</h3>


				<div class="control-row">
					<div class="control-label">
						<label></label>
					</div>
					<div class="control-widget">
						<input type="checkbox" name="invoice_address" id="invoice_address"{if (isset($smarty.post.invoice_address) && $smarty.post.invoice_address) || (isset($guestInformations) && $guestInformations.invoice_address)} checked="checked"{/if} autocomplete="off" value="0" /><label>{l s='использовать для выставления счета другой адрес'}</label>
					</div>
					<div class="gk-clear-fix"></div>
				</div>

				<div class="hide-panel" {if !(isset($smarty.post.invoice_address) && $smarty.post.invoice_address) && !(isset($guestInformations) && $guestInformations.invoice_address)}style="display:none;"{/if}>
					<div class="control-row">
						<div class="control-label">
							<label>{l s='Страна'}</label>
						</div>
						<div class="control-widget">
							<select name="id_country_invoice" id="id_country_invoice">
								<option value="">-</option>
								{foreach from=$countries item=v}
								<option value="{$v.id_country}"{if (isset($guestInformations) AND $guestInformations.id_country_invoice == $v.id_country) OR (!isset($guestInformations) && $sl_country == $v.id_country)} selected="selected"{/if}>{$v.name|escape:'html':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>
						<div class="gk-clear-fix"></div>
					</div>

					<div class="control-row">
						<div class="control-label">
							<label>{l s='Город'}</label>
						</div>
						<div class="control-widget">
							<input class="validate form-control" data-validate="isCityName" name="city_invoice" id="city_invoice" value="{if isset($guestInformations) && $guestInformations.city_invoice}{$guestInformations.city_invoice}{/if}" />
						</div>
						<div class="gk-clear-fix"></div>
					</div>

					<div class="control-row">
						<div class="control-label">
							<label>{l s='Адрес'}</label>
						</div>
						<div class="control-widget">
							<input class="validate form-control" data-validate="isAddress" type="text" name="address1_invoice" id="address1_invoice" value="{if isset($guestInformations) && $guestInformations.address1_invoice}{$guestInformations.address1_invoice}{/if}" />
							<div class="signature">улица, номер дома, корпус, номер квартиры</div>
						</div>
						<div class="gk-clear-fix"></div>
					</div>
                                     
                                        <div class="control-row {if isset($errors.company_invoice)}error{/if}">
                                            <div class="control-label">
                                                    <label>{l s='Организация'}</label>
                                            </div>
                                            <div class="control-widget">
                                                <input type="text" class="form-control" id="company_invoice" name="company_invoice" value="{if isset($smarty.post.company_invoice)}{$smarty.post.company_invoice}{/if}" />
                                            </div>
                                            <div class="gk-clear-fix"></div>
                                        </div>

                                        
                                     

					<div class="control-row">
						<div class="control-label">
							<label>{l s='Индекс'}</label>
						</div>
						<div class="control-widget">
							<input type="text" class="validate form-control lil-control" data-validate="isPostCode" name="postcode_invoice" id="postcode_invoice" value="{if isset($guestInformations) && $guestInformations.postcode_invoice}{$guestInformations.postcode_invoice}{/if}">
						</div>
						<div class="gk-clear-fix"></div>
					</div>

					<div class="control-row">
						<div class="control-label">
							<label>{l s='Имя'}</label>
						</div>
						<div class="control-widget">
							<input class="validate form-control" data-validate="isName" type="text" id="firstname_invoice" name="firstname_invoice" value="{if isset($guestInformations) && $guestInformations.firstname_invoice}{$guestInformations.firstname_invoice}{/if}">
						</div>
						<div class="gk-clear-fix"></div>
					</div>

					<div class="control-row">
						<div class="control-label">
							<label>{l s='Фамилия'}</label>
						</div>
						<div class="control-widget">
							<input class="validate form-control" data-validate="isName" type="text" id="lastname_invoice" name="lastname_invoice" value="{if isset($guestInformations) && $guestInformations.lastname_invoice}{$guestInformations.lastname_invoice}{/if}">
						</div>
						<div class="gk-clear-fix"></div>
					</div>
				</div>

                <div class="gk-align-L">
                    <a class="btn" href="">Назад</a>
                </div>
			</div>
				
			<div class="gk-col gk-col6">



				{*}<div class="gk-separator" style="margin: 278px 0 0 0"></div>{*}
				<div class="gk-align-R" style="bottom: 0px; position: absolute; right: 0px;">
					<div class="total-price">{l s='Всего:'} <span>{displayPrice price=$global_order_total}</span></div>
					{if $auth_type === '1'}
						<button type="submit" class="btn" name="submitGuestAccount" id="submitGuestAccount">{l s='Далее'}</button>
					{else}
						<input type="hidden" name="email_create" value="1" />
						<input type="hidden" name="is_new_customer" value="1" />
						<button type="submit" class="btn" name="submitAccount" id="submitAccount">{l s='Далее'}</button>
					{/if}
				</div>
			</div>
		</div>
	</form>
</div>