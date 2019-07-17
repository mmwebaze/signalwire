<?php
namespace Drupal\signalwire\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use SignalWire\Rest\Client;
use Drupal\Core\Ajax\AjaxResponse;
use Twilio\Exceptions\TwilioException;

class SignalwireConfigForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return array(
            'signalwire.config',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'signalwire_config_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('signalwire.config');
        $plugin_manager = \Drupal::service('plugin.manager.signalwire_manager');
        $plugin_definitions = $plugin_manager->getDefinitions();

        $clientOptions['none'] = $this->t('None');

        foreach ($plugin_definitions as $key => $plugin_definition){
            $clientOptions[$plugin_definition['id']] = $plugin_definition['label'];
        }

        $form['signalwire'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('General Signalwire Settings'),
        );
        $form['signalwire']['space_url'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Signalwire Space URL'),
            '#default_value' => $config->get('space_url'),
            '#required' => TRUE,
        );
        //switch from using plain textfield to password to hide values from someone looking over the shoulder.
        $form['signalwire']['api_key'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Signalwire api key'),
            '#default_value' => $config->get('api_key'),
            '#required' => TRUE,
        );
        $form['signalwire']['project_key'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Signalwire project key'),
            '#default_value' => $config->get('project_key'),
            '#required' => TRUE,
        );

        $client = $config->get('client');

        $form['signalwire']['client'] = array(
            '#type' => 'radios',
            '#title' => $this->t('Signalwire Gateways'),
            '#options' => $clientOptions,
            '#default_value' => isset($client) ? $client : 'none',
            '#required' => TRUE,
        );

        $form['signalwire_test'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('Test connection'),
        );
        $form['signalwire_test']['from'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('From'),
            //'#default_value' => $config->get('api_key'),
            '#required' => TRUE,
        );
        $form['signalwire_test']['to'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('To'),
            //'#default_value' => $config->get('api_key'),
            '#required' => TRUE,
        );

        $form['signalwire_test']['test'] = array(
            '#type' => 'submit',
            '#value' => t('Test call'),
            '#description' => $this->t('Run test'),
            '#ajax' => array(
                'callback' => '::runTest',
                'event' => 'click',
                'progress' => array(
                    'type' => 'throbber',
                    'message' => 'Running test...',
                ),
            )
        );
        return parent::buildForm($form, $form_state);
    }
    public function runTest(array &$form, FormStateInterface $form_state) {

        $ajaxResponse = new AjaxResponse();

        $client = $form_state->getValue('client');
        if ($client != 'none'){
            $plugin_manager = \Drupal::service('plugin.manager.signalwire_manager');

            $instance = $plugin_manager->createInstance($client);
            $testMsg = $instance->sendMessage('This is send by plugin', $form_state->getValue('from'), $form_state->getValue('to'));

            \Drupal::logger('signalwire')->notice($testMsg);
        }
        else{
            \Drupal::logger('signalwire')->notice('No client has been selected..');
        }


        return $ajaxResponse;
    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $this->config('signalwire.config')
            ->set('space_url', $form_state->getValue('space_url'))
            ->set('api_key', $form_state->getValue('api_key'))
            ->set('project_key', $form_state->getValue('project_key'))
            ->set('client', $form_state->getValue('client'))
            ->save();
    }
}