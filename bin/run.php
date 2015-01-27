#!/usr/bin/env php
<?php
if (!$loader = include __DIR__ . '/../vendor/autoload.php')
{
    die('You must set up the project dependencies.');
}

defined('PATH_ROOT') or define('PATH_ROOT', __DIR__ . '/..');
defined('PATH_TEMPLATES') or define('PATH_TEMPLATES', PATH_ROOT . '/templates');

$app = new \Cilex\Application('Cilex');
$app->command(new \Cilex\Command\CodeGenCommand());
$app->run();
