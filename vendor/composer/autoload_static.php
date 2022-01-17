<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4aa6f840b82c6b0093d7a1293314c44c
{
    public static $files = array (
        '9c1d1eaeae8e82c98ff6d22f223c66d4' => __DIR__ . '/../..' . '/includes/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'ExpenseManager\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ExpenseManager\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4aa6f840b82c6b0093d7a1293314c44c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4aa6f840b82c6b0093d7a1293314c44c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4aa6f840b82c6b0093d7a1293314c44c::$classMap;

        }, null, ClassLoader::class);
    }
}