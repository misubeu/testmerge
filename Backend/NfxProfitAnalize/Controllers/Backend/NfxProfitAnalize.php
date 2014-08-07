<?php
/**
 * nfx ProfitAnalize
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2014, nfx:MEDIA
 * @author nf, kz - info@nfxmedia.de;
 * @package nfxMEDIA
 * @subpackage NFXProfitAnalize
 * @version 1.0.0 initial release of the plugin 
 *
 * Plugin which adds the page Profit-Analize to the top menu item Article.
 * This page is very similar to the page Articles, but has some modifications:
 * several unnecessary buttons removed, added three columns: saled items, 
 * sales price and total profit.
 */

class Shopware_Controllers_Backend_NfxProfitAnalize extends Shopware_Controllers_Backend_ArticleList
{
  /** 
   * Function that fires when system calls to the class Shopware_Controllers_Backend_NfxProfitAnalize
   * 
   * It extends the existing template directory with additional views of plugin ProfitAnalize
   * 
   * @return bool
   */
  public function init()
  {
    $this->View()->addTemplateDir(dirname(__FILE__) . "/../../Views/");
    parent::init();
  }

  /**
   * Initial action of this controller.
   *
   * If no action defined in the request, the indexAction called automatically.
   * This action is used to initial the profit analize app. It loads the plugin 
   * configuration with app.js
   * 
   * @return bool
   */
  public function indexAction()
  {
    $this->View()->loadTemplate("backend/nfx_profit_analize/app.js");
  }
  
  /**
   * Main action of the controller
   *
   * System calls to this action when user chooses page Profit-Analize in top menu.
   * The function makes corrections to the returned list of articles and adds
   * three additional items to data of each article: saledItems, salesPrice, totalProfit.
   *
   * @return modified array of articles data
   */
  public function listAction()
  {
    if (!$this->_isAllowed('read', 'article')) {
      /** @var $namespace Enlight_Components_Snippet_Namespace */
      $this->View()->assign(array(
        'success' => false,
        'data' => $this->Request()->getParams(),
        'message' => 'Insufficient permissions' )
      );
      return;
    }

    $categoryId   = $this->Request()->getParam('categoryId');
    $filterParams = $this->Request()->getParam('filter', array());
    $filterBy     = $this->Request()->getParam('filterBy');
    $showVariants = (bool) $this->Request()->getParam('showVariants', false);
    $order        = $this->Request()->getParam('sort', null);
    $start        = $this->Request()->getParam('start', 0);
    $limit        = $this->Request()->getParam('limit', 20);

    $filters = array();
        foreach ($filterParams as $singleFilter) {
      $filters[$singleFilter['property']] = $singleFilter['value'];
    }


    $categorySql = '';
    $sqlParams = array();
    if (!empty($categoryId) && $categoryId !== 'NaN') {
      $categorySql =  "
        LEFT JOIN s_categories c
          ON c.id = ?
        LEFT JOIN s_categories c2
          ON c2.left >= c.left
          AND c2.right <= c.right
        JOIN s_articles_categories ac
          ON ac.articleID = articles.id AND ac.categoryID = c2.id
      ";
      $sqlParams[] = $categoryId;
    }

    $filterSql = 'WHERE 1 = 1';
    if (isset($filters['search'])) {
      $filterSql .= " AND (details.ordernumber LIKE ? OR articles.name LIKE ? OR suppliers.name LIKE ? OR articles.description_long LIKE ?)";
      $sqlParams[] = '%' . $filters['search'] . '%';
      $sqlParams[] = '%' . $filters['search'] . '%';
      $sqlParams[] = '%' . $filters['search'] . '%';
      $sqlParams[] = '%' . $filters['search'] . '%';
    }

      if ($filterBy == 'notInStock') {
        $filterSql .= " AND details.instock <= 0 ";
      }

      if ($filterBy == 'noCategory') {
        $categorySql = "
          LEFT JOIN s_articles_categories ac
          ON ac.articleID = articles.id
        ";
        $filterSql .= " AND ac.id IS NULL ";
      }

      if ($filterBy == 'noImage') {
        $categorySql = "
          LEFT JOIN s_articles_img as mainImages
          ON mainImages.articleID = articles.id
        ";
        $filterSql .= " AND mainImages.id IS NULL ";
      }

    // Make sure that whe don't get a cold here
    $columns = array('number', 'name', 'supplier', 'active', 'inStock', 'price', 'tax' );
    $directions = array('ASC', 'DESC');

    if (null === $order || !in_array($order[0]['property'] , $columns) || !in_array($order[0]['direction'], $directions)) {
      $order = 'id DESC';
    } else {
      $order = array_shift($order);
      $order = $order['property'] . ' ' . $order['direction'];
    }

    if ($showVariants) {
      $sql = "
        SELECT DISTINCT SQL_CALC_FOUND_ROWS
          details.id as id,
          articles.id as articleId,
          articles.name as name,
          articles.configurator_set_id,
          suppliers.name as supplier,
          articles.active as active,
          details.id as detailId,
          details.additionaltext as additionalText,
          details.instock as inStock,
          details.ordernumber as number,
            ROUND(prices.price*(100+tax.tax)/100,2) as `price`,
            tax.tax as tax
        FROM
          s_articles_details as details
        INNER JOIN s_articles as articles
          ON details.articleID = articles.id
        LEFT JOIN s_articles_supplier as suppliers
          ON articles.supplierID = suppliers.id
        LEFT JOIN s_articles_prices prices
          ON prices.articledetailsID = details.id
          AND prices.`to`= 'beliebig'
          AND prices.pricegroup='EK'
        LEFT JOIN s_core_tax AS tax
          ON tax.id = articles.taxID
        $categorySql
        $filterSql
        AND details.kind <> 3
        ORDER BY $order, details.ordernumber ASC
        LIMIT  $start, $limit
      ";
    } else {
      $sql = "
        SELECT DISTINCT SQL_CALC_FOUND_ROWS
          details.id as id,
          articles.id as articleId,
          articles.name as name,
          articles.configurator_set_id,
          suppliers.name as supplier,
          articles.active as active,
          details.id as detailId,
          details.additionaltext as additionalText,
          details.instock as inStock,
          details.ordernumber as number,
          ROUND(prices.price*(100+tax.tax)/100,2) as `price`,
          tax.tax as tax
        FROM s_articles as articles
        INNER JOIN s_articles_details as details
          ON articles.main_detail_id = details.id
        LEFT JOIN s_articles_supplier as suppliers
          ON articles.supplierID = suppliers.id
        LEFT JOIN s_articles_prices prices
          ON prices.articledetailsID = details.id
          AND prices.`to`= 'beliebig'
          AND prices.pricegroup='EK'
        LEFT JOIN s_core_tax AS tax
          ON tax.id = articles.taxID
        $categorySql
        $filterSql
        ORDER BY $order, details.ordernumber ASC
        LIMIT  $start, $limit
      ";
    }

    $articles = Shopware()->Db()->fetchAll($sql, $sqlParams);

    $sql= "SELECT FOUND_ROWS() as count";
    $count = Shopware()->Db()->fetchOne($sql);

    foreach ($articles as $key => &$article) {
      // Check for configurator
      $isConfigurator = !empty($article['configurator_set_id']);
      $articles[$key]['hasConfigurator'] = ($isConfigurator !== false);

      // Check for Image
      $image = Shopware()->Db()->fetchOne(
        'SELECT img FROM s_articles_img WHERE articleID = ? AND main = 1 AND article_detail_id IS NULL',
        $article['articleId']
      );

      if ($image) {
        $articles[$key]['imageSrc']= $image . '_140x140.jpg';
      }

      // Check for Categories
      $hasCategories = Shopware()->Db()->fetchOne(
        'SELECT id FROM s_articles_categories WHERE articleID = ?',
        $article['articleId']
      );
      $articles[$key]['hasCategories'] = ($hasCategories !== false);
      
      //saledItems
      $siSql = "
        SELECT sod.quantity, sod.price, sct.description, sap.baseprice
        FROM s_order_details AS sod
        JOIN s_core_tax AS sct ON sct.id = sod.taxID
        JOIN s_articles_prices AS sap ON sap.articleID = sod.articleID
        WHERE sod.articleID = '" . $article['articleId'] . "'
      ";
      $aPricesQuantitiesData = Shopware()->Db()->fetchAll($siSql, array());
      $saled_items = 0;
      $sales_price = 0;
      $total_profit = 0;

      //get values from NfxProfitColumn plugin config
      $config = Shopware()->Plugins()->Backend()->NfxProfitAnalize()->Config();
      $basePriceIncludesTax = (bool) $config->includesTax;
      $shippingCosts = $config->shippingCosts;
      $runningCosts = $config->runningCosts;

      foreach ($aPricesQuantitiesData as $pq_data) {
        $saled_items += $pq_data['quantity'];
        $sales_price += $pq_data['price'] * $pq_data['quantity'];

        $tax_decmt = 0;
        $shc_decmt = 0;
        $run_decmt = 0;
        $totalPrice = $totalPrice_temp = $pq_data['price'] * $pq_data['quantity'];
        if (!empty($shippingCosts)) {
          if (strpos($shippingCosts, '%') > 0) {
            $shippingCosts = rtrim($shippingCosts, '%');
            if (strlen($shippingCosts) == 1) {
              $shippingCosts = '1.0' . $shippingCosts;
            }
            else {
              $shippingCosts = '1.' . $shippingCosts;
            }
            $shc_decmt = $totalPrice_temp - ($totalPrice_temp/floatval($shippingCosts));
          }
          else {
            $shc_decmt = $shippingCosts * $pq_data['quantity'];
          }
        }
        if (!empty($runningCosts)) {
          if (strpos($runningCosts, '%') > 0) {
            $runningCosts = rtrim($runningCosts, '%');
            if (strlen($runningCosts) == 1) {
              $runningCosts = '1.0' . $runningCosts;
            }
            else {
              $runningCosts = '1.' . $runningCosts;
            }
            $run_decmt = $totalPrice_temp - ($totalPrice_temp/floatval($runningCosts));
          }
          else {
            $run_decmt = $runningCosts * $pq_data['quantity'];
          }
        }
        if (!$basePriceIncludesTax) {
          if (strpos($pq_data['description'], '%') > 0) {
            $pq_data['description'] = rtrim($pq_data['description'], '%');
            if (strlen($pq_data['description']) == 1) {
              $pq_data['description'] = '1.0' . $pq_data['description'];
            }
            else {
              $pq_data['description'] = '1.' . $pq_data['description'];
            }
            $tax_decmt = $totalPrice_temp - ($totalPrice_temp/floatval($pq_data['description']));
          }
          else {
            $tax_decmt = $pq_data['description'] * $article['quantity'];
          }
        }
        $decmnt = ((float)$pq_data['baseprice'] * (int)$pq_data['quantity']) + $tax_decmt + $shc_decmt + $run_decmt;

        $total_profit += $totalPrice - $decmnt;

      }

      //saledItems
      $article['saledItems'] = $saled_items;

      //salesPrice
      $article['salesPrice'] = $sales_price;

      //totalProfit
      $article['totalProfit'] = round($total_profit, 2);

    }

    $this->View()->assign(array(
      'success' => true,
      'data'    => $articles,
      'total'   => $count
    ));
  }
}