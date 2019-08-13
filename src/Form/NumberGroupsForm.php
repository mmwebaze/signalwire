<?php

namespace Drupal\signalwire\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;

/**
 * Provides a form for displaying Number Groups. Number Groups allow you to build collections of Phone Numbers to be
 * used for things like outbound message pools, white/black-lists, and much more.
 */
class NumberGroupsForm extends FormBase {

    /**
     * The signalwire configured gateway.
     *
     * @var string
     */
    protected $gateway;

    /**
     * Default signalwire plugin.
     *
     * @var
     */
    protected $signalwirePluginManager;

    public function __construct(ConfigFactory $configFactory, $signalwirePluginManager) {
        $config = $configFactory->get('signalwire.config');
        $this->gateway = $config->get('client');
        $this->signalwirePluginManager = $signalwirePluginManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'signalwire_number_groups_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $parameter = NULL) {

        $instance = $this->signalwirePluginManager->createInstance($this->gateway);
        $numberGroups = $instance->numberGroups();
        $numberGroupMemberships = $instance->numberGroupMemberships('782f5bf8-e424-477d-89e1-a975690fdeff');
        $phoneNumbers = $instance->phoneNumbers();

       /* $header = array(
            'name' => $this->t('Name'),
            'phone_number_count' => $this->t('Phone Number Count')
        );*/

        //$rows = array();
        $groups = array();
        foreach ($numberGroups['data'] as  $numberGroup) {
            $groups[$numberGroup->id] = $numberGroup->name.' ('.$numberGroup->phone_number_count.')';
            /*$rows[$numberGroup->id] = array(
                'name' => $numberGroup->name,
                'phone_number_count' => $numberGroup->phone_number_count
            );*/
        }
        /*$form['table'] = array(
            '#theme' => 'table',
            '#header' => $header,
            '#rows' => $rows,
            '#empty' => t('No number groups found'),
        );*/
        $form['number_groups'] = array(
            '#type' => 'checkboxes',
            '#title' => $this->t('Number groups to import'),
            '#options' => $groups,
            '#required' => TRUE,
        );

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#button_type' => 'primary',
            '#value' => $this->t('Import Number Groups'),
        );


        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        print_r($form_state->getValue('number_groups'));die;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('config.factory'),
            $container->get('plugin.manager.signalwire_manager')
        );
    }
}