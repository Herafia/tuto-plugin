<?php

/**
 * -------------------------------------------------------------------------
 * myplugin plugin for GLPI
 * Copyright (C) 2024 by the myplugin Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;

define('PLUGIN_MYPLUGIN_VERSION', '0.0.1');

// Minimal GLPI version, inclusive
define("PLUGIN_MYPLUGIN_MIN_GLPI_VERSION", "10.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_MYPLUGIN_MAX_GLPI_VERSION", "10.0.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_myplugin()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['myplugin'] = true;

    // add menu hook
    $PLUGIN_HOOKS['menu_toadd']['myplugin'] = [
        // insert dans plugin menu
        'plugins' => 'PluginMypluginSuperasset'
    ];
    Plugin::registerClass('PluginMypluginConfig', ['addtabon' => 'Config']);
    Plugin::registerClass('PluginMypluginSuperasset_Item', ['addtabon' => 'Computer']);
    Plugin::registerClass('PluginMypluginProfile', ['addtabon' => 'Profile']);
    Plugin::registerClass('PluginMypluginSuperasset', ['notificationtemplates_types' => true]);

    $PLUGIN_HOOKS['item_purge']['myplugin'] = [
        'Computer'  => 'myplugin_itempurge_called',
    ];

    $PLUGIN_HOOKS[Hooks::PRE_ITEM_FORM]['myplugin']
        = [PluginMypluginSuperasset::class, 'preItemFormComputer'];

    // ajoute des actions massives supplémentaires
    $PLUGIN_HOOKS['use_massive_action']['myplugin'] = true;

}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_myplugin()
{
    Config::setConfigurationValues('PluginMypluginNotificationTargetSuperasset');

    return [
        'name'           => 'myplugin',
        'version'        => PLUGIN_MYPLUGIN_VERSION,
        'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
        'license'        => '',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_MYPLUGIN_MIN_GLPI_VERSION,
                'max' => PLUGIN_MYPLUGIN_MAX_GLPI_VERSION,
            ]
        ]
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_myplugin_check_prerequisites() {
    // Strict version check (could be less strict, or could allow various version)
    if (version_compare(GLPI_VERSION, '9.1', 'lt')) {
        if (method_exists('Plugin', 'messageIncompatible')) {
            echo Plugin::messageIncompatible('core', '9.1');
        } else {
            echo "This plugin requires GLPI >= 9.1";
        }
        return false;
    }
    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_myplugin_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'myplugin');
    }
    return false;
}
