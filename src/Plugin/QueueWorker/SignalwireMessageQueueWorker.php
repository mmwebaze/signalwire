<?php

namespace Drupal\signalwire\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
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
    protected $sender;
    protected $senderType;

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
        $this->gateWay = $signalwireConfigs->get('client');
        $this->sender = $signalwireConfigs->get('sender');
        $this->senderType = $signalwireConfigs->get('sender_type');
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
        $instance = $this->signalwirePluginManager->createInstance($this->gateWay);
        if ($this->gateWay != 'none'){
            $numbers = explode(',', $data['to']);
            $messageStatus = NULL;

            foreach ($numbers as $number){
                $messageStatus = $instance->sendMessage($data['message'], $this->sender, $number, $this->senderType);
            }

            /*if (is_null($messageStatus)){
                throw new SuspendQueueException('Sending messages has failed and will be tried again later.');
            }*/
        }
    }
}