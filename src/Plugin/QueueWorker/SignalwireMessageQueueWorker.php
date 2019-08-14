<?php

namespace Drupal\signalwire\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\signalwire\Plugin\Signalwire\SignalwireManager;
use Drupal\signalwire\Service\SignalwireMessageInterface;
use Drupal\signalwire\Util\SignalwireUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @QueueWorker(
 * id = "signalwire_message_queue",
 * title = @Translation("Signalwire message queue processor"),
 * cron = {"time" = 60}
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
     * The sender telephone number or number group id.
     *
     * @var string
     */
    protected $sender;

    /**
     * The sender type (telephone or number_group).
     *
     * @var string
     */
    protected $senderType;

    /**
     * The signalwire message manager.
     *
     * @var SignalwireMessageInterface
     */
    protected $signalwireMessage;

    /**
     * The logger factory service..
     *
     * @var LoggerChannelFactoryInterface
     */
    protected $loggerChannelFactory;

    /**
     * SignalwireMessageQueueWorker constructor.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     *
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     *
     * @param array $plugin_definition
     *   The plugin definition.
     *
     * @param SignalwireManager $signalwirePluginManager
     *   The signalwire manager plugin.
     *
     * @param ConfigFactory $configFactory
     *   The config factory service.
     *
     * @param LoggerChannelFactoryInterface $loggerChannelFactory
     *   The logger factory service.
     *
     * @param SignalwireMessageInterface $signalwireMessage
     *   The signalwire message manager service.
     */
    public function __construct(array $configuration, string $plugin_id, array $plugin_definition, SignalwireManager $signalwirePluginManager,
                                ConfigFactory $configFactory, SignalwireMessageInterface $signalwireMessage, LoggerChannelFactoryInterface $loggerChannelFactory) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->signalwirePluginManager = $signalwirePluginManager;
        $signalwireConfigs = $configFactory->get('signalwire.config');
        $this->gateWay = $signalwireConfigs->get('client');
        $this->sender = $signalwireConfigs->get('sender');
        $this->senderType = $signalwireConfigs->get('sender_type');
        $this->signalwireMessage = $signalwireMessage;
        $this->loggerChannelFactory = $loggerChannelFactory;
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
            $container->get('config.factory'),
            $container->get('signalwire_messaging.manager'),
            $container->get('logger.factory')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function processItem($data) {
        $instance = $this->signalwirePluginManager->createInstance($this->gateWay);
        //$numbers = $data['to'];

        $messageStatus = $instance->sendMessage($data['message'], $this->sender, $data['to'], $this->senderType);

        //$messageStatus = $instance->sendMessage($data['message'], $this->sender, $number, $this->senderType);
        if (!is_null($messageStatus)){
            $nextSendDate = SignalwireUtil::nextSendTimeStamp($data['field_send_date'], $data['frequency']);
            $this->signalwireMessage->setNextSend($data['node'], $nextSendDate, $data['field_stop_date']);
            $this->loggerChannelFactory->get('signalwire')->notice('Node id: '.$data['node'].' Next send: '.$nextSendDate.' Prev send: '.$data['field_send_date']);
        }
        else{
            //throw new SuspendQueueException('Sending messages has failed and will be tried again later.');
            $this->loggerChannelFactory->get('signalwire')->notice('Unable to send this message: @'.$data['node']);
        }
    }
}