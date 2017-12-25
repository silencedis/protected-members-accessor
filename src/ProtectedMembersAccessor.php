<?php

namespace SilenceDis\ProtectedMembersAccessor;

use SilenceDis\ProtectedMembersAccessor\Exception\ProtectedMembersAccessException;

/**
 * Class ProtectedMembersAccessor
 *
 * @author Yurii Slobodeniuk <silencedis@gmail.com>
 */
class ProtectedMembersAccessor
{
    private $methodReflectors = [];
    private $propertyReflectors = [];

    /**
     * Returns a method reflection
     *
     * @param string $class
     * @param string $method
     *
     * @return \ReflectionMethod
     */
    protected function getReflectionMethod($class, $method)
    {
        if (!isset($this->methodReflectors[$class])) {
            $this->methodReflectors[$class] = [];
        }
        if (!isset($this->methodReflectors[$class][$method])) {
            $this->methodReflectors[$class][$method] = new \ReflectionMethod($class, $method);
        }

        return $this->methodReflectors[$class][$method];
    }

    /**
     * Returns a property reflection
     *
     * @param string $class
     * @param string $property
     *
     * @return \ReflectionProperty
     */
    protected function getReflectionProperty($class, $property)
    {
        if (!isset($this->propertyReflectors[$class])) {
            $this->propertyReflectors[$class] = [];
        }
        if (!isset($this->propertyReflectors[$class][$property])) {
            $this->propertyReflectors[$class][$property] = new \ReflectionProperty($class, $property);
        }

        return $this->propertyReflectors[$class][$property];
    }

    /**
     * Checks whether $object is object
     *
     * @param object $object
     * @throws ProtectedMembersAccessException
     */
    protected function checkOnObject($object)
    {
        if (!is_object($object)) {
            throw new ProtectedMembersAccessException('The parameter "object must be an object"');
        }
    }

    /**
     * Checks whether $name is string
     *
     * @param string $name
     * @throws ProtectedMembersAccessException
     */
    protected function checkOnMemberName($name)
    {
        if (!is_string($name)) {
            throw new ProtectedMembersAccessException('The parameter "name" must be a string');
        }
    }

    /**
     * Returns a method as closure
     *
     * @param array ...$params
     *
     * @return \Closure
     * @throws ProtectedMembersAccessException
     */
    public function getProtectedMethod(...$params)
    {
        if (is_string($params[0])) {
            list($className, $object, $name) = $params;
        } elseif (is_object($params[0])) {
            list($object, $name) = $params;
            $className = get_class($object);
        }

        /** @var string $className */
        /** @var object $object */
        /** @var string $name */


        $this->checkOnObject($object);
        $this->checkOnMemberName($name);

        $reflector = $this->getReflectionMethod($className, $name);

        return $reflector->getClosure($object);
    }

    /**
     * Returns a value of a protected property of the object
     *
     * @param array ...$params
     *
     * @return mixed
     * @throws ProtectedMembersAccessException
     */
    public function getProtectedProperty(...$params)
    {
        if (is_string($params[0])) {
            list($className, $object, $name) = $params;
        } elseif (is_object($params[0])) {
            list($object, $name) = $params;
            $className = get_class($object);
        }

        /** @var string $className */
        /** @var object $object */
        /** @var string $name */

        $this->checkOnObject($object);
        $this->checkOnMemberName($name);

        $reflector = new \ReflectionProperty($className, $name);
        $isProtected = $reflector->isProtected() || $reflector->isPrivate();

        if ($isProtected) {
            $reflector->setAccessible(true);
        }
        $value = $reflector->getValue($object);
        if ($isProtected) {
            $reflector->setAccessible(false);
        }

        return $value;
    }

    /**
     * Sets a protected property for the object
     *
     * @param array ...$params
     * @throws ProtectedMembersAccessException
     */
    public function setProtectedProperty(...$params)
    {
        if (is_string($params[0])) {
            list($className, $object, $name, $value) = $params;
        } elseif (is_object($params[0])) {
            list($object, $name, $value) = $params;
            $className = get_class($object);
        }

        /** @var string $className */
        /** @var object $object */
        /** @var string $name */
        /** @var mixed $value */

        $this->checkOnObject($object);
        $this->checkOnMemberName($name);

        $reflector = $this->getReflectionProperty($className, $name);
        $isProtected = $reflector->isProtected() || $reflector->isPrivate();

        if ($isProtected) {
            $reflector->setAccessible(true);
        }
        $reflector->setValue($object, $value);
        if ($isProtected) {
            $reflector->setAccessible(false);
        }
    }
}
