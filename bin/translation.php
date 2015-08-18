<?php
use Zend\Console\Console;
use Zend\Console\ColorInterface;
use DasRed\Zend\Console\Getopt;
use DasRed\Translation\Version;
use DasRed\Translation\Command\Factory;
use DasRed\Translation\Command\Exception\InvalidArguments;

set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext)
{
	throw new Exception($errstr, $errno);
});

require_once __DIR__ . '/../autoload.php';

// consoleoptions
$console = Console::getInstance();
$opt = (new Getopt([
	'help|h' => 'Display this help message',
]))->setOptions([
	Getopt::CONFIG_CUMULATIVE_PARAMETERS => true
]);

$message = 'operation' . PHP_EOL;
$message .= PHP_EOL;

$message .= $console->colorize('translation Operations:', ColorInterface::YELLOW) . PHP_EOL;
$message .= $console->colorize(' translation sort <path>', ColorInterface::GREEN) . '   sorts the translations keys in the files.' . PHP_EOL;

$message .= PHP_EOL;
$message .= $console->colorize('log Operations:', ColorInterface::YELLOW) . PHP_EOL;
$message .= $console->colorize(' parse <logFile>', ColorInterface::GREEN) . '              parse a log file written with \DasRed\Translation\Logger\Formatter.' . PHP_EOL;
$message .= $console->colorize(' toCsv <logFile> <csvFile>', ColorInterface::GREEN) . '    parse a log file written with \DasRed\Translation\Logger\Formatter and write to CSV.' . PHP_EOL;
$message .= $console->colorize(' toExcel <logFile> <xlsFile>', ColorInterface::GREEN) . '  parse a log file written with \DasRed\Translation\Logger\Formatter and write to XLS.' . PHP_EOL;

try
{
	$opt->parse();

	if ($opt->help)
	{
		throw new \Exception('wants help');
	}

	if (!$opt->version && count($opt->getRemainingArgs()) < 2)
	{
		throw new \Exception('missing remaining args');
	}
}
catch (\Exception $exception)
{
	echo $opt->getUsageMessage($message);
	exit(1);
}

// version
if ($opt->version)
{
	$console->writeLine('Translation - ' . basename($_SERVER['argv'][0], '.php') . ' ' . (new Version())->get() . ' by Marco Starker');
	exit(0);
}

try
{
	if ((new Factory($console))->factory($opt->getRemainingArgs())->execute() === false)
	{
		$console->writeLine('Operation failed.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
		exit(1);
	}
}
catch (InvalidArguments $exception)
{
	$console->writeLine('Invalid arguments for operation.', ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
	echo PHP_EOL . $opt->getUsageMessage($message);
	exit(1);
}
catch (\Exception $exception)
{
	$console->writeLine($exception->getMessage(), ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED);
	exit(1);
}

exit(0);