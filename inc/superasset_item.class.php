<?php


class PluginMypluginSuperasset_Item extends CommonDBTM {



    /**
     * Tabs title
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

        switch ($item->getType()) {
            case "PluginMypluginSuperasset":
                $nb = countElementsInTable(
                    self::getTable(),
                    ['plugin_superassets_id' => $item->getID()]
                );
                return self::createTabEntry(self::getTypeName($nb), $nb);
            case Computer::getType():
                return __('SuperAssets associés');
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
        switch ($item::getType()) {
            case "PluginMypluginSuperasset":
                return self::showForSuperasset($item, $withtemplate);
        }
        if ($item->getType() == 'Computer') {
            $monplugin = new self();
            return $monplugin->showSuperAssetsForComputer($item);
        }
        return true;
    }

    // afficher dans les ordinateurs un nouvel onglet listant les SuperAsset qui lui sont associés.
    function showSuperAssetsForComputer($item) {

        global $DB;

        $computer_id = $item->getField('id');

        $query = [
            'SELECT'     => 'glpi_plugin_myplugin_superassets.*',
            'FROM'       => 'glpi_plugin_myplugin_superassets_items',
            'DISTINCT'        => true,
            'INNER JOIN' => [
                'glpi_plugin_myplugin_superassets' => [
                    'FKEY' => [
                        'glpi_plugin_myplugin_superassets_items' => 'plugin_superassets_id',
                        'glpi_plugin_myplugin_superassets'       => 'id'
                    ]
                ]
            ],
            'WHERE'      => [
                'glpi_plugin_myplugin_superassets_items.itemtype' => 'Computer',
                'glpi_plugin_myplugin_superassets_items.items_id' => $computer_id
            ]
        ];

        $result = $DB->request($query);

        if ($result->numrows() > 0) {
            echo '<h2>Supers Assets Associés</h2>';

            foreach ($result as $data) {
                $superasset_id = $data['id'];
                $superasset_name = $data['name'];

                echo '<br>';
                echo '<a href="/plugins/myplugin/front/superasset.form.php?id=' . $superasset_id . '">';
                echo __($superasset_name);
                echo '</a>';
            }
        }
    }


    /**
     * Specific function for display only items of Superasset
     */
    static function showForSuperasset(PluginMypluginSuperasset $superasset,
                                                               $withtemplate=0) {
        echo "tab content";
        echo "<br>";
        echo "bla bla bla";
        echo "<br>";
        echo "<br>";
        echo Html::input('name', [
            'value' => $superasset->getField('name'),
            'id'    => 'name'
        ]);
    }


    /**
     * Specific function for display only items of Computer
     */
    static function showForComputer(PluginMypluginSuperasset $computer,
                                                               $withtemplate=0) {
        echo "tab content of computer";
        echo "<br>";
        echo Html::input('name', [
            'value' => $computer->getField('name'),
            'id'    => 'name'
        ]);
    }
}