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

use KM\Lang\Reflect\Method;
use KM\Lang\Reflect\Parameter;
use KM\Lang\Reflect\ReflectionUtility;

/**
 * Template to render a class method definition.
 *
 * @author Blair
 */
class MethodTemplate
{

    /**
     * The method definition template.
     *
     * @var string
     */
    private static $TEMPLATE = '
		{docComment}
		{modifiers} function {methodName}({parameterDefinitions}) {
			$args = func_get_args();
			return $invocationHandler->invoke($this, $method, $args);
		}
		';

    /**
     * The reflected method.
     *
     * @var Method
     */
    private $method;

    /**
     * Constructs an instance of this class with the given <code>Method</code>
     * object.
     *
     * @param Method $method The <code>Method</code> object representing the
     *            reflected method to generate.
     */
    public function __construct(Method $method)
    {
        $this->method = $method;
    }

    /**
     * Renders the method definition.
     *
     * @return string
     */
    public function render()
    {
        /* @var $p Parameter */
        $parameters = [];
        $parameterDefs = [];
        foreach ($this->method->getParameters() as $p) {
            $parameters = $p->asString();
            $parameterDefs = $p->asFullString();
        }
        $modifiers = ReflectionUtility::printModifiersIfNonZero($this->method->getModifiers());
        $methodName = $this->method->returnsReference() ? '&' . $this->method->getName() : $this->method->getName();
        
        $data = array(
            'docComment' => $this->method->getDocComment(),
            'modifiers' => $modifiers,
            'methodName' => $methodName,
            'method' => $this->method->getName(),
            'parameterDefinitions' => $parameterDefs,
            'parameters' => $parameters
        );
        return Template::render(self::$TEMPLATE, $data);
    }
}
?>