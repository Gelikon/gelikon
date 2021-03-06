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
<div class="wrap">
<div class="control-row radio">
        <div class="control-widget">
                <input type="radio" name="payment_option" id="delivery_option_cashondelivery" value="{$link->getModuleLink('cashondelivery', 'validation', [], true)|escape:'html'}">
                <label for="delivery_option_cashondelivery">
                        {l s='Pay with cash on delivery (COD)' mod='cashondelivery'}
                </label>
        </div>
        <p class="description">
               <span>{l s='You pay for the merchandise upon delivery' mod='cashondelivery'}</span>
        </p>
</div>
</div>    



{*}
<div class="row">
	<div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="cash" href="{$link->getModuleLink('cashondelivery', 'validation', [], true)|escape:'html'}" title="{l s='Pay with cash on delivery (COD)' mod='cashondelivery'}" rel="nofollow">
            	{l s='Pay with cash on delivery (COD)' mod='cashondelivery'}<br />
            	{l s='You pay for the merchandise upon delivery' mod='cashondelivery'}
            </a>
        </p>
    </div>
</div>
{*}