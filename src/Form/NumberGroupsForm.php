<?php

namespace Drupal\signalwire\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;

/**
 * Provides a form for displaying Number Groups.
 */
class NumberGroupsForm extends FormBase {

    protected $gateway;
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

        $header = array(
            'name' => $this->t('Name'),
            'phone_number_count' => $this->t('Phone Number Count')
        );

        $rows = array();

        foreach ($numberGroups['data'] as  $numberGroup) {
            $rows[$numberGroup->id] = array(
                'name' => $numberGroup->name,
                'phone_number_count' => $numberGroup->phone_number_count
            );
        }

        $form['table'] = array(
            '#theme' => 'table',
            '#header' => $header,
            '#rows' => $rows,
            '#empty' => t('No number groups found'),
        );

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

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