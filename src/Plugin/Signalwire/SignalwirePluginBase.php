<?php

namespace Drupal\signalwire\Plugin\Signalwire;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The base class for signalwire plugins.
 *
 * @package Drupal\signalwire\Plugin\Signalwire
 */
abstract class SignalwirePluginBase extends PluginBase implements SignalwireServiceInterface, ContainerFactoryPluginInterface {

    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration, $plugin_id, $plugin_definition,
            $container->get('config.factory')
        );
    }

}