<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitfe84d78c647a17dbfcd1426f3d3848d6
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInitfe84d78c647a17dbfcd1426f3d3848d6', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitfe84d78c647a17dbfcd1426f3d3848d6', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitfe84d78c647a17dbfcd1426f3d3848d6::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
