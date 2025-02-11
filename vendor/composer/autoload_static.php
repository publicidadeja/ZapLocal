<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4b55c7ef33235a0353c1b6b079f86964
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4b55c7ef33235a0353c1b6b079f86964::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4b55c7ef33235a0353c1b6b079f86964::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4b55c7ef33235a0353c1b6b079f86964::$classMap;

        }, null, ClassLoader::class);
    }
}
