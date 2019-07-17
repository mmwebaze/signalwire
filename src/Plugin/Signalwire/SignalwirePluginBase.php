<?php

namespace Drupal\signalwire\Plugin\Signalwire;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The base class for signalwire plugins.
 *
 * @package Drupal\signalwire\Plugin\Signalwire
 */
abstract class SignalwirePluginBase extends PluginBase implements SignalwireServiceInterface, ContainerFactoryPluginInterface {

    /**
     * An http client service.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var \Drupal\Core\Config\ConfigFactory
     */
    protected $configFactory;

    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory, Client $httpClient) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->httpClient = $httpClient;
        $this->configFactory = $configFactory;
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration, $plugin_id, $plugin_definition,
            $container->get('config.factory'),
            $container->get('http_client')
        );
    }

}