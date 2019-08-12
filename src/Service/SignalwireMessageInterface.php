<?php

namespace Drupal\signalwire\Service;


interface SignalwireMessageInterface {

    /**
     * Saves a single scheduled message to the signalware_messages database table.
     *
     * @param array $message
     *   The key value pair that define a single message record e.g
     *   array(
     *    'node' => 'node or message id',
     *    'message' => 'The message body',
     *    'recipients' => 'Serialized array of recipient numbers',
     *    'frequency' => '0, 1, 2, or 3'
     *    'created' => 'Unix timestamp message was created',
     *    'date_next_send' => 'Unix timestamp message will next be sent',
     *    'date_stop' => 'Unix timestamp message sending will stop'
     *   )
     * @return int
     *   The status (0 or 1) of the insert process.
     */
    public function saveMessage(array $message);

    /**
     * Retrives a message from signalware_messages database table.
     *
     * @param $messageId
     *   A unique id of the scheduled message usually a node id.
     *
     * @return array
     *   A single message record as defined in the saveMessage routine.
     */
    public function getMessage($messageId);

    /**
     * Sets the date a message will next be sent.
     *
     * @param int $messageId
     *   The id of the message.
     *
     * @param int $nextSendDate
     *   The next send unix timestamp.
     *
     * @param int $stopDate
     *   The stop date unix timestamp.
     * @param int $frequency
     *   The frequency the message is sent out. 0 - once, 1 - daily, 2 - weekly and 3 - monthly. Defaults to once.
     *
     * @return int
     *   The status (0 or 1).
     */
    public function setNextSend(int $messageId, int $nextSendDate, int $stopDate, int $frequency = 0);

    /**
     * Gets messages by sending date.
     *
     * @param int $sendDate
     *   The send date unix timestamp.
     *
     * @param string $entityType
     *   The entity type.
     *
     * @return \Drupal\Core\Entity\EntityInterface[]
     *   Array of entity objects.
     */
    public function getMessagesBySendDate(int $sendDate, string $entityType);
}