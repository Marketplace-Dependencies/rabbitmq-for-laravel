<?php
/**
 * User: Wajdi Jurry
 * Date: 22 May 2020
 * Time: 03:58 PM
 */

namespace Jurry\RabbitMQ\Handler;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class AmqpHandler
{
    /** @var AMQPChannel */
    private $channel;

    /** @var array */
    private $queuesProperties;

    /** @var string */
    public $classesNamespace;

    /**
     * QueuesHandler constructor.
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $password
     * @param string|null $classesNamespace
     * @param array $queuesProperties
     */
    public function __construct(
        string $host = 'localhost',
        int $port = 5672,
        string $user = 'guest',
        string $password = 'guest',
        ?string $classesNamespace = null,
        array $queuesProperties = []
    )
    {
        $connection = new AMQPStreamConnection($host, $port, $user, $password);
        $this->channel = $connection->channel();
        $this->queuesProperties = $queuesProperties;
        $this->classesNamespace = $classesNamespace;
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->queuesProperties;
    }

    public function declareSync()
    {
        $defaultOptions = [
            'passive' => false,
            'durable' => false,
            'exclusive' => false,
            'auto_delete' => false,
            'no_wait' => false,
        ];

        $options = array_merge($defaultOptions, $this->queuesProperties['sync_queue']);

        $this->channel->queue_declare($options['name'],
            $options['passive'], $options['durable'], $options['exclusive'], $options['auto_delete'], $options['no_wait'],
            new AMQPTable(['x-message-ttl' => $options['message_ttl']])
        );
    }

    public function declareAsync()
    {
        $defaultOptions = [
            'passive' => false,
            'durable' => false,
            'exclusive' => false,
            'auto_delete' => false,
            'no_wait' => false,
        ];

        $options = array_merge($defaultOptions, $this->queuesProperties['async_queue']);

        $this->channel->queue_declare($options['name'],
            $options['passive'], $options['durable'], $options['exclusive'], $options['auto_delete'], $options['no_wait'],
            new AMQPTable(['x-message-ttl' => $options['message_ttl']])
        );
    }
}