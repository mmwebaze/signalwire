<?php

namespace Drupal\signalwire\Plugin\Signalwire;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\encrypt\EncryptionProfileManagerInterface;
use Drupal\encrypt\EncryptService;
use Drupal\encrypt\EncryptServiceInterface;
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

  /**
   * Encryption profile.
   *
   * @var EncryptionProfileManagerInterface
   */
  protected $encryptionProfileManager;

  /**
   * Encryption service.
   *
   * @var EncryptService
   */
  protected $encryptService;


    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory, Client $httpClient, EncryptionProfileManagerInterface $encryption_profile_manager, EncryptServiceInterface $encrypt_service) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->httpClient = $httpClient;
        $this->configFactory = $configFactory;
        $this->encryptionProfileManager = $encryption_profile_manager;
        $this->encryptService = $encrypt_service;
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration, $plugin_id, $plugin_definition,
            $container->get('config.factory'),
            $container->get('http_client'),
            $container->get('encrypt.encryption_profile.manager'),
            $container->get('encryption')
        );
    }

}
