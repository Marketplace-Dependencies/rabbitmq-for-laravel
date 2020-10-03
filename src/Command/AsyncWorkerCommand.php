<?php

namespace Jurry\RabbitMQ\Command;

use Illuminate\Console\Command;
use Jurry\RabbitMQ\Handler\AmqpHandler;
use Jurry\RabbitMQ\Handler\RequestHandler;
use PhpAmqpLib\Message\AMQPMessage;

class AsyncConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amqp:async_worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Async Consumer Command';

    /**
     * @var AmqpHandler
     */
    private $amqpHandler;

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * AsyncWorkerCommand constructor.
     * @param AmqpHandler $amqpHandler
     * @param RequestHandler $requestHandler
     */
    public function __construct(AmqpHandler $amqpHandler, RequestHandler $requestHandler)
    {
        $this->amqpHandler = $amqpHandler;
        $this->requestHandler = $requestHandler;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->amqpHandler->declareAsync();

        $channel = $this->amqpHandler->getChannel();
        $properties = $this->amqpHandler->getProperties();

        try {
            $channel->basic_qos(null, 1, null);
            $channel->basic_consume($properties['async_queue']['name'], '', false, true, false, false,
                function (AMQPMessage $message) {
                    try {
                        $payload = json_decode($message->getBody(), true);
                        $this->requestHandler->process(
                            $payload['route'],
                            $payload['method'],
                            $payload['headers'],
                            $payload['query'],
                            $payload['body']
                        );
                    } catch (\Throwable $exception) {
                        // TODO: handle exception message
                        throw $exception;
                    }
                }
            );

            while (count($channel->callbacks)) {
                $channel->wait();
            }

        } catch (\Throwable $exception) {
            // TODO: handle exception
            throw $exception;
        }

        return 0;
    }
}
