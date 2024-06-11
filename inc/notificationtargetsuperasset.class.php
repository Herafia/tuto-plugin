<?php
class PluginMypluginNotificationTargetSuperasset
    extends NotificationTarget {

    function getEvents() {
        return array ('my_event_key' => __('My event label', 'myplugin'));
    }

    function getDatasForTemplate($event, $options=array()) {
        $this->datas['PluginMypluginSuperasset'] = __('Name');
    }
}