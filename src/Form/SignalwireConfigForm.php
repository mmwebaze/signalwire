<?php
namespace Drupal\signalwire\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;

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
        $form['signalwire_sender'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('Sender Settings'),
        );

        $senderType = $config->get('sender_type');

        $form['signalwire_sender']['sender_type'] = array(
            '#type' => 'radios',
            /*'#prefix' => '<div class="signalwire_sender_type">',
            '#suffix' => '</div>',*/
            '#title' => $this->t('Sender settings'),
            '#options' => array(
                //'none' => $this->t('Turn off'),
                'telephone' => $this->t('Use a specific number'),
                'number_group' => $this->t('Use a specific number group')
            ),
            '#default_value' => isset($senderType) ? $senderType : 'telephone',
            '#required' => TRUE,
            /*'#ajax' => array(
                'callback' => '::sender',
                'progress' => array(
                    'type' => 'throbber',
                    'message' => 'processing...',
                ),
            ),*/
        );
        $senderType = ($senderType == 'none'? 'hide_from_number': 'unhide_from_number');
        $form['signalwire_sender']['from'] = array(
            '#prefix' => '<div id="signalwire_from" class="'.$senderType.'">',
            '#suffix' => '</div>',
            '#type' => 'textfield',
            '#title' => $this->t('Sender'),
            '#default_value' => $config->get('sender'),
            /*'#attributes' => array(
                'class' => array($senderType == 'none'? 'hide_from_number': 'unhide_from_number')
            ),*/
            '#required' => TRUE,
        );

        $form['signalwire_test'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('Test connection'),
        );

        $form['signalwire_test']['to'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('To'),
        );

        $form['signalwire_test']['test'] = array(
            '#type' => 'submit',
            '#value' => t('Test call'),
            '#description' => $this->t('Run test'),
            '#ajax' => array(
                'wrapper' => 'edit-from',
                'callback' => '::runTest',
                'event' => 'click',
                'progress' => array(
                    'type' => 'throbber',
                    'message' => 'Running test...',
                ),
            )
        );
        /*$form['#attached']['library'][] = 'signalwire/signalwire.settings';
        $form['#attached']['drupalSettings']['signalwire']['setting']['sender_type'] = $senderType;*/
        return parent::buildForm($form, $form_state);
    }

    /**
     * Sends a test sms message.
     *
     * @param array $form
     * @param FormStateInterface $form_state
     * @return AjaxResponse
     */
    public function runTest(array &$form, FormStateInterface $form_state) {

        $ajaxResponse = new AjaxResponse();

        $client = $form_state->getValue('client');
        if ($client != 'none'){
            $plugin_manager = \Drupal::service('plugin.manager.signalwire_manager');

            $instance = $plugin_manager->createInstance($client);
            $testMsg = $instance->sendMessage('Drupal signalwire test message.', $form_state->getValue('from'), $form_state->getValue('to'), $form_state->getValue('sender_type'));

            \Drupal::logger('signalwire')->notice($testMsg);
        }
        else{
            \Drupal::logger('signalwire')->notice('No gateway or sender has been selected..');
        }

        return $ajaxResponse;
    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $sender = $form_state->getValue('from');
        /*if ($form_state->getValue('sender_type') == 'none'){
            $sender = NULL;
        }*/

        $this->config('signalwire.config')
            ->set('space_url', $form_state->getValue('space_url'))
            ->set('api_key', $form_state->getValue('api_key'))
            ->set('project_key', $form_state->getValue('project_key'))
            ->set('client', $form_state->getValue('client'))
            ->set('sender_type', $form_state->getValue('sender_type'))
            ->set('sender',$sender)
            ->save();
    }
    /*public function sender(array $form, FormStateInterface $form_state) {
        //$ajaxResponse = new AjaxResponse();
        $form['signalwire_sender']['from']['#required'] = FALSE;
        \Drupal::logger('signalwire')->notice(json_encode('CHange to FALSE', 1));
        //return $ajaxResponse;

        return $form['signalwire_sender']['from'];
    }*/
}