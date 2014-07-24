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
namespace Sun\Misc;

use KM\IO\File;

/**
 * URLClassPath Class
 *
 * @author Blair
 */
class URLClassPath
{

    /**
     * The original search path of URLs
     *
     * @var array
     */
    private $path = [];

    /**
     * The stack of unopened URLs
     *
     * @var array
     */
    protected $urls = [];

    /**
     * The resulting search path of loaders.
     *
     * @var array
     */
    protected $loaders = [];

    /**
     * Map of each URL opened to its corresponding loader.
     *
     * @var array
     */
    protected $lmap = [];

    public function __construct(array $urls)
    {
        foreach ($urls as $url) {
            $this->path[] = $url;
        }
        $this->push($urls);
    }

    public function addUrl($url)
    {
        foreach ($this->path as $e) {
            if ($e == $url) {
                return;
            }
        }
        
        $newUrls[] = $url;
        foreach ($this->urls as $us) {
            $newUrls[] = $us;
        }
        $this->urls = $newUrls;
        
        $this->path[] = $url;
    }

    public function getUrls()
    {
        $returnValue = [];
        foreach ($this->path as $url) {
            $returnValue[] = $url;
        }
        return $returnValue;
    }

    public static function pathToUrls($path)
    {
        $st = explode(PATH_SEPARATOR, $path);
        $urls = [];
        foreach ($st as $token) {
            $f = new File($token);
            $urls[] = $f->getPath();
        }
        return $urls;
    }

    private function push(array $us)
    {
        for ($i = count($us); $i >= 0; $i --) {
            $this->urls[] = $us[$i];
        }
    }
}
?>