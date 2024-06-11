<?php

// forbid direct calls of this file
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginMypluginSuperasset extends CommonDBTM {
    const RIGHT_ONE = 128;
    // permits to automaticaly store logs for this itemtype
    // in glpi_logs table
    public $dohistory = true;

    // right management, we'll change this later
    static $rightname = 'computer';


    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
        switch ($item->getType()) {
            case "Computer":
                $nb = countElementsInTable(self::getTable(),  "`items_id` = ".$item->getID()
                );
                return self::createTabEntry(self::getTypeName($nb), $nb);
        }
        return '';
    }

    /**
     * Display tabs content
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
        switch ($item->getType()) {
            case "Computer":
                return self::showForSuperasset($item, $withtemplate);
        }

        return true;
    }

    /**
     *  Name of the itemtype
     */
    static function getTypeName($nb=0) {
        return _n('Super-asset', 'Super-assets', $nb);
    }

    function showForm($ID, $options=array()) {
        // init form html
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr>";
        echo "<td><label for='name'>".__('name')."</label></td>";
        echo "<td>";
        echo Html::input('name', [
            'value' => $this->getField('name'),
            'id'    => 'name'
        ]);
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td><label for='name'>".__('Computer')."</label></td>";
        echo "<td>";
        echo Dropdown::show('Computer', [
            'name'  => 'items_id',
        ]);
        echo "</td>";
        echo "</tr>";

        // end form html and show controls
        $this->showFormButtons($options);

        return true;
    }

    /**
     * Define menu name
     */
    static function getMenuName($nb = 0) {
        // call class label
        return self::getTypeName($nb);
    }

    /**
     * Define additionnal links used in breacrumbs and sub-menu
     */
    static function getMenuContent() {
        $title  = self::getMenuName(2);
        $search = self::getSearchURL(false);
        $form   = self::getFormURL(false);

        // define base menu
        $menu = [
            'title' => __("My plugin", 'myplugin'),
            'page'  => $search,

            // define sub-options
            // we may have multiple pages under the "Plugin > My type" menu
            'options' => [
                'superasset' => [
                    'title' => $title,
                    'page'  => $search,

                    //define standard icons in sub-menu
                    'links' => [
                        'search' => $search,
                        'add'    => $form
                    ]
                ]
            ]
        ];

        return $menu;
    }

    function defineTabs($options=array()) {
        $tabs = array();
        $this->addDefaultFormTab($tabs)
            ->addStandardTab('PluginMypluginSuperasset_Item', $tabs, $options)
            ->addStandardTab('Notepad', $tabs, $options)
            ->addStandardTab('Log', $tabs, $options);

        return $tabs;
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id' => '4',
            'table' => self::getTable(),
            'field' => 'value',
            'name' => __('Valeur'),
            'datatype' => 'text'
        ];

        return $tab;
    }
// vérifier que le champ name est bien rempli
    public function prepareInputForAdd($input)
    {
        if (isset($input['name'])) {
            return $input;
        }
        return false;
    }

    // vérifier que le champ name est bien rempli après update
    public function prepareInputForUpdate($history = true)
    {
        if (isset($input['name'])) {
            return $input;
        }
        return false;
    }

    // vérifier que l'item est bien supprimé après suppression du superasset
    public function post_purgeItem()
    {
        /** @var \DBmysql $DB */
        global $DB;

        $iterator = $DB->request([
            'FROM'   => 'glpi_plugin_myplugin_superassets_items',
            'WHERE'  => [
                'plugin_superassets_id'   => $this->fields['id'],
            ]
        ]);

        foreach ($iterator as $data) {
            $DB->delete(
                'glpi_plugin_myplugin_superassets_items', [
                    'id' => $data['id']
                ]
            );
        }

    }

    static public function preItemFormComputer($params) {

        global $DB;

        $iterator = $DB->request([
            'FROM'       => 'glpi_plugin_myplugin_superassets_items',
            'INNER JOIN' => [
                'glpi_plugin_myplugin_superassets' => [
                    'FKEY' => [
                        'glpi_plugin_myplugin_superassets_items'     => 'plugin_superassets_id',
                        'glpi_plugin_myplugin_superassets' => 'id'
                    ]
                ]
            ],
            'WHERE'      => [
                'glpi_plugin_myplugin_superassets_items.itemtype' => 'Computer',
                'glpi_plugin_myplugin_superassets_items.items_id' => $params['item']->fields['id']
            ]
        ]);

        echo '<a href="' . $params['options']['_target'] . '?id=' . $params['item']->fields['id'] . '&forcetab=PluginMypluginSuperasset_Item$' . $params['item']->fields['id'] . '">';
        echo __(count($iterator) . ' Super assets associés');
        echo '</a>';


    }

    function getRights($interface='central') {
        // if we need to keep standard rights
        $rights = parent::getRights();

        // define an additional right
        $rights[self::RIGHT_ONE] = __("My specific rights", "myplugin");

        return $rights;
    }


    /// actions massives ///


    function getSpecificMassiveActions($checkitem=NULL) {
        $actions = parent::getSpecificMassiveActions($checkitem);

        // Ajouter l'action massive "add_computer"
        $class        = __CLASS__;
        $action_key   = "add_computer";
        $action_label = "Add Computer";
        $actions[$class.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key] = $action_label;

        return $actions;
    }

    static function showMassiveActionsSubForm(MassiveAction $ma) {
        switch ($ma->getAction()) {
            case 'add_computer':
                echo __("Select a computer to add:");
                Computer::dropdown(['name' => 'computer_id']);
                echo Html::submit(__('Add Computer'), array('name' => 'massiveaction'))."</span>";
                break;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
        switch ($ma->getAction()) {
            case 'add_computer':
                $computer_id = $ma->getInput('computer_id');
                foreach ($ids as $id) {
                    if ($item->getFromDB($id)) {
                        if ($item->addComputer($computer_id)) {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                            $ma->addMessage(__("Failed to add computer to Super asset ID: ") . $id);
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage(__("Failed to retrieve Super asset ID: ") . $id);
                    }
                }
                return;
        }

        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

// Ajouter un computer à un superasset
    function addComputer($computer_id) {
        global $DB;

        if (empty($this->fields['id'])) {
            return false;
        }

                // Ajoute l'ordinateur au superasset
        $insertQuery = "INSERT INTO glpi_plugin_myplugin_superassets_items (plugin_superassets_id, itemtype, items_id)
                    VALUES ('".intval($this->fields['id'])."', 'Computer', '".intval($computer_id)."')";
        if ($DB->query($insertQuery)) {
            return true;
        } else {
            return false;
        }
    }


    static function cronInfo($name) {

        switch ($name) {
            case 'myaction' :
                return ['description' => __('action desc', 'myplugin')];
        }
        return array();
    }

    static function cronMyaction($task=NULL) {
        // do the action

        print_r('task');
        return true;
    }

}