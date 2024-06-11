<?php
use GlpiPlugin\Myplugin\Superasset;

include ('../../../inc/includes.php');

$supperasset = new PluginMypluginSuperasset();

$computer = new Computer();

if (isset($_POST["add"])) {
    $newID = $supperasset->add($_POST);

    if ($_SESSION['glpibackcreated']) {
        Html::redirect($supperasset->getFormURL()."?id=".$newID);
    }
    Html::back();

} else if (isset($_POST["delete"])) {
    $supperasset->delete($_POST);
    $supperasset->redirectToList();

} else if (isset($_POST["restore"])) {
    $supperasset->restore($_POST);
    $supperasset->redirectToList();

} else if (isset($_POST["purge"])) {
    $supperasset->delete($_POST, 1);
    $supperasset->redirectToList();

} else if (isset($_POST["update"])) {
    $supperasset->update($_POST);
    Html::back();

} else {
    // fill id, if missing
    isset($_GET['id'])
        ? $ID = intval($_GET['id'])
        : $ID = 0;

    // display form
    Html::header(PluginMypluginSuperasset::getTypeName(),
        $_SERVER['PHP_SELF'],
        "plugins",
        "PluginMypluginSuperasset",
        "superasset");
    $supperasset->display(['id' => $ID]);



    $dbu = new DbUtils();

    $superassetsItems = $dbu->getAllDataFromTable('glpi_plugin_myplugin_superassets_items');

    $results = [];
    foreach ($superassetsItems as $item) {
        if ($item['itemtype'] === 'Computer') {
            // filtre sur le type Computer
            $table = getTableForItemType('Computer');

            // details du computer en utilisant sur les items_id
            $computer = $dbu->getAllDataFromTable($table, ['id' => $item['items_id']]);

            // Si l'ordinateur n'a pas encore été ajouté aux résultats, ajoutez-le
            if (!empty($computer)) {
                foreach ($computer as $computerDetails) {
                    // éviter les doublons
                    $results[$computerDetails['id']] = [
                        'superasset_id' => $item['plugin_superassets_id'],
                        'name' => $computerDetails['name']
                    ];
                }
            }
        }
    }

    echo "<h2>Liste des ordinateurs déjà associés</h2><br>";
    foreach ($results as $result) {
        echo "<a href='../../../front/computer.form.php?id=".$result['superasset_id']."'>".__($result['name'])."</a><br>";
    }


    Html::footer();


}
