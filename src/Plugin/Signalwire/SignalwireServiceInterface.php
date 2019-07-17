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
     *   The senders number.
     *
     * @param string $recipientNumber
     *   The recipient number.
     *
     * @return MessageList
     *  The message list.
     */
    public function sendMessage(string $message, string $fromNumber, string $recipientNumber);

    /**
     * Retrieves a list of
     * @return mixed
     */
    public function numberGroups();
}