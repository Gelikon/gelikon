
{if !$content_only}
					</div><!-- #center_column -->
					{if isset($right_column_size) && !empty($right_column_size)}
						<div id="right_column" class="col-xs-12 col-sm-{$right_column_size|intval} column">{$HOOK_RIGHT_COLUMN}</div>
					{/if}
					</div><!-- .row -->
				</div><!-- #columns -->
			</div><!-- .columns-container -->
            {if isset($about_shop_cart)}
                <!--блок о магазине-->
                <div class="gk-elem-last gk-blk-about-shop">
                    <div class="gk-container">
                        <div class="gk-g-wrap">
                            <div class="gk-col gk-col5">
                                <div class="title">
                                    {l s='магазин в Берлине'}
                                </div>
                                <p>
                                    {l s='adress_header'}
                                    {*}Kantstraße 84, 10627 Berlin, Germany{*}
                                </p>
                            </div>
                            <div class="gk-col gk-col4">
                                <div class="title">
                                    {l s='phone_header'}
                                    {*}+49 30 3234815{*}
                                </div>
                                <p>{l s='email'}{*}info@gelikon.de{*}</p>
                            </div>
                            <div class="gk-col gk-col3">
                                <a href="{$link->getCmsLink(8)}" class="btn">{l s='о магазине'}</a>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

			<!-- Footer -->
				<!--подвал-->
				<div class="gk-panel gk-panel-footer">
				<!--разделитель-->
				<div class="gk-separator"></div>
				<div class="gk-container">
					<div class="gk-g-wrap">
    <div class="gk-col gk-col3">

        <!--энциклопедия gelicon-->
                                <div class="gk-border-h gk-encyclopedia">
                                    <a href="{$link->getPageLink('new-products')}">{l s='Новинки'}</a>
                                </div>
    </div>

    <div class="gk-col gk-col3">

        <!--о магазине-->
        <div class="gk-about-shop gk-align-C">
            <a href="#">{l s='о магазине'}</a>
        </div>
    </div>

   {hook h='displayMyAccountBlockfooter'}
   
    <!--шара fb-->
    <div class="gk-col gk-col2">
        <a href="#" class="icon icon-facebook"></a>
    </div>
</div>
<div class="gk-mega-footer">
					{$HOOK_FOOTER}
				</footer>
			</div>
			</div><!-- #footer -->
			</div>
			</div>
		</div><!-- #page -->
{/if}
{include file="$tpl_dir./global.tpl"}
	</body>
</html>