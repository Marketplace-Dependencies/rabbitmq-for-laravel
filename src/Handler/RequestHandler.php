<?php
/**
 * User: Wajdi Jurry
 * Date: 22/02/19
 * Time: 04:40 م
 */

namespace Jurry\RabbitMQ\Handler;


class RequestHandler
{
    /**
     * @var string
     */
    private $classesNamespace;

    /**
     * @param string|null $classesNamespace
     */
    public function setClassesNamespace(?string $classesNamespace = null)
    {
        if (!empty($classesNamespace) && strpos($classesNamespace, '\\') !== 0) {
            $classesNamespace = '\\' . $classesNamespace;
        }

        $this->classesNamespace = rtrim($classesNamespace, '\\');
    }

    /**
     * @param string $service
     * @return string
     */
    private function formatServiceName(string $service): string
    {
        return ucfirst($service);
    }

    /**
     * @param string $service
     * @param string $method
     * @param $params
     * @return mixed
     *
     * @throws \Exception
     */
    public function process(string $service, string $method, $params)
    {
        $className = $this->classesNamespace . '\\' . $this->formatServiceName($service);

        if (!($service = class_exists($className))) {
            throw new \Exception("Class \"{$className}\" does not exists", 404);
        }

        if (!is_callable([$className, $method])) {
            throw new \Exception('Method "' . get_class($service) . '::' . $method . '" is not a callable method', 400);
        }

        $class = app($className);

        return call_user_func_array([$class, $method], $params);
    }
}
