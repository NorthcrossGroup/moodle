<?php
require $CFG->libdir . "/externallib.php";

function xmldb_local_symphony_upgrade($oldversion){
  global $DB;
  if($oldversion < 2014121801) {
    $servicerole = create_role('Drupal Service', 'drupalservice','Allows users to access the Drupal service endpoint');
    set_config('roleid',$servicerole,'local_symphony');
  }
  if($oldversion < 2014121802) {
    $capabilities=array(
      "moodle/webservice:createtoken",
      "moodle/cohort:manage",
      "moodle/cohort:view",
      "webservice/rest:use",
    );
    $servicerole = get_config('local_symphony', 'roleid');
    $systemcontext = context_system::instance();
    foreach($capabilities as $capability){
      assign_capability($capability, true, $servicerole, $systemcontext);
    }
  }
  if($oldversion < 2014121803){
    // Create a service user. The password can be random because the token acts as authentication.
    $serviceuser = create_user_record("DrupalAuthUser", md5(time()));
    set_config('serviceuserid', $serviceuser->id, 'local_symphony');
  }
  if($oldversion < 2014121805){
    // create a token
    $serviceuser->id = get_config('local_symphony', 'serviceuserid');

    $service = array_shift($DB->get_records('external_services',array('component'=>"local_symphony",'shortname'=>'drupal_services')));
    set_config('serviceid', $service->id, 'local_symphony');

    $token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id,
      $serviceuser->id, context_system::instance(),
      null, '');

  }
  if($oldversion < 2014121806){
    $userid=get_config('local_symphony', 'serviceuserid');
    $roleid=get_config('local_symphony', 'roleid');
    $contextid=context_system::instance();
    role_assign($roleid, $userid, $contextid, 'local_symphony');

  }
  if($oldversion < 2014121809){
    $roleid=get_config('local_symphony', 'roleid');
    $context=context_system::instance();
    set_role_contextlevels($roleid, array($context->contextlevel));
  }
  upgrade_plugin_savepoint(true, 2014121809, 'local', 'symphony');
}