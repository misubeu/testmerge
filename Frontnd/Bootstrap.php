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


/**
 * todo@all: Documentation
 */
 
class Shopware_Plugins_Frontend_NfxLiveShoppingCart_Bootstrap extends Shopware_Components_Plugin_Bootstrap {
  /**
     *
     * @var \Shopware\Models\Media\Repository
     */
    protected $mediaRepository = null;
 
    /**
     * Returns the plugin label which displayed in the plugin information and
     * in the plugin manager.
     * @return string
     */
    public function getLabel()
    {
      return 'NFX Live Shopping Cart';
    }
 
    /**
     * Returns the plugin information
     * @return array
     */
    public function getInfo()
    {
      return array(
        'label' => $this->getLabel(),
        'version' => $this->getVersion(),
        'link' => 'http://www.shopware.de/'
      );
    }
 
    /**
     * Returns the plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
      return '1.0.0';
    }
 
    /**
     * Creates the configuration fields and subscribes the post dispatch event of the frontend listing container.
     *
     * @return bool
     */
    public function install()
    {
 
      $this->subscribeEvent(
        'Enlight_Controller_Action_PostDispatch_Frontend_Listing',
        'onFrontendPostDispatch'
      );

      $this->subscribeEvent(
        'Enlight_Controller_Dispatcher_ControllerPath_Frontend_LiveShoppingCart',
        'onGetFrontendController'
      );
      return array('success' => true, 'invalidateCache' => array('frontend'));
    }

     public function uninstall()
    {
      return array('success' => true, 'invalidateCache' => array('frontend'));
    }
    
    /**
     * Event listener function of the Frontend Listing Post Dispatch event.
     *
     * Triggered when the user enters a category listing in the frontend.
     * This function extends the listing template with a new carousel element in the left column
     * under the categories, which shows the activity (placing in cart, buying products) of users on site.
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onFrontendPostDispatch(Enlight_Event_EventArgs $arguments)
    {
      /**@var $controller Shopware_Controllers_Frontend_Index */
      $controller = $arguments->getSubject();
      $view = $controller->View();
      $request = $controller->Request();

      if ($request->getControllerName() !== 'listing' || $request->getModuleName() !== 'frontend' || !$view->hasTemplate()) {
        return;
      }
      
      $aTwoArticles = $this->get2Articles();
      
      $view->assign('aTwoArticles', $aTwoArticles);
      $view->addTemplateDir($this->Path() . 'Views/');
      $view->extendsTemplate('frontend/LiveShoppingCart/live_shopping_cart.tpl');
    }
    
    /**
     * Function to get 2 Articles, bought and placed in cart lately, 
     * these articles will be shown at once after coming to page
     * @todo@all: Documentation
     * @return nfxAtricles 
    */
    public function get2Articles() {
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
        ORDER BY o.ordertime ASC
        LIMIT 0, 1
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
        ORDER BY o.ordertime ASC
        LIMIT 0, 1
      ";
      $nfxArticles['baught'] = Shopware()->Db()->fetchAll($sql_baught, array());
      $nfxArticles['in_cart'] = Shopware()->Db()->fetchAll($sql_cart, array());
      return $nfxArticles;
    }
    /**
     * Event listener function which returns the controller path of the plugin widget controller.
     *
     * @param Enlight_Event_EventArgs $arguments
     *
     * @return string
     */
    public function onGetFrontendController(Enlight_Event_EventArgs $arguments)
    {
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );
        return $this->Path() . 'Controllers/Frontend/LiveShoppingCart.php';
    }
}