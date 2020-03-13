<?php

namespace Drupal\signalwire\Plugin\Signalwire\Client;

use Drupal\Core\Config\ConfigFactory;
use Drupal\encrypt\EncryptionProfileManagerInterface;
use Drupal\encrypt\EncryptServiceInterface;
use Drupal\encrypt\Exception\EncryptException;
use Drupal\encrypt\Exception\EncryptionMethodCanNotDecryptException;
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

    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory, HttpClient $httpClient, EncryptionProfileManagerInterface $encryption_profile_manager, EncryptServiceInterface $encrypt_service)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $configFactory, $httpClient, $encryption_profile_manager, $encrypt_service);

        $signalwireConfigs = $configFactory->get('signalwire.config');
        $profile = $encryption_profile_manager->getEncryptionProfile($signalwireConfigs->get('encryption'));

        try{
            if (is_null($profile)){
              //todo need to log this error
              return;
            }
          $this->spaceUrl = $this->encryptService->decrypt($signalwireConfigs->get('space_url'), $profile);
          $this->projectKey = $this->encryptService->decrypt($signalwireConfigs->get('project_key'), $profile);
          $this->apiKey = $this->encryptService->decrypt($signalwireConfigs->get('api_key'), $profile);

            $this->client = new Client($this->projectKey, $this->apiKey, array(
                    "signalwireSpaceUrl" => $this->spaceUrl,
                )
            );
        }
        catch(EncryptException $e){
          //todo need to log this exception
          //$this->loggerFactory->get('zoom_meeting')->error("{$e->getMessage()}");
        }
        catch(EncryptionMethodCanNotDecryptException $e){
          //todo need to log this exception
          //$this->loggerFactory->get('zoom_meeting')->error("{$e->getMessage()}");
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
    public function sendMessage($message, $fromNumber, $recipientNumber, $senderType = 'telephone') {
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
    public function numberGroupMemberships($numberGroupId){
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
