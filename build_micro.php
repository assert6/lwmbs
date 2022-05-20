#!php
<?php
/**
 * Copyright (c) 2022 Yun Dou <dixyes@gmail.com>
 *
 * lwmbs is licensed under Mulan PSL v2. You can use this
 * software according to the terms and conditions of the
 * Mulan PSL v2. You may obtain a copy of Mulan PSL v2 at:
 *
 * http://license.coscl.org.cn/MulanPSL2
 *
 * THIS SOFTWARE IS PROVIDED ON AN "AS IS" BASIS,
 * WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO NON-INFRINGEMENT,
 * MERCHANTABILITY OR FIT FOR A PARTICULAR PURPOSE.
 *
 * See the Mulan PSL v2 for more details.
 */

spl_autoload_register(function ($class) {
    if (strpos($class, '\\') !== false) {
        // never here
        throw new Exception('???');
    }

    $osDir = match (PHP_OS_FAMILY) {
        'Windows', 'WINNT', 'Cygwin' => 'windows',
        'Linux' => 'linux',
        'Darwin' => 'macos',
    };

    if (str_starts_with($class, 'Lib') && $class !== 'Library') {
        $libName = substr($class, 3);
        $file = __DIR__ . "/$osDir/libraries/$libName.php";
        require $file;
        return;
    }

    $file = __DIR__ . "/$osDir/$class.php";
    if (is_file($file)) {
        require $file;
    } else {
        require __DIR__ . "/common/$class.php";
    }
});


function mian($argv): int
{
    Util::setErrorHandler();

    $allStatic = false;
    if (in_array('all-static', $argv, true)) {
        $allStatic = true;
    }

    $config = new Config($argv);

    $libNames = [
        'libssh2',
        'curl',
        'zlib',
        'brotli',
        'libiconv',
        'libffi',
        'openssl',
        'libzip',
        'bzip2',
        'nghttp2',
        'onig',
        'xz',
    ];

    $extNames = [
        'pdo',
        'phar',
        'mysqli',
        'pdo',
        'pdo_mysql',
        'mbstring',
        'mbregex',
        'session',
        'pcntl',
        'posix',
        'ctype',
        'fileinfo',
        'filter',
        'tokenizer',
        'curl',
        'ffi',
        'swow',
        'redis',
        'parallel',
        'sockets',
        'openssl',
        'zlib',
        'bz2',
    ];

    if ($allStatic) {
        unset($libNames[array_search('libffi', $libNames, true)]);
        unset($extNames[array_search('ffi', $extNames, true)]);
    }

    foreach ($libNames as $name) {
        $lib = new ("Lib$name")($config);
        $config->addLib($lib);
    }
    //var_dump(array_map(fn($x)=>$x->getName(),$config->makeLibArray()));

    foreach ($config->makeLibArray() as $lib) {
        $lib->prove();
    }

    foreach ($extNames as $name) {
        $ext = new Extension(name: $name, config: $config);
        $config->addExt($ext);
    }

    $build = new MicroBuild($config);
    $build->build($allStatic);

    return 0;
}

exit(mian($argv));
