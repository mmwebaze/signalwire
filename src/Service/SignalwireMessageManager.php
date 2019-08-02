<?php

namespace Drupal\signalwire\Service;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;

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
     * SignalwireMessageManager constructor.
     *
     * @param Connection $connection
     *   The connection service.
     *
     * @param LoggerChannelFactoryInterface $loggerChannelFactory
     *   The logger channel factory.
     *
     * @param MessengerInterface $messenger
     *   A messenger service.
     */
    public function __construct(Connection $connection, LoggerChannelFactoryInterface $loggerChannelFactory, MessengerInterface $messenger) {
        $this->connection = $connection;
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
                    'from' => $values['from'],
                    'recipients' => $values['recipients'],
                    'frequency' => $values['frequency'],
                    'created' => $values['created'],
                    'next_send_date' => $values['next_send_date'],
                    'stop_date' => $values['stop_date'],
                );
                $this->connection->insert('signalware_messages')->fields(['node', 'message', 'from', 'recipients',
                    'frequency', 'created', 'next_send_date', 'stop_date'])
                    ->values($value)->execute();

                $this->messenger->addMessage("The text message associated with id ".$values['node']." has been saved.", MessengerInterface::TYPE_STATUS);
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
    public function setNextSend(int $messageId, int $date){

    }

    /**
     * {@inheritdoc}
     */
    public function removeMessage(int $messageId){

    }
}