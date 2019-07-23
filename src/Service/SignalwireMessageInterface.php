<?php

namespace Drupal\signalwire\Service;


interface SignalwireMessageInterface {
    public function saveMessage(array $message);
    public function getMessage();
}