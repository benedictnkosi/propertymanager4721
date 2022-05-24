<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit436a22f6ac2e4dbc861ae149384854ec
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

        spl_autoload_register(array('ComposerAutoloaderInit436a22f6ac2e4dbc861ae149384854ec', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit436a22f6ac2e4dbc861ae149384854ec', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit436a22f6ac2e4dbc861ae149384854ec::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
