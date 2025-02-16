<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6aa00a9a0d1f22a9fc58c83d4d37c55e
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPDevAssist\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPDevAssist\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'WPDevAssist\\ActionQuery' => __DIR__ . '/../..' . '/inc/ActionQuery.php',
        'WPDevAssist\\Activation' => __DIR__ . '/../..' . '/inc/Activation.php',
        'WPDevAssist\\Asset' => __DIR__ . '/../..' . '/inc/Asset.php',
        'WPDevAssist\\Assistant' => __DIR__ . '/../..' . '/inc/Assistant.php',
        'WPDevAssist\\Assistant\\Control' => __DIR__ . '/../..' . '/inc/Assistant/Control.php',
        'WPDevAssist\\Assistant\\MailHog' => __DIR__ . '/../..' . '/inc/Assistant/MailHog.php',
        'WPDevAssist\\Assistant\\Section' => __DIR__ . '/../..' . '/inc/Assistant/Section.php',
        'WPDevAssist\\Assistant\\SupportUser' => __DIR__ . '/../..' . '/inc/Assistant/SupportUser.php',
        'WPDevAssist\\Assistant\\WPDebug' => __DIR__ . '/../..' . '/inc/Assistant/WPDebug.php',
        'WPDevAssist\\Deactivation' => __DIR__ . '/../..' . '/inc/Deactivation.php',
        'WPDevAssist\\Fs' => __DIR__ . '/../..' . '/inc/Fs.php',
        'WPDevAssist\\Htaccess' => __DIR__ . '/../..' . '/inc/Htaccess.php',
        'WPDevAssist\\MailHog' => __DIR__ . '/../..' . '/inc/MailHog.php',
        'WPDevAssist\\Notice' => __DIR__ . '/../..' . '/inc/Notice.php',
        'WPDevAssist\\PluginsScreen' => __DIR__ . '/../..' . '/inc/PluginsScreen.php',
        'WPDevAssist\\PluginsScreen\\ActivationManager' => __DIR__ . '/../..' . '/inc/PluginsScreen/ActivationManager.php',
        'WPDevAssist\\PluginsScreen\\Downloader' => __DIR__ . '/../..' . '/inc/PluginsScreen/Downloader.php',
        'WPDevAssist\\Setting' => __DIR__ . '/../..' . '/inc/Setting.php',
        'WPDevAssist\\Setting\\BasePage' => __DIR__ . '/../..' . '/inc/Setting/BasePage.php',
        'WPDevAssist\\Setting\\Control\\Checkbox' => __DIR__ . '/../..' . '/inc/Setting/Control/Checkbox.php',
        'WPDevAssist\\Setting\\Control\\Control' => __DIR__ . '/../..' . '/inc/Setting/Control/Control.php',
        'WPDevAssist\\Setting\\Control\\Status' => __DIR__ . '/../..' . '/inc/Setting/Control/Status.php',
        'WPDevAssist\\Setting\\Control\\Text' => __DIR__ . '/../..' . '/inc/Setting/Control/Text.php',
        'WPDevAssist\\Setting\\DebugLog' => __DIR__ . '/../..' . '/inc/Setting/DebugLog.php',
        'WPDevAssist\\Setting\\DevEnv' => __DIR__ . '/../..' . '/inc/Setting/DevEnv.php',
        'WPDevAssist\\Setting\\Page' => __DIR__ . '/../..' . '/inc/Setting/Page.php',
        'WPDevAssist\\Setting\\SupportUser' => __DIR__ . '/../..' . '/inc/Setting/SupportUser.php',
        'WPDevAssist\\Setting\\Tab' => __DIR__ . '/../..' . '/inc/Setting/Tab.php',
        'WPDevAssist\\Setup' => __DIR__ . '/../..' . '/inc/Setup.php',
        'WPDevAssist\\WPDebug' => __DIR__ . '/../..' . '/inc/WPDebug.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6aa00a9a0d1f22a9fc58c83d4d37c55e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6aa00a9a0d1f22a9fc58c83d4d37c55e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6aa00a9a0d1f22a9fc58c83d4d37c55e::$classMap;

        }, null, ClassLoader::class);
    }
}
