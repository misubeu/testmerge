<?php
/**
 * nfx LiveShoppingCart
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2014, nfx:MEDIA
 * @author nf, kz - info@nfxmedia.de;
 * @package nfxMEDIA
 * @subpackage NFXLiveShoppingCart
 * @version 1.0.0 initial release of the plugin 
 *
 * Live shopping cart plugin which shows in the left column products that are in the shopping carts of other users
 */
 
class Shopware_Controllers_Frontend_LiveShoppingCart extends Enlight_Controller_Action
{
    /**
     * Initial action of this controller.
     *
     * If no action defined in the request, the indexAction called automatically.
     * This action is used to initial the live shopping cart slider. It loads the plugin configuration,
     * the thumbnail directory and loads the frontend/LiveShoppingCart/live_shopping_cart.tpl template.
    */
    public function indexAction() {

      $thumbnailPath = $this->Request()->getBasePath(). '/media/image/thumbnail/';

      /**@var $config Enlight_Config*/
      $config = Shopware()->Plugins()->Frontend()->LiveShoppingCart()->Config();
      $this->View();
    }
 
    /**
     * Controller action for the ajax request.
     * This function refers to the private function getCartArticles() to get up to 30 products
     * which are in cart and up to 30 products that were bought lately by clients. After that
     * this function echoes data in json format in the background for the system to catch and 
     * show in the carousel. This function loads no template.
    */
    public function getCartArticlesAction() {
      Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
      echo json_encode(array('data' => $this->getCartArticles()));
    }
 
    /**
     * Internal helper function which loads the article images and necessary data.
     *
     * Uses the request offset, limit for the query.
     * Returns the associative array with bought and in_cart articles as array result.
     * @return array
    */
    private function getCartArticles()
    {
      $nfxArticles = array();
      $sql_baught = "
        SELECT CONCAT_WS('_', o.id, a.id) AS order_article_ids, a.id AS article_id, o.ordertime, a.name, aim.img, aim.extension, od.price
        FROM s_order AS o
        JOIN s_order_details AS od ON od.orderID = o.id
          AND od.articleID <> 0
        JOIN s_articles AS a ON od.articleID = a.id
        JOIN s_articles_img AS aim ON aim.articleID = a.id
          AND aim.main = '1'
        WHERE o.ordernumber <> '0'
        ORDER BY o.ordertime DESC
        LIMIT 0, 30
      ";
      $sql_cart = "
        SELECT CONCAT_WS('_', o.id, a.id) AS order_article_ids, a.id AS article_id, o.ordertime, a.name, aim.img, aim.extension, od.price
        FROM s_order AS o
        JOIN s_order_details AS od ON od.orderID = o.id
          AND od.articleID <> 0
        JOIN s_articles AS a ON od.articleID = a.id
        JOIN s_articles_img AS aim ON aim.articleID = a.id
          AND aim.main = '1'
        WHERE o.ordernumber = '0'
        ORDER BY o.ordertime DESC
        LIMIT 0, 30
      ";
      $nfxArticles['baught'] = Shopware()->Db()->fetchAll($sql_baught, array());
      if (count($nfxArticles['baught']) == 0) {
        $nfxArticles['baught'] = $this->getAlternativeArticles(true);
      }
      $nfxArticles['in_cart'] = Shopware()->Db()->fetchAll($sql_cart, array());
      if (count($nfxArticles['in_cart']) == 0) {
        $nfxArticles['in_cart'] = $this->getAlternativeArticles(false);
      }
      return $nfxArticles;
    }
    
    /*
     * Internal helper function which loads alternative articles from database
     * in case when there were no activities from users like buying or placing in cart.
     * if the parameter $isBought equals true it returns as bought products otherwise
     * it returns as in cart products.
     * 
    */
    private function getAlternativeArticles($isBought = true) {
      $aResultArticle = array();
      $limit = " LIMIT 0, 10";
      $aArticlesIdsSql = "
        SELECT a.id 
        FROM s_articles AS a
        JOIN s_articles_img AS aim ON aim.articleID = a.id
          AND aim.main = '1'
        WHERE active = '1' ORDER BY id DESC";
      if (!$isBought) {
        $limit = " LIMIT 10, 20";
      }
      $aArticlesIdsSql .= $limit;
      $aAltTempArticles = Shopware()->Db()->fetchAll($aArticlesIdsSql, array());
      foreach ($aAltTempArticles as $art) {
        $TempArticleData = Shopware()->Modules()->Articles()->sGetArticleById($art['id']);
        $aAltArticles[$art['id']]['article_id'] = $TempArticleData['articleID'];
        $aAltArticles[$art['id']]['name'] = $TempArticleData['articleName'];
        
        $tokens1 = explode('/', $TempArticleData['image']['src']['original']);
        $fullImageName = $tokens1[count($tokens1)-1];
        $tokens2 = explode('.', $fullImageName);
        
        $aAltArticles[$art['id']]['extension'] = $tokens2[count($tokens2)-1];
        $aAltArticles[$art['id']]['img'] = $tokens2[count($tokens2)-2];
        $aAltArticles[$art['id']]['price'] = $TempArticleData['price'];
        $aResultArticle[] = $aAltArticles[$art['id']];
      }
      return $aResultArticle;
    }
}
?>