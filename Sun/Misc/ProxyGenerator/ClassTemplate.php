<?php
/**
 * Copyright (c) 2009-2014 Kaaterskil Management, LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Sun\Misc\ProxyGenerator;

use KM\Lang\Clazz;
use KM\Lang\Reflect\Method;
use KM\Lang\Reflect\Parameter;
use KM\Lang\Reflect\Proxy\ProxyClassFactory;

/**
 * ClassTemplate Class
 *
 * @author Blair
 */
class ClassTemplate
{

    /**
     * The class definition template.
     *
     * @var string
     */
    private static $TEMPLATE = '
		{namespace}
		
		{uses}
		
		final class {proxyClassName} extends {className} implements {interfaces} {
			/**
			 * The proxy class invocation handler. It should contain a map of methods and their
			 * individual invocation handlers mapped by method name to be called by each method.
			 * @var \KM\Lang\Reflect\InvocationHandler
			 */
			private $invocationHandler;
			
			/**
			 * Constructs an instance of this proxy class with the given configuration.
             *
			 * @param \KM\Reflect\InvocationHandler $invocationHandler A map of
			 * <code>MethodAndHandler</code> objects containing reflected methods and their
			 * invocation handlers mapped by method name.
			 */
			public function __construct(\KM\Lang\Reflect\InvocationHandler $invocationHandler) {
				$this->invocationHandler = $invocationHandler;
			}
		
			{methods}
		}
		';

    /**
     * The reflection class.
     *
     * @var Clazz
     */
    private $clazz;

    private $uses = [];

    /**
     * Constructs a new ClassTemplate with the given <code>Clazz</code> object.
     *
     * @param Clazz $clazz
     */
    public function __construct(Clazz $superClazz)
    {
        $this->clazz = $superClazz;
    }

    public function render($proxyClassName, array $interfaces, array $methods)
    {
        /* @var $m Method */
        $this->generateInterfaceUses($interfaces);
        
        $generatedMethods = [];
        foreach ($methods as $m) {
            $mt = new MethodTemplate($m);
            $generatedMethods[] = $mt->render();
            $this->generateMethodUses($m);
        }
        
        sort($this->uses);
        
        $date = array(
            'namespace' => ProxyClassFactory::PROXY_PACKAGE . ';',
            'uses' => implode("\n", $this->uses),
            'proxyClassName' => $proxyClassName,
            'className' => $this->clazz->getShortName(),
            'interfaces' => $this->generateInterfaces($interfaces),
            'methods' => implode("\n\n", $generatedMethods)
        );
        return Template::render(self::$TEMPLATE, $data);
    }

    private function generateInterfaceUses(array $interfaces)
    {
        /* @var $clazz Clazz */
        foreach ($interfaces as $clazz) {
            $name = $clazz->getName();
            if (! in_array($name, $this->uses)) {
                $this->uses[$name] = 'use ' . $name . ';';
            }
        }
    }

    private function generateMethodUses(Method $method)
    {
        /* @var $p Parameter */
        foreach ($method->getParameters() as $p) {
            $type = $p->getType();
            if ($type instanceof Clazz) {
                $name = $type->getTypeName();
                if (! in_array($name, $this->uses)) {
                    $this->uses[$name] = 'use ' . $name . ';';
                }
            }
        }
    }

    private function generateInterfaces(array $interfaces)
    {
        /* @var $clazz Clazz */
        $sb = '';
        $size = count($interfaces);
        for ($i = 0; $i < $size; $i ++) {
            $clazz = $interfaces[$i];
            $sb .= '\\' . $clazz->getName();
            if ($i < $size - 1) {
                $sb .= ', ';
            }
        }
        return $sb;
    }
}
?>