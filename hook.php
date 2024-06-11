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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_myplugin_install()
{
    global $DB;

    //instanciate migration with version
    $migration = new Migration(PLUGIN_MYPLUGIN_VERSION);

    //Create table only if it does not exists yet!
    $table = getTableForItemtype('PluginMypluginSuperasset');
    if (!$DB->tableExists($table)) {
        //table creation query
        $query = "CREATE TABLE `$table` (
                  `id`         INT(11) NOT NULL AUTO_INCREMENT,
                  `is_deleted` TINYINT(1) NOT NULL DEFAULT '0',
                  `name`      VARCHAR(255) NOT NULL,
                  PRIMARY KEY  (`id`)
               ) ENGINE=InnoDB
                 DEFAULT CHARSET=utf8
                 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, $DB->error());
    }

    if ($DB->tableExists($table)) {
        // missing field
        $migration->addField(
            $table,
            'value',
            'string'
        );

        // missing index
        $migration->addKey(
            $table,
            'is_deleted'
        );
    }
    $model = new NotificationTemplate();
    $model->add([
        'name'     => 'plugin_myplugin_superasset',
        'title'    => __('Superasset', 'myplugin'),
        'message'  => __('Superasset', 'myplugin'),
        'template' => 'plugin_myplugin_superasset',
        'active'   => 1
    ]);

    $notification = new Notification();
    $notification->add([
        'itemtype' => 'Superasset',
        'event'    => 'plugin_myplugin_superasset',
        'name'     => __('Superasset', 'myplugin'),
        'comment'  => __('Superasset', 'myplugin'),
        'template' => 'plugin_myplugin_superasset',
        'active'   => 1
    ]);

    //execute the whole migration
    $migration->executeMigration();

    $table = getTableForItemtype('PluginMypluginSuperasset_Item');
    if (!$DB->tableExists($table)) {
        //table creation query
        $query = "CREATE TABLE `$table` (
                  `id`         INT(11) NOT NULL AUTO_INCREMENT,
                  `itemtype`        VARCHAR(255) NOT NULL,
                  `items_id`        INT(11) NOT NULL,
                  `plugin_superassets_id`   INT(11) NOT NULL,
                  PRIMARY KEY  (`id`),
                    KEY `plugin_superassets_id` (`plugin_superassets_id`),
                    KEY `items_id` (`items_id`)
               ) ENGINE=InnoDB
                 DEFAULT CHARSET=utf8
                 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, $DB->error());
    }


    $preferences = [
        [
            'itemtype'  => 'PluginMypluginSuperasset',
            'num'       => 4,
            'rank'      => 1,
            'users_id'  => 0
        ],
        //autres préférences
    ];

    foreach ($preferences as $preference) {
        $DB->insert(
            'glpi_displaypreferences',
            $preference
        );
    }


    //execute the whole migration
    $migration->executeMigration();


    Config::setConfigurationValues('plugin:myplugin', [
        'myplugin_computer_tab' => 0,
        'myplugin_computer_form' => 0
    ]);

    // add rights to current profile
    foreach (PluginMypluginProfile::getAllRights() as $right) {
        ProfileRight::addProfileRights([$right['field']]);
    }

    // ajout d'une action automatique
    CronTask::register('PluginMypluginSuperasset',
        'myaction',
        HOUR_TIMESTAMP,
        [
            'comment'   => '',
            'mode'      => CronTask::MODE_EXTERNAL
        ]);


    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_myplugin_uninstall() {
    global $DB;

    $tables = [
        getTableForItemtype('PluginMypluginSuperasset'),
        getTableForItemtype('PluginMypluginSuperasset_Item')
    ];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->queryOrDie(
                "DROP TABLE `$table`",
                $DB->error()
            );
        }
    }

    // supprimer toutes les entrées de la table glpi_displaypreferences en relations avec le plugin
    $DB->delete(
        'glpi_displaypreferences', [
            'itemtype' => 'PluginMypluginSuperasset'
        ]
    );

    $config = new Config();
    $config->deleteByCriteria(['context' => 'plugin:myplugin']);

    // delete notification

    $notif = new Notification();
    $options = ['itemtype' => 'Superasset',
        'event'    => 'plugin_myplugin_superasset',
        'FIELDS'   => 'id'];
    foreach ($DB->request('glpi_notifications', $options) as $data) {
        $notif->delete($data);
    }

    // delete rights for current profile
    foreach (PluginMypluginProfile::getAllRights() as $right) {
        ProfileRight::deleteProfileRights([$right['field']]);
    }


    return true;
}

// ajout de la recherche sur superasset dans la vue Computer
function plugin_myplugin_getAddSearchOptions($itemtype) {
    $sopt = [];

    if ($itemtype == 'Computer') {
        $sopt['666'] = [
            'table'        => PluginMypluginSuperasset::getTable(),
            'field'        => 'name',
            'name'         => __('Associated Superassets', 'myplugin'),
            'datatype'     => 'itemlink',
            'forcegroupby' => true,
            'usehaving'    => true,
            'joinparams'   => [
                'beforejoin' => [
                    'table'      => PluginMypluginSuperasset_Item::getTable(),
                    'joinparams' => [
                        'jointype' => 'itemtype_item',
                    ]
                ]
            ]
        ];
    }

    return $sopt;
}

// vérifie que des lignes de nos objets y sont associées et les supprimer.
function myplugin_itempurge_called (CommonDBTM $item) {

    global $DB;

    $DB->delete(
        'glpi_plugin_myplugin_superassets', [
            'id'   => 'glpi_plugin_myplugin_superassets_items.plugin_superassets_id',
        ]
    );
    $DB->delete(
        'glpi_plugin_myplugin_superassets_items', [
            'items_id'   => $item->fields['id'],
        ]
    );

}

function plugin_myplugin_MassiveActions($type) {
//    print_r($type);

    $actions = [];
    switch ($type) {
        case 'PluginMypluginSuperasset' :
            $class = PluginMypluginSuperasset::class;
            $key   = 'assign_computer';
            $label = __("Assign Computers to Superasset", 'myplugin');
            $actions[$class.MassiveAction::CLASS_ACTION_SEPARATOR.$key]
                = $label;

            break;

    }
    return $actions;
}
