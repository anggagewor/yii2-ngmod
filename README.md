# yii2-ngmod

[![Latest Version](https://img.shields.io/github/release/anggagewor/yii2-ngmod.svg?style=flat-square)](https://github.com/anggagewor/yii2-ngmod/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/anggagewor/yii2-ngmod/master.svg?style=flat-square)](https://travis-ci.org/anggagewor/yii2-ngmod)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/anggagewor/yii2-ngmod.svg?style=flat-square)](https://scrutinizer-ci.com/g/anggagewor/yii2-ngmod/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/anggagewor/yii2-ngmod.svg?style=flat-square)](https://scrutinizer-ci.com/g/anggagewor/yii2-ngmod)
[![Total Downloads](https://img.shields.io/packagist/dt/anggagewor/yii2-ngmod.svg?style=flat-square)](https://packagist.org/packages/anggagewor/yii2-ngmod)

Yii2 Next Generation Mod

# Disclaimer

Mod ini adalah gabungan dari beberapa package yang kami kumpulkan dan convert menjadi satu, jika kalian penulis dari library yang ada disini dan ingin di hapus, silakan untuk menghubungi kami untuk kami takedown library nya.

Berikut adalah list library yang kami pakai dan kami modifikasi agar sesuai dengan kebutuhan kami,

- [yii2-configloader](https://github.com/codemix/yii2-configloader) `BSD-3-Clause license`
- [yii2-gii-migration](https://github.com/yiimaker/yii2-gii-migration) `BSD-3-Clause license`
- [yii2-module-autoloader](https://github.com/bmsrox/yii2-module-autoloader/blob/master/ModuleLoader.php) `MIT`

## Install

Via Composer

```bash
$ composer require anggagewor/yii2-ngmod
```
## Usage 

### Initialize Yii environment and load configuration

Let's finally show a full example that demonstrates how to use all the mentioned features in one go. A typical setup will use the following files:

`config/web.php`

```php
<?php
$config = [
    'bootstrap'  => [
	    // other bootstrap 
	    'Anggagewor\Ngmod\Bootstraps\ModuleLoader' 
	],
];
```

`config/console.php`

```php
<?php
$config = [
    'bootstrap'  => [
	    // other bootstrap 
	    'Anggagewor\Ngmod\Bootstraps\ModuleLoader' 
	],
];
```

`.env`

```env
YII_DEBUG=1
YII_ENV=dev

DB_DSN=mysql:host=db.example.com;dbname=web
DB_USER=pengguna
DB_PASSWORD=rahasia
```

`config/web.php`

```php
<?php
/* @var Anggagewor\Ngmod\Config $this */
return [
    'components' => [
        
        'db' => [
            'class'     => self::env('DB_CLASS', 'yii\db\Connection'),
            'dsn'       => self::env('DB_DSN', 'mysql:host=db;dbname=web'),
            'username'  => self::env('DB_USER', 'web'),
            'password'  => self::env('DB_PASSWORD', 'web'),
        ],
    ]
];
```
`config/console.php`

```php
<?php
/* @var Anggagewor\Ngmod\Config $this */

$web = $this->web();
return [
    // ...
    'components' => [
        'db' => $web['components']['db'],
    ]
];
```

`web/index.php`

```php
<?php
use Anggagewor\Ngmod\Config;

require(__DIR__ . '/../vendor/autoload.php');
$config = new Config(__DIR__ . '/..');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::createObject('yii\web\Application', [$config->web()])->run();
```

`yii`

```php
<?php
use Anggagewor\Ngmod\Config;

require(__DIR__ . '/vendor/autoload.php');
$config = Config::bootstrap(__DIR__);
$application = Yii::createObject('yii\console\Application', [$config->console()]);
exit($application->run());
```

### For Gii Generator

`config/web.php`

```php

    $config[ 'modules' ][ 'gii' ] = [
        'class'      => 'yii\gii\Module',
        'generators' => [
            'model'  => 'Anggagewor\Ngmod\Generator\model\Generator',
            'module' => 'Anggagewor\Ngmod\Generator\module\Generator',
        ]
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Angga Purnama](https://github.com/anggagewor)
- [All Contributors](../../contributors)

## Security Vulnerabilities

If you discover a security vulnerability within yii2-ngmod, please send an e-mail to Angga Purnama via [anggagewor@gmail.com](mailto:anggagewor@gmail.com). All security vulnerabilities will be promptly addressed.

## License

yii2-ngmod is open-sourced software licensed under the [MIT license](LICENSE.md).
