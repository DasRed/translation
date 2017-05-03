# General
This is a simple translation engine. 

# Installation
```bash
composer install dasred/translation
```

# Usage
## File structure

The [translator](src/Translator.php) requires at least 2 arguments. The first argument is the current locale. The second argument is the path, in which are located the translation files.

The following structure for translation files in the given directory will be used.

```text
given translation directory
|--- de_DE
       |--- general.php
       |--- account.php
       |--- other.php
|--- en_GB
       |--- general.php
       |--- account.php
       |--- other.php
```
The third argument defines the default locale of the [translator](src/Translator.php). The default locale will be used as fallback. If a translation key not defined in the current locale, the key will be searched in the default locale.

Every translation file must return an array key value list. The key is a part of the translation key and the value is the translation.

The following example show the file de_DE/general.php
```php
<?php
return [
    'name' => 'Name',
    'hello' => 'Hallo',
    'world' => 'Welt'
];
```

The following example show the file en_GB/general.php
```php
<?php
return [
    'name' => 'name',
    'hello' => 'hello',
    'world' => 'world'
];
```

## Translation key
The translation key defines the translation file and the array key in the translation file. For example
```text
general.hello <-- will be match for de_DE with "Hallo"
general.hello <-- will be match for en_GB with "hello"
```

Example of requesting programmatically a translation key.
```php
$translator = new \DasRed\Translation\Translator('de_DE', __DIR__ . '/translations', 'en_GB');
echo $translator->__('general.hello'); // echos "Hallo"
echo $translator->__('general.hello', [], 'en_GB'); // echos "hello"
echo $translator->__('general.hello', [], 'fr_FR'); // echos "hello" from en_GB
```

## Placeholders

Translation values can have placeholder. Every placeholder is embeded in [ and ]. The placeholder key is case insensitive. 

The following example show the file en_GB/general.php with placeholders.
```php
<?php
return [
    'name' => 'name',
    'hello' => 'hello',
    'world' => 'world',
    'seconds' => '[SECONDS] seconds',
    'secondsAbbr' => '[SECONDS]s',
];
```

Example of requesting programmatically a translation key with parameters.
```php
$translator = new \DasRed\Translation\Translator('en_GB', __DIR__ . '/translations', 'en_GB');
echo $translator->__('general.seconds'); // echos "[SECONDS] seconds"
echo $translator->__('general.seconds', ['seconds' => 10]); // echos "10 seconds"
echo $translator->__('general.secondsAbbr', ['seconds' => 10]); // echos "10s"
echo $translator->__('general.secondsAbbr', ['seconds' => number_format(10.00020200202, 2, '.', ',')]); // echos "10.00s"
```

## BBCodes
The [translator](src/Translator.php) supports the [BBCode Parser](https://github.com/DasRed/bbCodeParser) to parse BBCode in the translation values.

The following example show the file en_GB/general.php with BBCode.
```php
<?php
return [
    'name' => 'name',
    'hello' => 'hello',
    'world' => 'world',
    'seconds' => '[b][SECONDS][/b] seconds',
    'secondsAbbr' => '[b][SECONDS][/b]s',
];
```

Example of requesting programmatically a translation key with parameters.
```php
$translator = new \DasRed\Translation\Translator('en_GB', __DIR__ . '/translations', 'en_GB', null, new \DasRed\Parser\BBCode());
echo $translator->__('general.seconds'); // echos "<strong>[SECONDS]</strong> seconds"
echo $translator->__('general.seconds', ['seconds' => 10]); // echos "<strong>10</strong> seconds"
echo $translator->__('general.secondsAbbr', ['seconds' => 10]); // echos "<strong>10</strong>s"
echo $translator->__('general.secondsAbbr', ['seconds' => number_format(10.00020200202, 2, '.', ',')]); // echos "<strong>10.00</strong>s"
```
