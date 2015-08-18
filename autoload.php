<?php
$autoloader = null;
foreach ([
	// Local install
	__DIR__ . '/vendor/autoload.php',
	// Root project is current working directory
	getcwd() . '/vendor/autoload.php',
	// Relative to composer install
	__DIR__ . '/../../autoload.php'
] as $autoloadFile)
{
	if (file_exists($autoloadFile) === true)
	{
		$autoloader = require $autoloadFile;
		break;
	}
}

// autoload not found... abort
if ($autoloader === null)
{
	fwrite(STDERR, 'Unable to setup autoloading; aborting\n');
	exit(2);
}
