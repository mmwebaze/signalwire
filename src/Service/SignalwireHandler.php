<?php

namespace Drupal\signalwire\Service;

use Drupal\Core\Form\FormStateInterface;

class SignalwireHandler {

    public static function signalwireManagedFields(&$form, FormStateInterface $form_state){

        $signalwireFields = $form_state->getValue('enabled_messaging_fields');
        $numberFields = count($signalwireFields);

        /** @var  \Drupal\Core\Config\ConfigFactoryInterface $config */
        $config = \Drupal::service('config.factory');
        $signalwireConfig = $config->getEditable('signalwire.config');

        $noDisabledFields = 0;
        foreach ($signalwireFields as $key => $signalwireFieldValue){
            if ($signalwireFieldValue == 1) {
                $parameters = explode('#', $key);
                $signalwireConfig->set('messaging.entities.'.$parameters[0].'.'.$parameters[1].'.'.$parameters[2], 1);
            }
            else{
                $noDisabledFields = $noDisabledFields + 1;
                $parameters = explode('#', $key);
                $signalwireConfig->clear('messaging.entities.'.$parameters[0].'.'.$parameters[1].'.'.$parameters[2]);
            }
            if ($numberFields == $noDisabledFields){
                $signalwireConfig->clear('messaging.entities.'.$parameters[0].'.'.$parameters[1]);
            }
        }
        $signalwireConfig->save();
    }
}