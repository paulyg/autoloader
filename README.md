##Synopsis

`Paulyg\Autoloader` is about the smallest and simplest PHP class autoloader you can imagine. It loads classes following both the established and popular [PSR-0 standard][psr0] and the recently ratified [PSR-4 standard][psr4]. It uses PHP's internal SPL stack to keep track of prefix-directory mappings rather than doing it in userland code.

[psr0]: http://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
[psr4]: http://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

##Motivation

There are plenty of autoloading libraries and components already for PHP. So why would I want to write my own? My original motivation was to eliminate the many empty directories that are a consequence of the PSR-0 directory structure. This gets exasperated if you are using [Composer](http://getcomposer.org) to manage libraries. Here is an example, pretend we are using` Zend\Feed`, which pulls in `Zend\Stdlib` and `Zend\Escaper`.
```
/path/to/project/vendor/zendframework/zend-feed/Zend/Feed/
/path/to/project/vendor/zendframework/zend-stdlib/Zend/Stdlib/
/path/to/project/vendor/zendframework/zend-escaper/Zend/Escaper/
                              |             |       |     |
                              |             |       |     `-- Finally code here!
                              |             |       |
                              |             |       `-- No code in this dir
                              |             |
                              |             `-- No code in this dir
                              |
                              `-- No code in this dir
```
I was constantly traversing those empty directories in my own code and in looking at library code. Composer enforces the first two directories, e.g. `zendframework/zend-blah`, as part of its own _namespacing_ scheme. But at least I could squash the other two. I called this **aliasing**, and the first version of this autoloader had a method called `addAlias()`. Later I saw the [PSR-4 proposal][psr4prop] from [PHP-FIG](http://www.php-fig.org "PHP Framework Interop Group") that is essentially the same idea. So that method got renamed to `addPsr4()`, even though PSR-4 has still not been ratified. *Disclaimer: I have no affiliation with PHP-FIG.*

The PSR-4 autoloading standard allows you to stucture library code like so.
```
/path/to/project/vendor/zf/zend-feed/Reader.php <- Class: Zend\Feed\Reader
                                     Writer.php <- Class: Zend\Feed\Writer
                                     AbstractEntry.php <- Class: Zend\Feed\AbstractEntry
```
And autoload them like so.
```php
<?php
use Paulyg\Autloader;
Autoloader::addPsr4('Zend\Feed', '/path/to/project/zf/zend-feed');
$reader = new \Zend\Feed\Reader();
```
A secondary motivation was some postings I saw in the PHPUnit issues on Github, re Sebastian Bergmann [not wanting to incude a PSR-0 autoloader][sb1] in PHPUnit. His [main beef][sb2] was that all the current PSR-0 autoloaders maintain the map of namespace/class prefixes to directories in userspace PHP arrays rather than in the `spl_autoloader` stack where it is managed by more efficient compiled C code. I believe Sebastian actually prefers classmaps but I took the point about putting the `spl_autoloader` stack to better use with this library.

[psr4prop]: https://groups.google.com/forum/#!topic/php-fig/qT7mEy0RIuI
[sb1]: https://github.com/sebastianbergmann/phpunit/pull/460
[sb2]: https://github.com/sebastianbergmann/phpunit/pull/649

## Installation

The autoloader is just one small class file and I don't expect it to change much so I recommend just downloading it from Github using the *raw* feature.
```
$ wget https://github.com/paulyg/autoloader/raw/master/src/Paulyg/Autloader.php
```

Or you could use Composer. Add the following to your project's `composer.json` file:
```
{
    "require": {
        "paulyg/autoloader": "dev-master"
    }
}
```

Or clone the Git repo.
```
$ git clone https://github.com/paulyg/autoloader
```

## API

**3 simple methods. All methods work both staticlly or called from an instance.**
1. `addPsr0($prefix, $dir, $prepend = false)`
2. `addPsr4($prefix, $dir, $prepend = false)`
3. `remove($prefix, $dir)`

## Examples
### PSR-0

Basic loading of classes with namespace and PSR-0 directory structure.
```php
<?php
use Paulyg\Autoloader;
Autoloader::addPsr0('Symfony', '/path/to/project/vendor/symfony/src');
$collection = new Symfony\Component\Routing\RouteCollection();
```
Loads from `/path/to/project/vendor/symfony/src/Symfony/Component/Routing/RouteCollection.php`

Leading (root) namespace separators are stripped. These two are equivalent.
```php
<?php
use Paulyg\Autoloader;
Autoloader::addPsr0('Silex', '/path/to/project/vendor/silex/silex/src');
Autoloader::addPsr0('\Silex', '/path/to/project/vendor/silex/silex/src');
```

Trailing namespace separators are honored. This is useful when you have namespaced code and non-namespaced code that share the same prefix. This example also illustrates using from an instance vs the static method calls.
```php
<?php
$autoloader = new Paulyg\Autoloader();
$autoloader->addPsr0('Zend\\', '/path/to/project/vendor/zf2/library');
$reader = new Zend\Feed\Reader(); // <- will load class
$writer = new Zend_Feed_Writer(); // <- will not load class
```

Load PEAR/Zend/Horde style classes. Note they are still PSR-0 compliant even though many other libraries have a separate method for namespaced and non-namespaced classes.
```php
<?php
use Paulyg\Autoloader;
Autoloader::addPsr0('Zend', '/path/to/project/vendor/zf1/library');
$writer = new Zend_Feed_Writer(); // <- Now works
```

Works with trailing underscore as well.
```php
<?php
use Paulyg\Autoloader;
Autoloader::addPsr0('Zend_', '/path/to/project/vendor/zf1/library');
$writer = new Zend_Feed_Writer(); // <- Still works
$fooBar = new ZendFooBar(); // <- Nothing found
```

### PSR-4

Basic loading of classes with namespace and PSR-4 directory structure.
```php
<?php
use Paulyg\Autoloader;
Autoloader::addPsr4('Symfony\Component\Routing', '/path/to/project/vendor/symfony/routing/');
$collection = new Symfony\Component\Routing\RouteCollection();
```
Loads from `/path/to/project/vendor/symfony/routing/RouteCollection.php`

Leading (root) namespace separators are again stripped. These two are equivalent.
```php
<?php
use Paulyg\Autoloader;
Autoloader::addPsr4('Zend\Db', '/path/to/project/vendor/zendframework/zend-db/');
Autoloader::addPsr4('\Zend\Db', '/path/to/project/vendor/zendframework/zend-db/');
```

Trailing namespace separators are honored the same way as PSR-0. This example also illustrates using from an instance vs the static method calls.
```php
<?php
$autoloader = new Paulyg\Autoloader();
$autoloader->addPsr4('Zend\Feed', '/path/to/project/vendor/zendframework/zend-feed/');
$reader = new Zend\Feed\Reader(); // <- will load class
$writer = new Zend_Feed_Writer(); // <- will not load class
```

## Tests

`Paulyg\Autoloader` comes with a full set of tests, written with PHPUnit. To run the tests yourself run the following command from the `test` directory.
```
$ phpunit "Paulyg\AutoloaderTest"
```

## License

`Paulyg\Autoloader` is licensed using the MIT license.
