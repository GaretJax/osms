<?php namespace osmf;


$config = Config::getInstance();


$config->debug = TRUE;


/**
 * The list of applications currently installed for this web application
 * instance.
 */
$config->installed_apps = array(
	'auth',
	'messaging',
	'logging',
);

/**
 * Directory containing the application packages.
 */
$config->app_root = __DIR__ . DIRECTORY_SEPARATOR . 'applications';

/**
 * A middlewares is a light low-level plugin for globally altering
 * the request/response objects. List below the middlewares you
 * want to use.
 */
$config->middleware_classes = array(
	'\osmf\SessionMiddleware',
	'\osmf\AuthenticationMiddleware',
	'\osmf\CsrfMiddleware',
	'\osmf\NotFoundMiddleware',
);

/**
 * A context processor is a callable which takes a request object as
 * its only argument and returns an array of items to be merged into
 * the context. List below the context processors you want to use.
 */
$config->context_processors = array(
	'\osmf\ContextProcessors::auth',
	'\osmf\ContextProcessors::config',
	'\osmf\ContextProcessors::request',
	'\osmf\ContextProcessors::messaging',
);

/**
 * List of template loader classes.
 */
$config->template_loaders = array(
	'osmf\ApplicationsTemplateLoader',
	'osmf\PathTemplateLoader',
);

/**
 * List of directories to search for templates when usign the 
 * osmf\PathTemplateLoader template loader implementation.
 */
$config->template_search_dirs = array(
	__DIR__ . DIRECTORY_SEPARATOR . 'templates',
);

/**
 * Base routes definition file.
 */
$config->urlconf = __DIR__ . DIRECTORY_SEPARATOR . 'urls.xml';

/**
 * Path in the URL where the application is hosted.
 */
$config->base_url = '/~garetjax/osms-root/htdocs/';

/**
 * Name of the url to which a user should be redirected after login.
 */
$config->login_redirect_url = 'index';

/**
 * Define here the database connection settings. The first entry in
 * this array will also be considered the default database for when
 * no name is provided.
 */
$config->databases = array(
	'default' => array(
		'type' => 'pgsql',
		'host' => '127.0.0.1',
		'name' => 'osms',
		'user' => 'osms',
		'pass' => '123456osms',
	),
	'admin' => array(
		'type' => 'pgsql',
		'host' => '127.0.0.1',
		'name' => 'osms',
		'user' => 'osms_admin',
		'pass' => '123456',
	),
);

/**
 * The model to use for retrieving user data.
 */
$config->user_model = '\osms\auth\models\User';
$config->user_dbconf = 'admin';

/**
 * Session management setup
 */
$config->session = array(
	'name' => 'session',
	'lifetime' => 60 * 60, // 1 hour
	'regenerate' => 10, // Number of requests before a new session
	                    // ID is generated. Set to 0 to disable.
);

/**
 * Directory where uploaded attachments have to be saved.
 */
$config->upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';


$config->log_dir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
$config->log_severity = 'INFO';

$config->captcha = array(
	'resources_path' => __DIR__ . DIRECTORY_SEPARATOR . 'captcha',
);

/**
 * Minimum interval between two messages from the same user.
 * Maximum is 23h59
 */
$config->message_flood_limit = 60;


$config->messages_per_page = 10;
