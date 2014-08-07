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

/**
 * todo@all: Documentation
 */
class Shopware_Plugins_Backend_NfxProfitAnalize_Bootstrap extends Shopware_Components_Plugin_Bootstrap {

    /**
     * Returns the plugin label which displayed in the plugin information and
     * in the plugin manager.
     * @return string
     */
    public function getLabel() {
        return 'Profit Analyse';
    }

    /**
     * Returns the plugin information
     * @return array
     */
    public function getInfo() {
        return array(
            'version' => $this->getVersion(),
            'autor' => 'nfx:MEDIA',
            'copyright' => 'Copyright (c) 2014, nfx:MEDIA',
            'label' => $this->getLabel(),
            'source' => '',
            'description' => '<iframe src="http://nfxmedia.de/tools/plugin_desc.html"  style="min-height:200px; height:auto !important;width:100%" frameBorder="0"></iframe>',
            'license' => '',
            'support' => 'info@nfxmedia.de',
            'link' => 'http://www.nfxmedia.de',
            'changes' => '',
            'revision' => '4840'
        );
    }

    /**
     * Returns the plugin version.
     *
     * @return string
     */
    public function getVersion() {
        return '1.0.0';
    }

    /**
     * Creates the configuration fields and subscribes the post dispatch
     * event of the backend listing container.
     * Also adds several fields for the configuration of plugin: 
     * includesTax, shippingCosts, runningCosts. These fields we use later
     * in the controller to count Profit
     *
     * @return array with data which tells system to refresh cache
     */
    public function install() {
        if ($this->nfxLicenceCheck() == false) {
            throw new Exception('Lizenz nicht g&uuml;ltig. Bitte kontaktieren Sie info@nfxmedia.de');
        }
        $this->createEvents();
        $this->createMenu();
        $this->createForm();

        return array('success' => true, 'invalidateCache' => array('backend'));
    }

    /**
     * Updates the plugin
     * @return type
     */
    public function update($version) {
        if ($this->nfxLicenceCheck() == false) {
            throw new Exception('Lizenz nicht g&uuml;ltig. Bitte kontaktieren Sie info@nfxmedia.de');
        }
        $this->createForm();
        $this->createEvents();
        return array('success' => true, 'invalidateCache' => array('config'));
    }

    /**
     * Removes plugin from usage
     *
     * @return array with data which tells system to refresh cache
     */
    public function uninstall() {
        return array('success' => true, 'invalidateCache' => array('backend'));
    }

    /**
     * Creates and subscribe the events
     */
    protected function createEvents() {
        // extend order extjs module
        $this->subscribeEvent(
                'Enlight_Controller_Action_PostDispatch_Backend_Order', 'onPostDispatchBackendOrder'
        );
        // add path to backend controller
        $this->subscribeEvent(
                'Enlight_Controller_Dispatcher_ControllerPath_Backend_NfxProfitAnalize', 'onGetControllerPathBackend'
        );
    }

    /**
     * Creates the menu item
     */
    protected function createMenu() {
        $parent = $this->Menu()->findOneBy('label', 'Artikel');

        $item = $this->createMenuItem(array(
            'label' => 'Profit Analyse',
            'class' => 'sprite-ui-scroll-pane-detail',
            'active' => 1,
            'action' => 'index',
            'controller' => 'NfxProfitAnalize',
            'parent' => $parent,
            'style' => 'background-position: 5px 5px;'
        ));
        $this->Menu()->addItem($item);
        $this->Menu()->save();
    }

    /**
     * Creates the form of the Plugin
     */
    protected function createForm() {
        /*
         * Create and store the values of plugin manager form.
         */
        $form = $this->Form();

        //checkbox which determines whether the Einkaufspreis (merchant price) includes Tax
        $form->setElement('checkbox', 'includesTax', array(
            'label' => 'Einkaufspreis inkl. Mwst.',
            'value' => false,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('text', 'shippingCosts', array(
            'label' => 'Versandkosten (fix oder prozentual)',
            'value' => '',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('text', 'runningCosts', array(
            'label' => 'Laufende Kosten (fix oder prozentual)',
            'value' => '',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    /**
     * Event listener function which returns the controller path of the plugin controller.
     *
     * @param Enlight_Event_EventArgs $arguments
     *
     * @return string
     */
    public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args) {
        if (Shopware()->Plugins()->Backend()->NfxProfitAnalize()->demoVersionExpired())
            throw new Exception("NFX Profie Analize - Demo version expired!");
        return dirname(__FILE__) . '/Controllers/Backend/NfxProfitAnalize.php';
    }

    /**
     * Event listener function of the Backend Listing Post Dispatch event.
     *
     * Triggered when the user opens page Orders in backend area.
     * This function calls to the overrided extJs classes order.js and list.js
     * when system fires controller Order and action load. If action getList 
     * fires we also redirect to our internal controller NfxProfitColumn where 
     * action getList is modified
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onPostDispatchBackendOrder(Enlight_Event_EventArgs $args) {
        if ($this->demoVersionExpired())
            return;

        $args->getSubject()->View()->addTemplateDir(
                $this->Path() . 'Views/'
        );
        // if the controller action name equals "load" we have to load all application components.
        if ($args->getRequest()->getModuleName() === 'backend' && $args->getRequest()->getControllerName() === 'Order' && $args->getRequest()->getActionName() === 'load') {
            $args->getSubject()->View()->extendsTemplate(
                    'backend/order/nfx_profit_column/view/list/list.js'
            );

            $args->getSubject()->View()->extendsTemplate(
                    'backend/order/nfx_profit_column/view/list/position.js'
            );
            $args->getSubject()->View()->extendsTemplate(
                    'backend/order/nfx_profit_column/view/detail/position.js'
            );
            $args->getSubject()->View()->extendsTemplate(
                    'backend/order/nfx_profit_column/model/order.js'
            );
            $args->getSubject()->View()->extendsTemplate(
                    'backend/order/nfx_profit_column/model/position.js'
            );
        }
        if ($args->getRequest()->getModuleName() === 'backend' && $args->getRequest()->getControllerName() === 'Order' && $args->getRequest()->getActionName() === 'getList') {
            $orders = $args->getSubject()->View()->getAssign('data');
            $orderIDs = array();
            foreach ($orders as $key => &$order) {
                $order = $this->computeProfit($order);
            }
            $args->getSubject()->View()->assign('data', $orders);
        }
    }

    /*
     * compute the profit for this order
     */
    private function computeProfit($order) {
         //get values from NfxProfitColumn plugin config
        $config = Shopware()->Plugins()->Backend()->NfxProfitAnalize()->Config();
        $basePriceIncludesTax = (bool) $config->includesTax;
        $shippingCosts = $config->shippingCosts;
        $runningCosts = $config->runningCosts;
    
        $decmnt = 0;
        $totalPrice = 0;
        foreach ($order['details'] as &$article) {

            if ($article['articleId'] > 0) {
                $sql = "
                    SELECT ap.baseprice
                    FROM s_articles_prices AS ap
                    JOIN s_articles_details AS ad ON ap.articleID = ad.articleID
                      AND ad.ordernumber = '" . $article['articleNumber'] . "'
                      AND ad.articleID = '" . $article['articleId'] . "'
                    LIMIT 1
                  ";
                $aBasePrice = Shopware()->Db()->fetchAll($sql, array());
                $tax_decmt = 0;
                $shc_decmt = 0;
                $run_decmt = 0;
                $totalPrice_temp = $article['price'] * $article['quantity'];
                $totalPrice += $totalPrice_temp;
                if (!empty($shippingCosts)) {
                    if (strpos($shippingCosts, '%') > 0) {
                        $shippingCosts = rtrim($shippingCosts, '%');
                        if (strlen($shippingCosts) == 1) {
                            $shippingCosts = '1.0' . $shippingCosts;
                        } else {
                            $shippingCosts = '1.' . $shippingCosts;
                        }
                        $shc_decmt = $totalPrice_temp - ($totalPrice_temp / floatval($shippingCosts));
                    } else {
                        $shc_decmt = $shippingCosts * $article['quantity'];
                    }
                }
                if (!empty($runningCosts)) {
                    if (strpos($runningCosts, '%') > 0) {
                        $runningCosts = rtrim($runningCosts, '%');
                        if (strlen($runningCosts) == 1) {
                            $runningCosts = '1.0' . $runningCosts;
                        } else {
                            $runningCosts = '1.' . $runningCosts;
                        }
                        $run_decmt = $totalPrice_temp - ($totalPrice_temp / floatval($runningCosts));
                    } else {
                        $run_decmt = $runningCosts * $article['quantity'];
                    }
                }
                if (!$basePriceIncludesTax) {
                    $taxRate = Shopware()->Db()->fetchOne(
                            "SELECT description FROM s_core_tax WHERE id = '" . $article['taxId'] . "'"
                    );
                    if (strpos($taxRate, '%') > 0) {
                        $taxRate = rtrim($taxRate, '%');
                        if (strlen($taxRate) == 1) {
                            $taxRate = '1.0' . $taxRate;
                        } else {
                            $taxRate = '1.' . $taxRate;
                        }
                        $tax_decmt = $totalPrice_temp - ($totalPrice_temp / floatval($taxRate));
                    } else {
                        $tax_decmt = $taxRate * $article['quantity'];
                    }
                }
                //$totalPrice += $totalPrice_temp;
                $temp_decmnt = (float) $aBasePrice[0]['baseprice'] * (int) $article['quantity'] + $shc_decmt + $run_decmt + $tax_decmt;
                $decmnt += $temp_decmnt;
                $article['profitAmountDetails'] = round($totalPrice_temp - $temp_decmnt, 2);
            }
        }
        $order['profitAmount'] = round($totalPrice - $decmnt /*- 1.68*/, 2);
        return $order;
    }
    
    /**
     * This method checks back if the software has a valid licence to use the plugin. For this plugin the lic_easy.php passes all requests as long as the host is not blocked in our blacklist.
     * @param ActionHappend $action
     * @return bool
     */
    public function nfxLicenceCheck() {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveDefault();
        $host = $shop->getHost();
        $host = ($host) ? $host : $_SERVER['HTTP_HOST'];
        $handle = fopen("http://www.nfxmedia.de/tools/lic_easy.php?lic_host=" . $host . "&software=NfxProfitAnalize", "r");
        $result = fgets($handle);
        if ($result == 'nfxsaidok') {
            //second request (to version.php)
            $plugin_version = $this->getVersion();
            $handle = fopen("http://www.nfxmedia.de/tools/version.php?lic_host=" . $host . "&software=NfxProfitAnalize&version=" . $plugin_version, "r");
            $result = fgets($handle);
            if ($result == 'nfxsaidok') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * function to check if the demo free version has expired
     * @return type
     */
    private function demoVersionExpired() {
        $today = strtotime(date("Y-m-d"));
        $expiration_date = strtotime("2014-06-30");

        return $today > $expiration_date;
    }

}
