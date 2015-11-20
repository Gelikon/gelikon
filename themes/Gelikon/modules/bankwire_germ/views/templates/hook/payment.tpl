{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="wrap sel">
<div class="control-row radio">
        <div class="control-widget">
                <input type="radio" name="payment_option" id="delivery_option_bankwire_germ" value="{$link->getModuleLink('bankwire_germ', 'payment')|escape:'html':'UTF-8'}" checked="checked">
                <label for="delivery_option_bankwire_germ">
                        {l s='Pay by bank wire' mod='bankwire_germ'}
                </label>
        </div>
        <p class="description">
                <span>{l s='(order processing will be longer)' mod='bankwire_germ'}</span>
        </p>
</div>
</div>

{*}
<div class="row">
	<div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a 
            class="bankwire_germ" 
            href="{$link->getModuleLink('bankwire_germ', 'payment')|escape:'html':'UTF-8'}" 
            title="{l s='Pay by bank wire' mod='bankwire_germ'}">
            	{l s='Pay by bank wire' mod='bankwire_germ'} <span>{l s='(order processing will be longer)' mod='bankwire_germ'}</span>
            </a>
        </p>
    </div>
</div>
{*}