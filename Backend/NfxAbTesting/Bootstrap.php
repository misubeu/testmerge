<?php
/**
 * nfx AbTesting
 *
 * @link http://www.nfxmedia.de
 * @copyright Copyright (c) 2014, nfx:MEDIA
 * @author nf, kz - info@nfxmedia.de;
 * @package nfxMEDIA
 * @subpackage NFXAbTesting
 * @version 1.0.0 initial release of the plugin 
 *
 * Plugin which adds the A/B testing dropdown 
 * to the top of templates overview in the backend area
 * with the ability to choose A and B template for the shop.
 * Half of users will be shown the A template, another half 
 * will be shown the B template.
 * 
 */

/**
 * todo@all: Documentation
 */
class Shopware_Plugins_Backend_NfxAbTesting_Bootstrap extends Shopware_Components_Plugin_Bootstrap {
    /**
     * Returns the plugin label which displayed in the plugin information and
     * in the plugin manager.
     * @return string
     */
    public function getLabel()
    {
      return 'NFX A/B Testing';
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
     * Subscribes the post dispatch event of the backend Config controller,
     * registers a new controller NfxAbTesting which will extend the class
     * Shopware_Controllers_Backend_Config and will be called by the system
     * in the background to make changes to setTemplage function
     *
     * @return array with data which tells system to refresh cache
     */
    public function install()
    {
      $this->createDatabase();
      // extend order extjs module
      $this->subscribeEvent(
        'Enlight_Controller_Action_PreDispatch_Backend_Config',
        'onPreDispatchBackendConfig'
      );
      // add path to backend-controller
      $this->subscribeEvent(
        'Enlight_Controller_Dispatcher_ControllerPath_Backend_NfxAbTesting',
        'onGetBackendController'
      );
      return array('success' => true, 'invalidateCache' => array('backend'));
    }
    
    private function createDatabase()
    {
      $sql = "
        CREATE TABLE IF NOT EXISTS `s_user_templates` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `templateID` int(11) NOT NULL,
          `userIP` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
      ";
      Shopware()->Db()->query($sql);

      $sql = "
        CREATE TABLE IF NOT EXISTS `s_ab_testing_templates` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `templateID` int(11) NOT NULL,
          `determinator` varchar(1),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
      ";

      Shopware()->Db()->query($sql);
    }
    
    /**
     * Removes plugin from usage
     *
     * @return array with data which tells system to refresh cache
     */
    public function uninstall()
    {
      $this->removeDatabase();
      return array('success' => true, 'invalidateCache' => array('backend'));
    }
    
    private function removeDatabase()
    {
      $sql= "DROP TABLE IF EXISTS `s_ab_testing_templates`";
      Shopware()->Db()->query($sql);

      $sql= "DROP TABLE IF EXISTS `s_user_templates`";
      Shopware()->Db()->query($sql);
    }
    /**
     * Event listener function of the Backend Listing Post Dispatch event.
     *
     * Triggered when the user opens page Config in backend area.
     * This function calls to the overrided extJs class template.js
     *
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onPreDispatchBackendConfig(Enlight_Event_EventArgs $args)
    {
      $args->getSubject()->View()->addTemplateDir(
        $this->Path() . 'Views/'
      );
      // if the controller action name equals "load" we have to load all application components.
      if ($args->getRequest()->getModuleName() === 'backend' && $args->getRequest()->getControllerName() === 'Config' && $args->getRequest()->getActionName() === 'getTemplateList') {
        //echo 'repa';
        $args->getSubject()->View()->extendsTemplate(
          'backend/config/nfx_ab_testing/controller/template.js'
        );
        $args->getSubject()->View()->extendsTemplate(
          'backend/config/nfx_ab_testing/view/template/view.js'
        );
      }
    }
    
    /**
     * Event listener function which returns the controller path of the plugin controller.
     *
     * @param Enlight_Event_EventArgs $arguments
     *
     * @return string
     */
    public function onGetBackendController()
    {
      $this->Application()->Snippets()->addConfigDir(
        $this->Path() . 'Snippets/'
      );

      $this->Application()->Template()->addTemplateDir(
        $this->Path() . 'Views/'
      );

      return $this->Path() . 'Controllers/Backend/NfxAbTesting.php';
    }
}