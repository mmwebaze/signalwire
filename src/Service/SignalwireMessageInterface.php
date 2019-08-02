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
     *    'frequency' => '0, 1 or 2'
     *    'created' => 'Unix timestamp message was created',
     *    'date_next_send' => 'Unix timestamp message will next be sent',
     *    'date_stop' => 'Unix timestamp message sending will stop'
     *   )
     * @return integer
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
     * @param int $date
     *   The next send unix timestamp.
     *
     * @return integer
     *   The status (0 or 1).
     */
    public function setNextSend(int $messageId, int $date);

    /**
     * Removes a message from  signalware_messages database table. Any messages already queued however won't be removed.
     *
     * @param integer $messageId
     *   The id of the message.
     *
     * @return integer
     *   The status (0 or 1).
     */
    public function removeMessage(int $messageId);
}