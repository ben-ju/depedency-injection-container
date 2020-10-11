<?php


namespace App;

use Exception;
use ReflectionClass;

class Container
{
    /**
     * Store unique services called with the "register" method
     * @var array
     */
    private array $dictionary = [];

    /**
     * @var Container|null
     */
    private static ?Container $_instance = null;

    /**
     * Private constructor so you can't instantiate a new class
     */
    private function __construct()
    {
    }

    /**
     * Always return the same instance of the class | Singleton
     * @return Container|null
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Declare a service to the container with a *eager* behavior.
     * Create the object and every dependency related to the object
     * Return a new instance so the function can be declared recursively
     *
     * @param string $key
     * @return object
     */
    public function register(string $key)
    {
        $key = ucfirst($key);
//      Enclose the whole function to handle the ReflectionClass error if the class does not exist
        try {
            $reflector = new ReflectionClass($this->getFullClassName($key));
//          Verify that the class is instantiable and not already set in the dictionary of unique services
            if ($reflector->isInstantiable() && !isset($this->dictionary[$key])) {
//              Verify that the class have a constructor
                if ($reflector->getConstructor()) {
                    $constructor = $reflector->getConstructor();
//                  Verify if the class have parameters
//                  If not it instantiate the class into the dictionary
                    if ($reflector->getConstructor()->getNumberOfParameters() === 0) {
                        return $this->reflectorNewInstance($reflector, $key);
                    }
//                   Collect every parameters
                    $parameters = $constructor->getParameters();
                    $constructorParameters = [];

                    foreach ($parameters as $parameter) {
//                        Check if there is a class dependency in the constructor
                        if ($parameter->getClass()) {
                            $parameterObjectName = str_replace(
                                'Model',
                                '',
                                $parameter->getClass()->getShortName()
                            );
//                           Call the function recursively and instantiate the object into the dictionary
                            $constructorParameters[] = $this->register($parameterObjectName);
//                          Retrieve the default value
                        } elseif ($parameter->isDefaultValueAvailable()) {
                            $constructorParameters[] = $parameter->getDefaultValue();
                        } else {
                            throw new Exception("The parameter '{$parameter->getName()}' require a default value to be instantiated.");
                        }
                    }
//                    Create a new instance with the array of parameter push it in the dictionary and return the new instance
                    $newInstance = $reflector->newInstanceArgs($constructorParameters);
                    $this->dictionary[$key] = $newInstance;
                    return $newInstance;
                }
//                If the class is instantiable but doesn't have a constructor, push it in the dictionary
                return $this->reflectorNewInstance($reflector, $key);

            }
        } catch (\ReflectionException $e) {
            echo $e;
        }
    }

    public function factory(string $key)
    {
        $className = $this->getFullClassName($key);
        try {
            $reflection = new ReflectionClass($className);
            if ($reflection->isInstantiable()) {
                return function (...$args) use ($reflection) {
                    if ($reflection->getConstructor()->getNumberOfParameters() === count($args)) {
                        return $reflection->newInstanceArgs($args);
                    }
                    throw new Exception("The class " . $reflection->getName() . " expect " . $reflection->getConstructor()->getNumberOfParameters() . " parameters.");
                };
            }
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function get(string $key)
    {
        try {
            if (!isset($this->dictionary[$key])) {
                throw new Exception("The key : '{$key}' does not exist in the dictionary. You can add it into the dictionary by using the method 'register'.");
            }
        } catch (Exception $e) {
            echo $e;
        }
        return $this->dictionary[$key];
    }

    /**
     * Get a key and return
     *
     * @param string $key
     * @return string
     */
    public function getFullClassName(string $key): string
    {
        return "App\\" . ucfirst(strtolower($key));
    }

    /**
     * Utilities function to create a new instance with reflector
     *
     * @param ReflectionClass $reflectionClass
     * @param string $key
     * @return object
     */
    public function reflectorNewInstance(ReflectionClass $reflectionClass, string $key)
    {
        $instance = $reflectionClass->newInstance();
        $this->dictionary[$key] = $instance;
        return $instance;
    }
}