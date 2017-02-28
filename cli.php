<?php
define('ROOT',realpath(__DIR__).DIRECTORY_SEPARATOR);
define('DISPLAY_ERROR',false);

/**
 *  @const HTTP_RUN_CMD = true;
 *  @run cmd http protocol
 *  @test: http://mydomen.com/cli.php?cmd_run=randomVoteFile:10000:mmotop.txt or
 *        http://mydomen.com/cli.php?cmd_run=parserLoad:mmotop
 */

define('HTTP_RUN_CMD',false);

if(DISPLAY_ERROR === false) {
    if (!ini_get('display_errors')) {
        ini_set('display_errors', 1);
    }
    error_reporting(E_ALL|E_NOTICE|E_STRICT);
}else{
    ini_set('display_errors', 0);
    error_reporting(0);
}

require_once ROOT.'init/Autoload.php';

Autoload::register(ROOT);

use \cli\Parser as Pars;

Pars::$_ARGV = (PHP_SAPI == 'cli' or PHP_SAPI == 'cgi-fcgi' or PHP_SAPI == 'fpm-fcgi') ? (isset($argv)) ? $argv : false : false;

Pars::CliRun();