<?php namespace osmf;

define('LIBRARIES_PATH', '/Users/garetjax/Sites/osms-root');
define('APPLICATION_PATH', '/Users/garetjax/Sites/osms-root/osms');
define('SETTINGS_FILE', 'settings.php');

// Setup include path
if (defined('LIBRARIES_PATH') && LIBRARIES_PATH !== '') {
	set_include_path(LIBRARIES_PATH . PATH_SEPARATOR . get_include_path());
}

set_include_path(APPLICATION_PATH . PATH_SEPARATOR . get_include_path());

// Bootstrap the framework
require_once 'osmf/bootstrap.php';


try {
	$customer = \osms\auth\models\Role::query()->where('name', 'eq', 'customer')->one();
	foreach (\osms\auth\models\User::query()->where('role', 'eq', $customer)->all() as $user) {
		$user->delete();
	}
} catch (\osmf\Model\ObjectNotFound $e) {
}

foreach (\osms\auth\models\User::query()->all() as $user) {
	$user->delete();
}

foreach (\osms\auth\models\Role::query()->all() as $role) {
	$role->delete();
}

$roles = array(
	array('admin', 'Administrator'),
	array('cro', 'Customer Realtionship Officer'),
	array('customer', 'Customer'),
);
$role_models = array();

foreach ($roles as $role) {
	$role = array_combine(array('name', 'display_name'), $role);
	$model = new \osms\auth\models\Role();
	$model->name = $role['name'];
	$model->display_name = $role['display_name'];
	$model->save();
	$role_models[$role['name']] = $model;
}


$users = array(
	array('Admin', 'superman', 'admin', NULL, TRUE),
	array('Alain', 'dfi2012', 'cro', NULL, TRUE),
	array('Doris', 'detec2012', 'cro', NULL, TRUE),
	array('Eveline', 'dff2012', 'customer', 'Alain', TRUE),
	array('Didier', 'dfae2012', 'customer', 'Doris', TRUE),
	array('Ueli', 'ddps2012', 'customer', 'Alain', TRUE),
	array('Simonetta', 'dfjp2012', 'customer', 'Doris', TRUE),
	array('Johann', 'dfe2012', 'customer', 'Alain', FALSE),
);
$user_models = array();

foreach ($users as $user) {
	$user = array_combine(array('username', 'password', 'role', 'cro', 'enabled'), $user);
	$model = new \osms\auth\models\User();
	$model->username = $user['username'];
	$model->setPassword($user['password']);
	$model->role = $role_models[$user['role']];
	$model->cro = $user['cro'] ? $user_models[$user['cro']] : NULL;
	$model->enabled = $user['enabled'];
	$model->save();
	$user_models[$user['username']] = $model;
}
 
