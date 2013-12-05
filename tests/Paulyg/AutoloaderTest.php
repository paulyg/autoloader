<?php
/**
 * Test cases for Paulyg/Autoloader.
 *
 * Copyright 2012-2013 Paul Garvin <paul@paulgarvin.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */ 
namespace Paulyg;

/**
 * @author Paul Garvin <paul@paulgarvin.net>
 * @copyright Copyright 2012-2013 Paul Garvin.
 * @license MIT License
 */
class AutoloaderTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Cases to test:

     * 14. PSR-0, NS, prepend
     * 15. PSR-0, PEAR, prepend
     * 16. PSR-4, prepend
     * 17. static vs dynamic
     */

    /*
     * 1. PSR-0, plain namespace, no trailing dir slash
     */
    function testPsr0()
    {
        $loader = new Autoloader();
        $loader->addPsr0('Foo', __DIR__ . '/../data');

        $bar = new \Foo\Bar();
        $this->assertInstanceOf('Foo\\Bar', $bar);

        $loader->remove('Foo', __DIR__ . '/../data');
    }

    /*
     * 2. PSR-0, leading & trailing NS, no trailing dir slash
     */
    function testPsr0LeadingAndTrailingNsSep()
    {
        Autoloader::addPsr0('\\Foo\\', './data');

        $bar = new \Foo\Bar();
        $this->assertInstanceOf('Foo\\Bar', $bar);

        Autoloader::remove('\\Foo\\', './data');
    }

    /*
     * 3. PSR-0, trailing NS, no trailing dir slash
     */
    function testPsr0TrailingNsSep()
    {
        $loader = new Autoloader();
        $loader->addPsr0('Foo\\', './data');

        $bar = new \Foo\Bar();
        $this->assertInstanceOf('Foo\\Bar', $bar);

        $loader->remove('Foo\\', './data');
    }

    /*
     * 4. PSR-0, plain namespace, trailing dir slash
     */
    function testPsr0TrailingDirSep()
    {
        Autoloader::addPsr0('Foo', './data/');

        $bar = new \Foo\Bar();
        $this->assertInstanceOf('Foo\\Bar', $bar);

        Autoloader::remove('Foo', './data/');
    }

    /*
     * 5. PSR-0, plain PEAR style, no trailing dir slash
     */
    function testPearStyle()
    {
        $loader = new Autoloader();
        $loader->addPsr0('Bar', './data');

        $baz = new \Bar_Baz();
        $this->assertInstanceOf('Bar_Baz', $baz);

        $loader->remove('Bar', './data/');
    }

    /*
     * 6. PSR-0, PEAR style w/ underscore, no trailing dir slash
     */
    function testPearStyleTrailingUnderscore()
    {
        Autoloader::addPsr0('Bar_', __DIR__ . '/../data');

        $baz = new \Bar_Baz();
        $this->assertInstanceOf('Bar_Baz', $baz);

        Autoloader::remove('Bar_', __DIR__ . '/../data');
    }

    /*
     * 7. PSR-0, plain PEAR style, trailing dir slash
     */
    function testPearStyleTrailingDirSep()
    {
        $loader = new Autoloader();
        $loader->addPsr0('Bar', './data/');

        $baz = new \Bar_Baz();
        $this->assertInstanceOf('Bar_Baz', $baz);

        $loader->remove('Bar', './data/');
    }

    /*
     * 8. PSR-4, plain NS, no trailing dir slash
     */
    function testPsr4()
    {
        Autoloader::addPsr4('Baz\\Bar', './data/Baz/src');

        $foo = new \Baz\Bar\Buzz\Foo();
        $this->assertInstanceOf('Baz\\Bar\\Buzz\\Foo', $foo);

        Autoloader::remove('Baz\\Bar', './data/Baz/src');
    }

    /*
     * 9. PSR-4, trailing NS, no trailing dir slash
     */
    function testPsr4TrailingNsSep()
    {
        $loader = new Autoloader();
        $loader->addPsr4('Baz\\Bar\\', './data/Baz/src');

        $foo = new \Baz\Bar\Buzz\Foo();
        $this->assertInstanceOf('Baz\\Bar\\Buzz\\Foo', $foo);

        $loader->remove('Baz\\Bar\\', './data/Baz/src');
    }

    /*
     * 10. PSR-4, leading and trailing NS, no trailing dir slash
     */
    function testPsr4LeadingAndTrailingNsSep()
    {
        Autoloader::addPsr4('\\Baz\\Bar\\', './data/Baz/src');

        $foo = new \Baz\Bar\Buzz\Foo();
        $this->assertInstanceOf('Baz\\Bar\\Buzz\\Foo', $foo);

        Autoloader::remove('\\Baz\\Bar\\', './data/Baz/src');
    }

    /*
     * 11. PSR-4, plain NS, trailing dir slash
     */
    function testPsr4NamespaceTrailingDirSep()
    {
        $loader = new Autoloader();
        $loader->addPsr4('Baz\\Bar', './data/Baz/src/');

        $foo = new \Baz\Bar\Buzz\Foo();
        $this->assertInstanceOf('Baz\\Bar\\Buzz\\Foo', $foo);

        $loader->remove('Baz\\Bar', './data/Baz/src/');
    }

    /*
     * 12. PSR-4, trailing NS, trailing dir slash
     */
    function testPsr4TrailingNsSepTrailingDirSep()
    {
        Autoloader::addPsr4('Baz\\Bar\\', './data/Baz/src/');

        $foo = new \Baz\Bar\Buzz\Foo();
        $this->assertInstanceOf('Baz\\Bar\\Buzz\\Foo', $foo);

        Autoloader::remove('Baz\\Bar\\', './data/Baz/src/');
    }

    /*
     * 13. PSR-4, leading & trailing NS, trailing dir slash
     */
    function testPsr4LeadingAndTrailingNsSepTrailingDirSep()
    {
        $loader = new Autoloader();
        $loader->addPsr4('\\Baz\\Bar\\', './data/Baz/src/');

        $foo = new \Baz\Bar\Buzz\Foo();
        $this->assertInstanceOf('Baz\\Bar\\Buzz\\Foo', $foo);

        $loader->remove('\\Baz\\Bar\\', './data/Baz/src/');
    }
}
