<?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'upgrade\builder') === 0) {
        $path = str_replace(array('upgrade\builder', '\\'), array('', DIRECTORY_SEPARATOR), $class) . '.php';

        include __DIR__ . '/src/' . $path;
    }
});
