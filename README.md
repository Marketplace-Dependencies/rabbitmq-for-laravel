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
                    env('JURRY_RABBITMQ_HOST'),
                    env('JURRY_RABBITMQ_PORT'),
                    env('JURRY_RABBITMQ_USERNAME'),
                    env('JURRY_RABBITMQ_PASSWORD'),
                    '\App\Services',
                    [
                        'sync_queue' => [
                            'name' => 'stores_sync',
                            'message_ttl' => 10000,
                        ],
                        'async_queue' => [
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

2. Start new workers:
    ```bash
    php artisan amqp:sync_worker
    php artisan amqp:async_worker
    ```