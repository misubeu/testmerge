{block name='frontend_index_left_categories' append}
<div id="nfx_live_cart">
  <form action="#" method="post">
    <input id="nfx_live_shpg_cart" type="hidden" name="requestUrl" value="{url controller='LiveShoppingCart' action='getCartArticles'}">
  </form>
  <div class="headline"><span>LIVE</span> im Shop</div>
  <div id="carousel_checkout_parent">
    <div id="carousel_checkout_presentation">
      <ul id="carousel_checkout_presentation_list">
        <li class="ccpl_image"><div class="imgHolder imgLiquidFill" style="width:190px;"><a href="#"></a></div></li>
        <li class="ccpl_text"><div class="incartText"><a href="#"></a></div></li>
      </ul>
    </div>
    <div id="carousel_checkout" class="jcarousel" dir="rtl">
      <ul class="jcarouselAutoHorizontalLiveTickerCheckout">
        {if $aTwoArticles.baught}
          <li class="car">
            <a class="product" title="{$aTwoArticles.baught[0].name}" href="/detail/index/sArticle/{$aTwoArticles.baught[0].article_id}">
              <div class="imgHolder">
                <span class="helper"></span><img src="/media/image/thumbnail/{$aTwoArticles.baught[0].img}_140x140.{$aTwoArticles.baught[0].extension}" alt=""/>
              </div>
              <div class="incartText">
                <span class="specT">Gerade wurde <br/><span class="spec">{$aTwoArticles.baught[0].name}</span><br/>gekauft</span><br/>
                <span class="currency">{$aTwoArticles.baught[0].price|currency}&nbsp;<span class="sstar">*</span></span>
                <div class="zumArticle">zum Artikel</div>
              </div>
            </a>
          </li>
        {/if}
      </ul>
      <div class="hid_example">
          <a class="product" title="prod_name_placeholder" href="aricle_link_placeholder">
            <div class="imgHolder">
              <span class="helper"></span>image_placeholder
            </div>
            <div class="incartText">
              <span class="specT">Gerade wurde <br/><span class="spec">prod_name_placeholder</span><br/>gekauft</span><br/>
                <span class="currency">{777|currency}&nbsp;<span class="sstar">*</span></span>
                <div class="zumArticle">zum Artikel</div>
            </div>
          </a>
      </div>
    </div>
  </div>
  <div id="carousel_in_cart_parent">
    <div id="carousel_in_cart_presentation">
      <ul id="carousel_in_cart_presentation_list">
        <li class="ccpl_image"><div class="imgHolder imgLiquidFill" style="width:190px;"><a href="#"></a></div></li>
        <li class="ccpl_text"><div class="incartText"><a href="#"></a></div></li>
      </ul>
    </div>
    <div id="carousel_in_cart" class="jcarousel" dir="rtl">
      <ul class="jcarouselAutoHorizontalLiveTickerCart">
        {if $aTwoArticles.in_cart}
          <li class="car">
          <a class="product" title="{$aTwoArticles.in_cart[0].name}" href="/detail/index/sArticle/{$aTwoArticles.in_cart[0].article_id}">
            <div class="imgHolder">
              <span class="helper"></span><img src="/media/image/thumbnail/{$aTwoArticles.in_cart[0].img}_140x140.{$aTwoArticles.in_cart[0].extension}" alt=""/>
            </div>
            <div class="incartText">
              <span class="specT">Jemand hat <br/><span class="spec">{$aTwoArticles.in_cart[0].name}</span><br/>in den Warenkorb gelegt</span>
              <span class="currency">{$aTwoArticles.in_cart[0].price|currency}&nbsp;<span class="sstar">*</span></span>
              <div class="zumArticle">zum Artikel</div>
            </div>
          </a>
        </li>
        {/if}
      </ul>
      <div class="hid_example">
          <a class="product" title="prod_name_placeholder" href="aricle_link_placeholder">
            <div class="imgHolder">
              <span class="helper"></span>image_placeholder
            </div>
            <div class="incartText">
              <span class="specT">Jemand hat <br/><span class="spec">prod_name_placeholder</span><br/>in den Warenkorb gelegt</span><br/>
                <span class="currency">{777|currency}&nbsp;<span class="sstar">*</span></span>
                <div class="zumArticle">zum Artikel</div>
            </div>
          </a>
      </div>
    </div>
  </div>
</div>
{/block}

{block name="frontend_index_header_css_screen" append}
    <link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/css/listing.css'}" />
{/block}
 
{block name="frontend_index_header_javascript" append}
    <script src="{link file='frontend/_resources/javascript/jcarousel.js'}"></script>
    <script src="{link file='frontend/_resources/javascript/imgLiquid.js'}"></script>
    <script src="{link file='frontend/_resources/javascript/jTimer.js'}"></script>
    <script src="{link file='frontend/_resources/javascript/live_shopping_cart.js'}"></script>
{/block}
 
