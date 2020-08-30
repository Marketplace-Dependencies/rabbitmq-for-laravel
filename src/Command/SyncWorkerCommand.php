<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Jurry\RabbitMQ\Handler\AmqpHandler;
use Jurry\RabbitMQ\Handler\RequestHandler;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class SyncConsumerCommand extends Command
{
    /**
     * @var AmqpHandler
     */
    private $amqpHandler;

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amqp:sync_worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Consumer Command';

    /**
     * SyncWorkerCommand constructor.
     * @param AmqpHandler $amqpHandler
     * @param RequestHandler $requestHandler
     */
    public function __construct(AmqpHandler $amqpHandler, RequestHandler $requestHandler)
    {
        $this->amqpHandler = $amqpHandler;

        $requestHandler->setClassesNamespace($amqpHandler->classesNamespace);
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
        $this->amqpHandler->declareSync();

        $channel = $this->amqpHandler->getChannel();
        $properties = $this->amqpHandler->getProperties();

        try {
            $channel->basic_qos(null, 1, null);
            $channel->basic_consume($properties['sync_queue']['name'],
                '', false, false, false, false,
                function (AMQPMessage $request) use ($channel) {
                    $payload = json_decode($request->getBody(), true);
                    /** @var AMQPChannel $amqpRequest */
                    $amqpRequest = $request->delivery_info['channel'];
                    try {

                        // handle request
                        $response = $this->requestHandler->process(
                            $payload['service'],
                            $payload['method'],
                            $payload['params']
                        );

                        // send response
                        $message = json_encode($response);

                    } catch (\Throwable $exception) {
                        // TODO: handle exception
                        $message = json_encode([
                            'hasError' => true,
                            'message' => $exception->getMessage(),
                            'code' => $exception->getCode() ?: 500
                        ]);
                    }
                    $amqpRequest->basic_ack($request->delivery_info['delivery_tag']);
                    $amqpRequest->basic_publish(new AMQPMessage($message, [
                        'correlation_id' => $request->get('correlation_id'),
                        'reply_to' => $request->get('reply_to')
                    ]), '', $request->get('reply_to'));
                }
            );

            while (count($channel->callbacks)) {
                $channel->wait();
            }

        } catch (\Throwable $exception) {
            // TODO: handle exception
        }

        return 0;
    }
}
