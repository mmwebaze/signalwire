<?php

namespace Drupal\signalwire\Service;

/**
 * Defines a Signalwire Message manager service.
 */
interface SignalwireMessageInterface {

    /**
     * Retrieves a message from signalware_messages database table.
     *
     * @param $messageId
     *   A unique id of the scheduled message usually a node id.
     *
     * @return \Drupal\Core\Entity\EntityInterface
     *   A single message.
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
     *
     * @param int $frequency
     *   The frequency the message is sent out. 0 - once, 1 - daily, 2 - weekly and 3 - monthly. Defaults to once.
     *
     * @return int
     *   The status (0 or 1).
     */
    public function setNextSend(int $messageId, int $nextSendDate, int $stopDate, $frequency = 0);

    /**
     * Gets messages by sending date.
     *
     * @param int $sendDate
     *   The send date unix timestamp.
     *
     * @param string $entityType
     *   The entity type or machine name.
     *
     * @return \Drupal\Core\Database\StatementInterface|null
     *   Array of results of a query run against a database.
     */
    public function getMessagesBySendDate(int $sendDate, $entityType = 'node');
}
