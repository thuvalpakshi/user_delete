<?php

/*
* Satheesh PM, BARC Mumbai
* www.satheesh.anushaktinagar.net
*/


elgg_register_event_handler('init', 'system', 'user_delete_init');

function user_delete_init(){
    elgg_extend_view('css/admin', 'user_delete/admin');
    elgg_register_plugin_hook_handler('cron', 'daily', 'banned_user_delete_cron');
    elgg_register_plugin_hook_handler('cron', 'daily', 'unvalidated_user_delete_cron');
    elgg_register_library('user_delete_functions', elgg_get_plugins_path() . 'user_delete/lib/user_delete.php');
    elgg_load_library('user_delete_functions');
}

