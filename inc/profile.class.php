<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginMypluginProfile extends CommonDBTM {
    public static $rightname = 'profile';

    static function getTypeName($nb = 0) {
        return __("My plugin", 'myplugin');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item instanceof Profile
            && $item->getField('id')) {
            return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item,
                                                        $tabnum=1,
                                                        $withtemplate=0) {
        if ($item instanceof Profile
            && $item->getField('id')) {
            return self::showForProfile($item->getID());
        }

        return true;
    }

    static function getAllRights($all = false) {
        $rights = array(
            array('itemtype' => 'PluginMypluginSuperasset',
                'label'    => PluginMypluginSuperasset::getTypeName(),
                'field'    => 'myplugin::superasset'
            )
        );

        return $rights;
    }


    static function showForProfile($profiles_id = 0) {
        $canupdate = self::canUpdate();
        $profile = new Profile();
        $profile->getFromDB($profiles_id);
        echo "<div class='firstbloc'>";
        echo "<form method='post' action='".$profile->getFormURL()."'>";

        $rights = self::getAllRights();
        $profile->displayRightsChoiceMatrix($rights, array(
            'canedit'       => $canupdate,
            'title'         => self::getTypeName(),
        ));

        if ($canupdate) {
            echo "<div class='center'>";
            echo Html::hidden('id', array('value' => $profiles_id));
            echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
            echo "</div>\n";
            Html::closeForm();

            echo "</div>";
        }
    }
}