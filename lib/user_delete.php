<?php

/*
* Satheesh PM, BARC Mumbai
* www.satheesh.anushaktinagar.net
*/


//Function to delete unvalidated users
function unvalidated_user_delete_cron($hook, $unvalidated_user_type, $returnvalue, $params) {
    if (!function_exists('uservalidationbyemail_get_unvalidated_users_sql_where')) {
        return;
    }
    $time_created = strtotime(elgg_get_plugin_setting('unvalidated_user_time', 'user_delete'));
    elgg_set_ignore_access(true);
    access_show_hidden_entities(true);
    $wheres = uservalidationbyemail_get_unvalidated_users_sql_where();
    $wheres[] = "e.time_created < $time_created";

    $options_unvalidated = array(
        'type' => 'user',
        'wheres' => $wheres,
        'limit' => 25,
    );

    $unvalidated_users = elgg_get_entities($options_unvalidated);
    $siteurl = elgg_get_site_entity()->url;
    $sitename = elgg_get_site_entity()->name;
    $siteemail = elgg_get_site_entity()->email;
    $from = $sitename.' <'.$siteemail.'>';
    $register = $siteurl.'register';
    
    foreach ($unvalidated_users as $unvalidated_user) {
        $name = $unvalidated_user->name;
        $email = $unvalidated_user->email;
        $message = sprintf(elgg_echo('user_delete:delete_message'), $name, $siteurl, $email, $register, $sitename, $siteurl);
        elgg_send_email($from, $email, elgg_echo('user_delete:delete_message:subject'), $message);
//Deletes Users        
        $unvalidated_user->delete();
    }
    access_show_hidden_entities(false);
    elgg_set_ignore_access(false);
    if ($unvalidated_users){
        $result = elgg_echo("user_delete:unvalidated_user_delete_cron_true");
    }else{
        $result = elgg_echo("user_delete:unvalidated_user_delete_cron_false");
    }
    $result .= ' | ';
    return $returnvalue.$result;
}

//Function to drop banned users after 30 days
function banned_user_delete_cron($hook, $unvalidated_user_type, $returnvalue, $params){
    elgg_set_ignore_access(true);
    $siteurl = elgg_get_site_entity()->url;
    $sitename = elgg_get_site_entity()->name;
    $siteemail = elgg_get_site_entity()->email;
    $from = $sitename.' <'.$siteemail.'>';
    $register = $siteurl.'register';
    $mail_today = date('dS F Y. h:i:s A', strtotime('now')); 
    
    $db_prefix = elgg_get_config('dbprefix');
    $joins = array("INNER JOIN {$db_prefix}users_entity u on e.guid = u.guid",);
    $time_banned = strtotime(elgg_get_plugin_setting('banned_user_time', 'user_delete'));
    
    $options_banned = array(
        'type' => 'user',
        'joins' => $joins,
        'wheres' => array("u.banned='yes'"),
        'limit' => 25,
        'full_view' => false,
        'pagination' => false,
    );
                
    $banned_users = elgg_get_entities($options_banned);

    foreach ($banned_users as $banned_user){
        $last_action_time = $banned_user->last_action;
        if ($last_action_time < $time_banned){
            $banned_user_name = $banned_user->name;
            $banned_user_email = $banned_user->email;
            if($banned_user_email){
                $message = sprintf(elgg_echo('user_delete:banned_user_message'), $banned_user_name, $siteurl, $banned_user_email, $last_action_time, $register, $sitename, $siteurl, $mail_today);
                elgg_send_email($from, $banned_user_email, elgg_echo('user_delete:banned_user_message:subject'), $message);
//Delete the User
               $banned_user->delete();
            }else{
//Delete the User
               $banned_user->delete();
            }
            $result = elgg_echo("user_delete:banned_user_delete_cron_true");
        }else{
            $result = elgg_echo("user_delete:banned_user_delete_cron_false");
        }
    }
    $result .= ' | ';
    elgg_set_ignore_access(false);
    return $returnvalue.$result;
}
