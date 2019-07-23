<?php

namespace Drupal\signalwire\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\signalwire\Plugin\Signalwire\SignalwireManager;
use Drupal\signalwire\Plugin\Signalwire\SignalwirePluginBase;
use Drupal\signalwire\Plugin\Signalwire\SignalwireServiceInterface;
use Drupal\signalwire\Service\SignalwireMessageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @QueueWorker(
 * id = "signalwire_message_queue",
 * title = @Translation("Signalwire message queue processor"),
 * cron = {"time" = 10}
 * )
 */
class SignalwireMessageQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

    /**
     * A signalwire plugin manager service.
     *
     * @var SignalwireManager
     */
    protected $signalwirePluginManager;
    /**
     * The configured signalwire gateway.
     *
     * @var string
     */
    protected $gateWay;

    /**
     * SignalwireMessageQueueWorker constructor.
     *
     * @param array $configuration
     * @param string $plugin_id
     * @param array $plugin_definition
     * @param SignalwireManager $signalwirePluginManager
     * @param ConfigFactory $configFactory
     */
    public function __construct(array $configuration, string $plugin_id, array $plugin_definition, SignalwireManager $signalwirePluginManager, ConfigFactory $configFactory) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->signalwirePluginManager = $signalwirePluginManager;
        $signalwireConfigs = $configFactory->get('signalwire.config');
        $this->gateway = $signalwireConfigs->get('client');
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('plugin.manager.signalwire_manager'),
            $container->get('config.factory')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function processItem($data) {
        $instance = $this->signalwirePluginManager->createInstance($this->gateway);
        $instance->sendMessage($data['message'], $data['from'], $data['to']);
    }
}