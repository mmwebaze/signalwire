<?php

namespace Drupal\signalwire\Util;

/**
 * Utility class with various helper functions.
 *
 * @package Drupal\signalwire\Util
 */
class SignalwireUtil {

    /**
     * @param int $sendTimeStamp
     *   The unix timestamp the message was last sent.
     *
     * @param string $frequency
     *   The frequency (once - 0, daily - 1, weekly - 2, and monthly - 3) a the message will be sent.
     *
     * @return integer
     *   A unix timestamp the message will next be sent.
     */
    public static function nextSendTimeStamp(int $sendTimeStamp, string $frequency){

        switch ($frequency) {
            case '1':

                return $sendTimeStamp + (24 * 60 * 60);
            case '2':

                return $sendTimeStamp + (7 * 24 * 60 * 60);
            case '3':

                return $sendTimeStamp + (31 * 24 * 60 * 60);
            default: //everything else defaults to once

                return $sendTimeStamp;
        }
    }
}