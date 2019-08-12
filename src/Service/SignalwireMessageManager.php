<?php

namespace Drupal\signalwire\Service;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManager;

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
     *   The connection service.
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
    public function __construct(Connection $connection, EntityTypeManager $entityTypeManager, LoggerChannelFactoryInterface $loggerChannelFactory, MessengerInterface $messenger) {
        $this->connection = $connection;
        $this->entityTypeManager = $entityTypeManager;
        $this->loggerChannelFactory = $loggerChannelFactory->get('signalwire');
        $this->messenger = $messenger;
    }

    /**
     * {@inheritdoc}
     */
    public function saveMessage(array $values) {

        try{
            if (isset($values)) {
                $value = array(
                    'node' => $values['node'],
                    'message' => $values['message'],
                    //'from' => $values['from'],
                    'recipients' => $values['recipients'],
                    'frequency' => $values['frequency'],
                    'created' => $values['created'],
                    'next_send_date' => $values['next_send_date'],
                    'stop_date' => $values['stop_date'],
                );
                $this->connection->insert('signalware_messages')->fields(['node', 'message', 'recipients',
                    'frequency', 'created', 'next_send_date', 'stop_date'])
                    ->values($value)->execute();

                $this->messenger->addMessage("The text message associated with id ".$values['node']." has been saved.", MessengerInterface::TYPE_STATUS);
                $this->loggerChannelFactory->notice('Message with id '.$values['node'].' has been added.');
            }
        }
        catch (\Exception $e) {
            //log for admin and debug purposes.
            $this->loggerChannelFactory->notice($e->getMessage());
            //Notify user something has gone seriously wrong with saving the message.
            $this->messenger->addMessage('Saving message failed. ', MessengerInterface::TYPE_ERROR);
        }
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
       $storage = $this->entityTypeManager->getStorage($entityType);
       $query = $storage->getQuery();
       $query->condition('field_send_date', $sendDate);
       $query->condition('field_stop_date', $sendDate, '<=');
       $query->condition('field_send_status', 1);
       $messageIds = $query->execute();
       $messages = $storage->loadMultiple($messageIds);

       return $messages;
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