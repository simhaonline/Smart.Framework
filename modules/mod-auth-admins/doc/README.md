
# AuthAdmins: a general API for implement multi-account authentication in Smart.Framework admin area (admin.php)


# Implementing multi-account Admin Auth (admin.php area authentication) for Smart.Framework

## Add the following line of code in modules/app/app-auth-admin.inc.php
```php
if((!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) OR (!class_exists('\\SmartModExtLib\\AuthAdmins\\AuthAdminsHandler'))) {
	http_response_code(500);
	die(SmartComponents::http_error_message('500 Internal Server Error', 'A required module is missing: `mod-auth-admins` ...'));
} //end if
\SmartModExtLib\AuthAdmins\AuthAdminsHandler::Authenticate(
	false // enforce SSL: TRUE/FALSE
);
```
## Add the following setup lines in etc/config-admin.php
```php
define('APP_AUTH_ADMIN_USERNAME', 'admin');
define('APP_AUTH_ADMIN_PASSWORD', 'the-password');
define('APP_AUTH_PRIVILEGES', '<admin>,<custom-priv1>,...,<custom-privN>');
$configs['app-auth']['adm-namespaces'] = [
	'Admins Manager' => 'admin.php?page=auth-admins.manager.stml',
	// ...
];
```


# Implementing single-account (Simple) Admin Auth (admin.php area authentication) for Smart.Framework


## Add the following line of code in modules/app/app-auth-admin.inc.php
```php
if((!SmartAppInfo::TestIfModuleExists('mod-auth-admins')) OR (!class_exists('\\SmartModExtLib\\AuthAdmins\\SimpleAuthAdminsHandler'))) {
	http_response_code(500);
	die(SmartComponents::http_error_message('500 Internal Server Error', 'A required module is missing: `mod-auth-admins` ...'));
} //end if
\SmartModExtLib\AuthAdmins\SimpleAuthAdminsHandler::Authenticate(
	false // enforce SSL: TRUE/FALSE
);
```
## Add the following setup lines in etc/config-admin.php
```php
define('APP_AUTH_ADMIN_USERNAME', 'admin');
define('APP_AUTH_ADMIN_PASSWORD', 'pass');
```

