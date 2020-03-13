<?php

namespace Drupal\signalwire\Plugin\Signalwire;


use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberList;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Drupal\Component\Plugin\PluginInspectionInterface;

interface SignalwireServiceInterface extends PluginInspectionInterface {
    /**
     * Retrieves a list of account associated phone numbers purchased through Signalwire.
     *
     * @return IncomingPhoneNumberList
     *   The incoming phone number list.
     */
    public function incomingPhoneNumbers();

    /**
     * Sends an outbound message from one of your SignalWire phone numbers.
     *
     * @param string $message
     *   The message body.
     *
     * @param string $fromNumber
     *   The senders number or number group id.
     *
     * @param string $recipientNumber
     *   The recipient number.
     *
     * @param string $senderType
     *   The type of sender (telephone or number_group)
     *
     * @return MessageList
     *  The message list.
     */
    public function sendMessage($message, $fromNumber, $recipientNumber, $senderType = 'telephone');

    /**
     * Gets a list of Number Groups .
     *
     * @return array
     *   Returns a list of your Number Groups
     */
    public function numberGroups();

    /**
     * Gets number group memberships.
     *
     * @param string $numberGroupId
     *   The number group id.
     *
     * @return array
     *   Returns a list of Number Group's Memberships.
     */
    public function numberGroupMemberships($numberGroupId);

    /**
     * Gets a list of SIP Endpoints.
     *
     * @return array
     *   Returns a list of SIP Endpoints.
     */
    public function phoneNumbers();
}
