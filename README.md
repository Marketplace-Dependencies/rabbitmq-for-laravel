## RabbitMQ Package for Laravel & Lumen Frameworks
#### Description:

Its purpose is to initiate workers (consumers) and to send "sync" and "async" requests to another queues or exchanges.

---

#### Installation

```bash
composer require jurry/laravel-rabbitmq
```
---

#### Usage

1. Register this package into your AppServiceProvider:
    ```php
    class AppServiceProvider {
        public function register()
        {
            ...
    
            $this->app->singleton(\Jurry\RabbitMQ\Handler\AmqpHandler::class, function () {
                return new \Jurry\RabbitMQ\Handler\AmqpHandler(
                    env('JURRY_RABBITMQ_HOST'), // host
                    env('JURRY_RABBITMQ_PORT'), // port
                    env('JURRY_RABBITMQ_USERNAME'), // username
                    env('JURRY_RABBITMQ_PASSWORD'), // password
                    '\App\Services', // classesNamespace, where the consumer will look for to process the message with targeted service class
                    [
                        'sync_queue' => [ // Sync queue options, will be used when declare the queue
                            'name' => 'stores_sync',
                            'message_ttl' => 10000,
                        ],
                        'async_queue' => [ // Async queue options, will be used when declare the queue
                            'name' => 'stores_async',
                            'message_ttl' => 10000,
                        ],
                    ]
                );
            });
        }
    }
    ```
    - You can change the parameters as wish you need

2. Register your custom command by adding your created class to the $commands member inside the app/Console/Kernel.php file:
```php
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ...
        \Jurry\RabbitMQ\Command\SyncConsumerCommand::class,
        \Jurry\RabbitMQ\Command\AsyncConsumerCommand::class,
    ];

    //....

}
```

3. Start new workers:
    ```bash
    php artisan amqp:sync_worker
    php artisan amqp:async_worker
    ```