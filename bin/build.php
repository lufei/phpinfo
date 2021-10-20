<?php

$dirname = dirname(__DIR__);
$pharFile = $dirname . '/phpinfo.phar';

if (file_exists($pharFile)) {
    unlink($pharFile);
}

function getStub()
{
    $stub = <<<'EOF'
#!/usr/bin/env php
<?php
if (!class_exists('Phar')) {
    echo 'PHP\'s phar extension is missing. phpinfo requires it to run. Enable the extension or recompile php without --disable-phar then try again.' . PHP_EOL;
    exit(1);
}
Phar::mapPhar('phpinfo.phar');
EOF;

    return $stub . <<<'EOF'
require 'phar://phpinfo.phar/bin/phpinfo';
__HALT_COMPILER();
EOF;
}

$phar = new Phar($pharFile, 0, 'phpinfo.phar');
$phar->startBuffering();
$phar->buildFromDirectory($dirname);
$phar->delete('bin/build.php');
$phar->delete('.gitignore');
$phar->delete('phpinfo');
$content = file_get_contents($dirname . '/phpinfo');
$content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
$phar->addFromString('bin/phpinfo', $content);
$phar->setStub(getStub());
$phar->stopBuffering();
