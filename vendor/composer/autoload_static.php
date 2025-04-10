<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit792ac377c88c5f5ad16e01c7c3558354
{
    public static $files = array (
        '6653e3c566de46c33e98b02efe481c54' => __DIR__ . '/../..' . '/includes/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'Y' => 
        array (
            'Yusocia\\YusocialEventManagment\\Classes\\' => 39,
            'Yusocia\\YusocialEventManagment\\' => 31,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Yusocia\\YusocialEventManagment\\Classes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
        'Yusocia\\YusocialEventManagment\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit792ac377c88c5f5ad16e01c7c3558354::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit792ac377c88c5f5ad16e01c7c3558354::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit792ac377c88c5f5ad16e01c7c3558354::$classMap;

        }, null, ClassLoader::class);
    }
}
