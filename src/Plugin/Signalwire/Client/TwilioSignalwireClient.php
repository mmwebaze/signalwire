<?php

namespace Drupal\signalwire\Plugin\Signalwire\Client;

use Drupal\Core\Config\ConfigFactory;
use Drupal\signalwire\Plugin\Signalwire\SignalwirePluginBase;
use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberList;
use Twilio\Rest\Api\V2010\Account\MessageList;
use SignalWire\Rest\Client;
use Twilio\Exceptions\TwilioException;

/**
 * Defines a signalwire client plugin.
 *
 * @Signalwire(
 *   id = "twilio",
 *   label = @Translation("Twilio"),
 *   httpClient = "twilio",
 *   usesEnvVariables = FALSE
 * )
 */
class TwilioSignalwireClient extends SignalwirePluginBase {

    private $client;

    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $configFactory);

        $signalwireConfigs = $configFactory->get('signalwire.config');

        try{
            $this->client = new Client($signalwireConfigs->get('project_key'), $signalwireConfigs->get('api_key'), array(
                    "signalwireSpaceUrl" => $signalwireConfigs->get('space_url'),
                )
            );
        }
        catch(TwilioException $exception){
            \Drupal::logger('signalwire')->notice($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function incomingPhoneNumbers() {
        $incomingPhoneNumberList = $this->client->incomingPhoneNumbers
            ->read();

        return $incomingPhoneNumberList;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage(string $message, string $recipientNumber) {
        try{
            $messageList = $this->client->messages
                ->create($recipientNumber, // to
                    array("from" => "+$$$$$$$", "body" => $message)
                );

            return $messageList;
        }
        catch(TwilioException $exception){
            \Drupal::logger('signalwire')->notice($exception->getMessage());
            return NULL;
        }
    }
}