<?php

/**
 * uapvAuthPlugin configuration.
 * 
 * @package     uapvAuthPlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: PluginConfiguration.class.php 12675 2008-11-06 08:07:42Z Kris.Wallsmith $
 */
class uapvAuthPluginConfiguration extends sfPluginConfiguration
{
  public function setup() // loads handler if needed
  {

    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      $configCache = $this->configuration->getConfigCache();
      $configCache->registerConfigHandler('config/parameters.yml', 'sfDefineEnvironmentConfigHandler',
        array('prefix' => 'parameters_'));
      $configCache->checkConfig('config/parameters.yml');
    }
}



  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      $configCache = $this->configuration->getConfigCache();
      include($configCache->checkConfig('config/parameters.yml'));
    }
  }
}
