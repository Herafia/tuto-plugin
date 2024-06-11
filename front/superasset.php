<?php
use GlpiPlugin\Myplugin\Superasset;

include ('../../../inc/includes.php');

Html::header(PluginMypluginSuperasset::getTypeName(),
    $_SERVER['PHP_SELF'],
    "plugins",
    "PluginMypluginSuperasset",
    "superasset");
Search::show('PluginMypluginSuperasset');
Html::footer();
