<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita32ab35ef129902bc17ab5dc4f01fc45
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita32ab35ef129902bc17ab5dc4f01fc45::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita32ab35ef129902bc17ab5dc4f01fc45::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
