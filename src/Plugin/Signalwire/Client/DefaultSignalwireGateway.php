<?php

namespace Drupal\signalwire\Plugin\Signalwire\Client;

use Drupal\Core\Config\ConfigFactory;
use Drupal\signalwire\Plugin\Signalwire\SignalwirePluginBase;
use SignalWire\Rest\Client;
use Twilio\Exceptions\TwilioException;
use GuzzleHttp\Client as HttpClient;

/**
 * Defines a signalwire client plugin.
 *
 * @Signalwire(
 *   id = "default",
 *   label = @Translation("Default Gateway"),
 * )
 */
class DefaultSignalwireGateway extends SignalwirePluginBase {

    /**
     * A signalwire client.
     *
     * @var \SignalWire\Rest\Client
     */
    private $client;

    /**
     * Signalwire space url.
     *
     * @var string
     */
    private $spaceUrl;

    /**
     * Signalwire project key.
     *
     * @var string
     */
    private $projectKey;

    /**
     * Signalwire api key.
     *
     * @var string
     */
    private $apiKey;

    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory, HttpClient $httpClient)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $configFactory, $httpClient);

        $signalwireConfigs = $configFactory->get('signalwire.config');
        $this->spaceUrl = $signalwireConfigs->get('space_url');
        $this->projectKey = $signalwireConfigs->get('project_key');
        $this->apiKey = $signalwireConfigs->get('api_key');

        try{
            $this->client = new Client($this->projectKey, $this->apiKey, array(
                    "signalwireSpaceUrl" => $this->spaceUrl,
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
    public function sendMessage(string $message, string $fromNumber, string $recipientNumber, string $senderType = 'telephone') {
        try{
            $messages = $this->client->messages;
            if ($senderType == 'number_group'){
                $messageList = $messages
                    ->create($recipientNumber,
                        array("MessagingServiceSid" => $fromNumber, "body" => $message)
                    );
            }
            else{
                $messageList = $messages
                    ->create($recipientNumber,
                        array("from" => $fromNumber, "body" => $message)
                    );
            }

            return $messageList;
        }
        catch(TwilioException $exception){
            \Drupal::logger('signalwire')->notice($exception->getMessage());
            return NULL;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function numberGroups(){
        $endpoint = 'https://'.$this->spaceUrl.'/api/relay/rest/number_groups';

        try{
            $request = $this->httpClient->get($endpoint, [
                'auth' => [$this->projectKey, $this->apiKey]
            ]);
            $response = $request->getBody();


            return array('status' => $request->getStatusCode(),
                'data' => json_decode($response)->data
            );
        }
        catch (\Exception $e) {

            return array(
                'status' => $e->getMessage(),
                'data' => NULL
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function numberGroupMemberships(string $numberGroupId){
        $endpoint = 'https://'.$this->spaceUrl.'/api/relay/rest/number_groups/'.$numberGroupId.'/number_group_memberships';

        try{
            $request = $this->httpClient->get($endpoint, [
                'auth' => [$this->projectKey, $this->apiKey]
            ]);
            $response = $request->getBody();


            return array('status' => $request->getStatusCode(),
                'data' => json_decode($response)->data
            );
        }
        catch (\Exception $e) {

            return array(
                'status' => $e->getMessage(),
                'data' => NULL
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function phoneNumbers(){
        $endpoint = 'https://'.$this->spaceUrl.'/api/relay/rest/phone_numbers';

        try{
            $request = $this->httpClient->get($endpoint, [
                'auth' => [$this->projectKey, $this->apiKey]
            ]);
            $response = $request->getBody();


            return array('status' => $request->getStatusCode(),
                'data' => json_decode($response)->data
            );
        }
        catch (\Exception $e) {

            return array(
                'status' => $e->getMessage(),
                'data' => NULL
            );
        }
    }
}