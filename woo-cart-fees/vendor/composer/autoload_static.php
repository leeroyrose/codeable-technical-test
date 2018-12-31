<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitab0796ad3fbf810b798ec28d11451ead
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Tests\\' => 6,
        ),
        'C' => 
        array (
            'CartFees\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/tests',
        ),
        'CartFees\\' => 
        array (
            0 => __DIR__ . '/../..' . '/CartFees',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitab0796ad3fbf810b798ec28d11451ead::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitab0796ad3fbf810b798ec28d11451ead::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
