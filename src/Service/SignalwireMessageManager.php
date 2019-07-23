<?php

namespace Drupal\signalwire\Service;

use Drupal\Core\Database\Driver\mysql\Connection;

class SignalwireMessageManager implements SignalwireMessageInterface {

    /**
     * Drupal\Core\Database\Driver\mysql\Connection definition.
     *
     * @var \Drupal\Core\Database\Driver\mysql\Connection
     */
    protected $connection;

    /**
     * SignalwireMessageManager constructor.
     *
     * @param \Drupal\Core\Database\Driver\mysql\Connection $connection
     *   The connection service.
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
    public function saveMessage(array $values) {

        try{
            if (isset($values)) {
                $value = array(
                    'node' => $values['node'],
                    'message' => $values['message'],
                    'from' => $values['from'],
                    'recipients' => $values['recipients'],
                    'frequency' => $values['frequency'],
                    'date_sent' => $values['date_sent'],
                    'date_next_send' => $values['date_next_send']
                );
                $this->connection->insert('signalware_messages')->fields(['node', 'message', 'from', 'recipients', 'frequency', 'date_sent', 'date_next_send'])
                    ->values($value)->execute();
            }

        }
        catch (\Exception $e) {
            //@todo replace by logging and redirect to error page
            print_r($e->getMessage());
        }
    }
    public function getMessage() {

    }
}