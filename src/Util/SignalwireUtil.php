<?php

namespace Drupal\signalwire\Util;

/**
 * Utility class with various helper functions.
 *
 * @package Drupal\signalwire\Util
 */
class SignalwireUtil {

    /**
     * @param integer $sendTimeStamp
     *   The unix timestamp the message was last sent.
     *
     * @param string $frequency
     *   The frequency (daily, weekly, and monthly) a the message will be sent.
     *
     * @return integer
     *   A unix timestamp the message will next be sent.
     */
    public static function nextSendTimeStamp($sendTimeStamp, $frequency){
        //$timenow = date('Y-m-d');
        $nextSendTimeStamp = $sendTimeStamp;

        switch ($frequency) {
            case 'daily':

                $nextSendTimeStamp = $nextSendTimeStamp + (24 * 60 * 60);
                break;
            case 'weekly':

                $nextSendTimeStamp = $nextSendTimeStamp + (7 * 24 * 60 * 60);
                break;
            case 'monthly':

                $nextSendTimeStamp = $nextSendTimeStamp + (31 * 24 * 60 * 60);
                break;
        }

        return $nextSendTimeStamp;
    }
    public static function processNumberGroups(){

    }
    //thoughtbot
}