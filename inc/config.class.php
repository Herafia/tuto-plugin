<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginMypluginConfig extends Config {

    static function getTypeName($nb=0) {
        return __('My plugin', 'myplugin');
    }

    static function getConfig() {
        return Config::getConfigurationValues('plugin:xivo');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
        switch ($item->getType()) {
            case "Config":
                return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item,
                                                        $tabnum=1,
                                                        $withtemplate=0) {
        switch ($item->getType()) {
            case "Config":
                return self::showForConfig($item, $withtemplate);
        }

        return true;
    }

    static function showForConfig(Config $config,
                                         $withtemplate=0) {
        global $CFG_GLPI;

        if (!self::canView()) {
            return false;
        }

        $current_config = self::getConfig();
        $canedit        = Session::haveRight(self::$rightname, UPDATE);

        if ($canedit) {
            echo "<form name='form' action='".Toolbox::getItemTypeFormURL("Config")."' method='post'>";
        }

        echo __("Display tab in computer", 'myplugin');
        Dropdown::showYesNo("myplugin_computer_tab", $current_config['myplugin_computer_tab']);


        echo __("Display information in computer form", 'myplugin');
        Dropdown::showYesNo("myplugin_computer_form", $current_config['myplugin_computer_form']);

        if ($canedit) {
            // we define a set of hidden field to indicate to glpi, we save data for the plugin context
            echo Html::hidden('config_class', ['value' => __CLASS__]);
            echo Html::hidden('config_context', ['value' => 'plugin:xivo']);

            echo Html::submit(_sx('button','Save'), [
                'name' => 'update'
            ]);
            Html::closeForm();
        }
    }
}
