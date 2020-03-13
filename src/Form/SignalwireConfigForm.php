<?php
namespace Drupal\signalwire\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\encrypt\EncryptionProfileInterface;
use Drupal\encrypt\EncryptService;
use Drupal\encrypt\Entity\EncryptionProfile;
use Drupal\encrypt\Exception\EncryptException;
use Drupal\encrypt\Exception\EncryptionMethodCanNotDecryptException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\encrypt\EncryptionProfileManagerInterface;
use Drupal\encrypt\EncryptServiceInterface;

class SignalwireConfigForm extends ConfigFormBase {

  /**
   * Encryption profile.
   *
   * @var EncryptionProfileManagerInterface
   */
  protected $encryptionProfileManager;

  /**
   * Encryption service.
   *
   * @var EncryptService
   */
  protected $encryptService;

  /**
   * The EncryptionProfile entity.
   *
   * @var EncryptionProfileInterface
   */
  protected $profile;

  public function __construct(ConfigFactoryInterface $config_factory, EncryptionProfileManagerInterface $encryption_profile_manager, EncryptServiceInterface $encrypt_service) {
    parent::__construct($config_factory);
    $this->encryptionProfileManager = $encryption_profile_manager;
    $this->encryptService = $encrypt_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('encrypt.encryption_profile.manager'),
      $container->get('encryption')
    );
  }

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

        $clientOptions['none'] = $this->t('Turn off outgoing messaging.');

        foreach ($plugin_definitions as $key => $plugin_definition){
            $clientOptions[$plugin_definition['id']] = $plugin_definition['label'];
        }

      // Fetching all available encryption profiles.
      $encryption_profiles = $this->encryptionProfileManager->getAllEncryptionProfiles();
      $encryption = $config->get('encryption');

      if ($encryption){
        $this->profile = $this->encryptionProfileManager->getEncryptionProfile($encryption);
      }

      $profiles = array();
      /**
       * @var  $encryption_profile_id
       * @var  EncryptionProfile $encryptionProfile
       */
      foreach ($encryption_profiles as $encryption_profile_id => $encryptionProfile ){
        $profiles[$encryption_profile_id] = $encryptionProfile->label();
      }

        $form['signalwire'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('General Signalwire Settings'),
        );
      $form['signalwire']['encryption'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Select encryption profile to use.'),
        '#default_value' => $encryption,
        '#options' => $profiles,
        '#required' => TRUE,
        '#description' => $this->t("The encryption profile to use for encryption and decryption.")
      );

      try{
        $form['signalwire']['space_url'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Signalwire Space URL'),
          '#default_value' => is_null($this->profile) ? '' : $this->encryptService->decrypt($config->get('space_url'), $this->profile),
          '#required' => TRUE,
          '#description' => $this->t("Custom URL (your-space.signalwire.com), replace your-space with unique subdomain.")
        );
        //switch from using plain textfield to password to hide values from someone looking over the shoulder.
        $form['signalwire']['api_key'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Signalwire api key'),
          '#default_value' => is_null($this->profile) ? '' : $this->encryptService->decrypt($config->get('api_key'), $this->profile),
          '#required' => TRUE,
          '#description' => $this->t("Token associated with unique subdomain.")
        );
        $form['signalwire']['project_key'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Signalwire project key'),
          '#default_value' => is_null($this->profile) ? '' : $this->encryptService->decrypt($config->get('project_key'), $this->profile),
          '#required' => TRUE,
          '#description' => $this->t("Project ID associated with unique subdomain.")
        );
      }
      catch(EncryptException $e){
        $this->logger('signalwire')->error("Settings form creation: {$e->getMessage()}");
      }
      catch (EncryptionMethodCanNotDecryptException $e) {
        $this->logger('signalwire')->error("Settings form creation: {$e->getMessage()}");
      }

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
            '#description' => $this->t("The number in E.164 format minus the dashes e.g. +1XXX-XXX-XXX"),
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
            '#description' => $this->t("The number in E.164 format minus the dashes e.g. +1XXX-XXX-XXX")
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
            $testMsg = $instance->sendMessage('Drupal signalwire test message!.', $form_state->getValue('from'), $form_state->getValue('to'), $form_state->getValue('sender_type'));

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
        try{
          $this->profile = $this->encryptionProfileManager->getEncryptionProfile($form_state->getValue('encryption'));
          $this->config('signalwire.config')
            ->set('encryption', $form_state->getValue('encryption'))
            ->set('space_url', $this->encryptService->encrypt(trim(str_replace(' ', '',$form_state->getValue('space_url'))), $this->profile))
            ->set('api_key', $this->encryptService->encrypt(trim(str_replace(' ', '',$form_state->getValue('api_key'))), $this->profile))
            ->set('project_key', $this->encryptService->encrypt(trim(str_replace(' ', '',$form_state->getValue('project_key'))), $this->profile))
            ->set('client', $form_state->getValue('client'))
            ->set('sender_type', $form_state->getValue('sender_type'))
            ->set('sender',$sender)
            ->save();

        }
        catch(EncryptException $e){
          $this->logger('signalwire')->error("Settings form submission: {$e->getMessage()}");
        }
    }
    /*public function sender(array $form, FormStateInterface $form_state) {
        //$ajaxResponse = new AjaxResponse();
        $form['signalwire_sender']['from']['#required'] = FALSE;
        \Drupal::logger('signalwire')->notice(json_encode('CHange to FALSE', 1));
        //return $ajaxResponse;

        return $form['signalwire_sender']['from'];
    }*/
}
