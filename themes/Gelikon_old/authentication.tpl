{if isset($smarty.post.auth_type)}
	{assign var='auth_type' value=$smarty.post.auth_type}
{else}
	{assign var='auth_type' value='1'}
{/if}

{if isset($smarty.post.next_step)}
	{assign var='next_step' value=$smarty.post.next_step}
{else}
	{assign var='next_step' value=false}
{/if}

{capture name=path}
	{if !isset($email_create)}{l s='Authentication'}{else}
		<a href="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Authentication'}">{l s='Authentication'}</a>
		<span class="navigation-pipe">{$navigationPipe}</span>{l s='Create your account'}
	{/if}
{/capture}
<!--<h1 class="page-heading">{if !isset($email_create)}{l s='Authentication'}{else}{l s='Create an account'}{/if}</h1>-->
<h1 class="gk-align-C">Оформление заказа</h1>
{if $next_step}
	{assign var='current_step' value='address'}{include file="$tpl_dir./order-steps.tpl"}
{else}
	{assign var='current_step' value='login'}{include file="$tpl_dir./order-steps.tpl"}
{/if}
{include file="$tpl_dir./errors.tpl"}
{assign var='stateExist' value=false}
{assign var="postCodeExist" value=false}
{assign var="dniExist" value=false}
{if $next_step}
	<h2 class="blk gk-align-C">{l s='Доставка'}</h2>
{else}
	<h2 class="blk gk-align-C">{l s='Выберите способ авторизации'}</h2>
{/if}


{if !$next_step}
<div class="gk-blk-open-radio gk-blk-authentication">
	<div class="gk-g-wrap">
		<div class="gk-col gk-col6">
			<div class="wrap {if $auth_type === '1'}sel{/if}">
				<form method="post" action="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}">
					<div class="control-row radio">
						<div class="control-widget">
							<input type="radio" name="auth_type" id="radio1" value="1" {if $auth_type === '1'}checked{/if}>
							<input type="hidden" name="display_guest_checkout" value="1">
							<input type="hidden" name="next_step" value="true">
							<label for="radio1">{l s='Быстрая покупка без регистрации'}</label>
						</div>

					</div>
					<div class="hide-panel">
						<div class="gk-align-R">
							<button type="submit" class="btn btn-yellow">{l s='продолжить'}</button>
						</div>

						<div class="gk-signature">{l s='Регистрация поможет отследить заказы и сократит время оформления последующих покупок.'}</div>
					</div>
				</form>
			</div>

			<div class="gk-separator"></div>

			<div class="wrap {if $auth_type === '2'}sel{/if}">
				<form method="post" action="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}">
					<input type="hidden" name="SubmitCreate" value="1">
					<input type="hidden" name="next_step" value="true">
					<input type="hidden" name="auth_type" value="2">
					<div class="control-row radio ">
						<div class="control-widget">
							<input type="radio" name="auth_type" id="radio2" value="2" {if $auth_type === '2'}checked{/if}>
							<label for="radio2">{l s='Зарегистрироваться'}</label>
						</div>

					</div>
					<div class="hide-panel">
						<div class="control-row">
							<div class="control-label">
								<label>e-mail</label>
							</div>
							<div class="control-widget">
						<div class="form-group">
						<input type="text" class="is_required validate account_input form-control" data-validate="isEmail" id="email_create" name="email_create" value="{if isset($smarty.post.email_create)}{$smarty.post.email_create|stripslashes}{/if}" />
						</div>
							</div>
							<div class="gk-clear-fix"></div>
						</div>

						<div class="control-row">
							<div class="control-label">
								<label>{l s='пароль'}</label>
							</div>
							<div class="control-widget">
								<input type="password" name="passwd" />
							</div>
							<div class="gk-clear-fix"></div>
						</div>
						<div class="gk-align-R">
							<button type="submit" class="btn btn-yellow">{l s='зарегистрироваться'}</button>
						</div>

						{*}<div class="gk-signature">
							<p>Войти можно еще быстрее с помощью <br/> аккаунтв Facebook</p>
							<a href="#" class="facebook"></a>
						</div>{*}

					</div>
				</form>
			</div>

			<div class="gk-separator"></div>

			<div class="wrap {if $auth_type === '3'}sel{/if}" >
				<form action="{$link->getPageLink('authentication', true, NULL, "back=$back")|escape:'html':'UTF-8'}" method="post">
					<input type="hidden" name="auth_type" value="3">
					<input type="hidden" name="SubmitLogin" value="1">
					<div class="control-row radio">
						<div class="control-widget">
							<input type="radio" name="auth_type" id="radio3" value="3"{if $auth_type === '3'}checked{/if}>
							<label for="radio3">{l s='Войти со своим логином и паролем'}</label>
						</div>

					</div>
					<div class="hide-panel">

						<div class="control-row {if isset($errors.email)}error{/if} ">
							<div class="control-label">
								<label>{l s='e-mail'}</label>
							</div>
							<div class="control-widget">

						<div class="form-group">
						<input type="text" class="is_required validate account_input form-control" data-validate="isEmail" id="email_create" name="email_create" value="{if isset($smarty.post.email_create)}{$smarty.post.email_create|stripslashes}{/if}" />
						</div>
							</div>
							<div class="gk-clear-fix"></div>
						</div>

						<div class="control-row {if isset($errors.passwd)}error{/if}">
							<div class="control-label">
								<label>{l s='пароль'}</label>
							</div>
							<div class="control-widget">
								<input type="password" id="passwd" name="passwd" value="{if isset($smarty.post.passwd)}{$smarty.post.passwd|stripslashes}{/if}"/>
								{if isset($errors.passwd)}<div class="signature">{$errors.passwd}</div>{/if}
							</div>

							<div class="gk-clear-fix"></div>
						</div>

						<div class="gk-align-R">
							<a href="{$link->getPageLink('password')|escape:'html':'UTF-8'}">{l s='Забыли пороль?'}</a>
							<button type="submit" class="btn btn-yellow">{l s='продолжить'}</button>
						</div>

						{*}<div class="gk-signature">
							<p>Войти можно еще быстрее с помощью <br/> аккаунтв Facebook</p>
							<a href="#" class="facebook"></a>
						</div>{*}

					</div>
				</form>
			</div>

		</div>
		<div class="gk-col gk-col6">

		</div>
	</div>
</div>
{else}<!-- если нужно ввести адресс -->
	{if $auth_type === '1' || $auth_type === '2'}
		{include file="$tpl_dir./auth-not-register.tpl"}
	{/if}
	
{/if}


{strip}
{if isset($smarty.post.id_state) && $smarty.post.id_state}
	{addJsDef idSelectedState=$smarty.post.id_state|intval}
{else if isset($address->id_state) && $address->id_state}
	{addJsDef idSelectedState=$address->id_state|intval}
{else}
	{addJsDef idSelectedState=false}
{/if}
{if isset($smarty.post.id_country) && $smarty.post.id_country}
	{addJsDef idSelectedCountry=$smarty.post.id_country|intval}
{else if isset($address->id_country) && $address->id_country}
	{addJsDef idSelectedCountry=$address->id_country|intval}
{else}
	{addJsDef idSelectedCountry=false}
{/if}
{if isset($countries)}
	{addJsDef countries=$countries}
{/if}
{if isset($vatnumber_ajax_call) && $vatnumber_ajax_call}
	{addJsDef vatnumber_ajax_call=$vatnumber_ajax_call}
{/if}
{if isset($email_create) && $email_create}
	{addJsDef email_create=$email_create|boolval}
{else}
	{addJsDef email_create=false}
{/if}
{/strip}