<?php
require $CFG->libdir . "/externallib.php";
function xmldb_local_symphony_install()
{
  global $DB;
  $context=context_system::instance();
  // Create a webservice service role.
  $servicerole = create_role('Drupal Service', 'drupalservice', 'Allows users to access the Drupal service endpoint');
  // Give the role the system context.
  set_role_contextlevels($servicerole, array($context->contextlevel));
  // Save the roleid for use later.
  set_config('roleid', $servicerole, 'local_symphony');

  // Give the role the requisite capabilities to run the service functions
  $capabilities=array(
    "moodle/webservice:createtoken",
    "moodle/cohort:manage",
    "moodle/cohort:view",
    "webservice/rest:use",
  );
  $systemcontext = context_system::instance();
  foreach($capabilities as $capability){
    assign_capability($capability, true, $servicerole, $systemcontext);
  }

  // Create a service user. The password can be random because the token acts as authentication.
  $serviceuser = create_user_record("DrupalAuthUser", md5(time()));
  set_config('serviceuserid', $serviceuser->id, 'local_symphony');

  // Give the new user the service role
  role_assign($servicerole, $serviceuser->id, $systemcontext, 'local_symphony');

  // Get the webservice internal ID created during install
  $service = array_shift($DB->get_records('external_services',array('component'=>"local_symphony",'shortname'=>'drupal_services')));
  set_config('serviceid', $service->id, 'local_symphony');

  // create a token
  $token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id,
    $serviceuser->id, $context,
    null, '');

}