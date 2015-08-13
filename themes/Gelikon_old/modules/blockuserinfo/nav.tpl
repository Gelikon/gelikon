<!-- Block user information module NAV  -->
 <div class="gk-col">

 	        <div class="gk-user-authentication">
                    <div class="gk-border-h ">
                {if $is_logged}
                    <a class="btn-open" style="max-width: 200px;" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
                         {$customerName}
                    </a>
                    <a class="logout" href="{$link->getPageLink('index', true, NULL, "mylogout")|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Log me out' mod='blockuserinfo'}"><span class="icon icon-open"></span></a>
                    {*if isset($best_presetage) && $best_presetage}
                        <p>{l s='Ваша скидка' mod='blockuserinfo'} {$best_presetage}%</p>
                    {/if*}
                
                
                    </div>
                {else}
                    <a class="btn-open" href="#{*$link->getPageLink('my-account', true)|escape:'html':'UTF-8'*}" onclick="return false">
                        {l s='личный кабинет' mod='blockuserinfo'}</a><span class="icon icon-lock btn-open"></span>
                    </div>
                {/if}    
          
            <!--скрытыя панел-->
            <div class="hide-panel">
                <form method="post" action="{$link->getPageLink('authentication', true)|escape:'html':'UTF-8'}">
                    <input type="hidden" value="3" class="login_hidden" name="auth_type">
                    <input type="hidden" value="1" class="login_hidden" name="SubmitLogin">
                    
                    <input type="hidden" name="SubmitCreate" class="create_hidden" value="1">
                    <input type="hidden" name="next_step" class="create_hidden" value="true">
                    <input type="hidden" name="auth_type" class="create_hidden" value="2">
                    
                    <a href="#" class="icon icon-delete"></a>

                    <div class="control-row">
                        <div class="control-label">
                            <label style="margin-left: 10px">{l s='логин' mod='blockuserinfo'}</label>
                        </div>
                        <div class="control-widget">
                            <input type="text" name="email_create"/>
                        </div>
                        <div class="gk-clear-fix"></div>
                    </div>
                    <div class="control-row">
                        <div class="control-label">
                            <label>{l s='пороль' mod='blockuserinfo'}</label>
                        </div>
                        <div class="control-widget">
                            <input type="password"  name="passwd"/>
                        </div>
                        <div class="gk-clear-fix"></div>
                    </div>

                    <button type="submit" class="btn btn-yellow" id="SubmitLogin" name="SubmitLogin">{l s='войти' mod='blockuserinfo'}</button>
                    <button type="submit" class="btn btn-black" id="SubmitCreate" name="SubmitCreate">{l s='регистрация' mod='blockuserinfo'}</button>
                    {*}<a href="#" class="btn btn-black">{l s='регистрация' mod='blockuserinfo'}</a>{*}
                </form>
            </div>

            </div>
</div>

<!-- /Block usmodule NAV -->