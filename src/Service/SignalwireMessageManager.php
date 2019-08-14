<?php

namespace Drupal\signalwire\Service;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * A signalwire message manager service.
 */
class SignalwireMessageManager implements SignalwireMessageInterface {

    /**
     * Drupal\Core\Database\Driver\mysql\Connection definition.
     *
     * @var \Drupal\Core\Database\Driver\mysql\Connection
     */
    protected $connection;

    /**
     * The logger channel factory.
     *
     * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
     */
    protected $loggerChannelFactory;

    /**
     * The messenger service.
     *
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;

    /**
     * @var EntityTypeManager
     */
    protected $entityTypeManager;

    /**
     * SignalwireMessageManager constructor.
     *
     * @param Connection $connection
     *   The database connection service.
     *
     * @param EntityTypeManager $entityTypeManager
     *   The entity type service.
     *
     * @param LoggerChannelFactoryInterface $loggerChannelFactory
     *   The logger channel factory.
     *
     * @param MessengerInterface $messenger
     *   A messenger service.
     */
    public function __construct(Connection $connection,EntityTypeManager $entityTypeManager, LoggerChannelFactoryInterface $loggerChannelFactory, MessengerInterface $messenger) {
        $this->connection = $connection;
        $this->entityTypeManager = $entityTypeManager;
        $this->loggerChannelFactory = $loggerChannelFactory->get('signalwire');
        $this->messenger = $messenger;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage($messageId) {

    }

    /**
     * {@inheritdoc}
     */
    public function getMessagesBySendDate(int $sendDate, string $entityType = 'node') {

       $q = $this->connection->select('node', 'n');
       $q->innerJoin('node__field_signalwire_message','sm', 'n.nid = sm.entity_id');
       $q->innerJoin('node__field_send_date', 'sd','sd.entity_id = n.nid');
       $q->innerJoin('node__field_stop_date', 'sp', 'sp.entity_id = n.nid');
       $q->innerJoin('node__field_send_status', 'ss', 'ss.entity_id = n.nid');
       $q->innerJoin('node__field_message_frequency', 'fm', 'fm.entity_id = n.nid');
       $q->innerJoin('node__field_recipients', 'fr', 'fr.entity_id = n.nid');
       $q->fields('n', ['nid']);
       $q->fields('sd', ['field_send_date_value']);
       $q->fields('sp', ['field_stop_date_value']);
       $q->fields('sm', ['field_signalwire_message_value']);
       $q->fields('fm', ['field_message_frequency_value']);
       $q->fields('fr', ['field_recipients_value']);
       $q->condition('n.type', 'signalwire_message');
       $q->condition('sd.field_send_date_value', $sendDate);
       $q->where( 'sd.field_send_date_value <= sp.field_stop_date_value');
       $results = $q->execute();

       return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function setNextSend(int $messageId, int $nextSendDate, int $stopDate, int $frequency = 0){
        $storage = $this->entityTypeManager->getStorage('node');
        $message = $storage->load($messageId);
        $message->field_send_date = $nextSendDate;

        //check if frequency is once, then set send status to off.
        if ($frequency == 0){
            $message->field_send_status = 0;
        }
        else {
            if ($nextSendDate > $stopDate){
                //set send status to stop.
                $message->field_send_status = 0;
            }
        }

        $message->save();
    }
}