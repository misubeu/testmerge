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

class Shopware_Controllers_Backend_NfxAbTesting extends Shopware_Controllers_Backend_Config
{
  /**
   * The only action of this controller.
   *
   * This action is used to rewrite the backend Config saveTemplateAction and save additional data.
   *
   * @return modified array of orders data
   */
  public function saveTemplateAction()
  {
    
  }
}