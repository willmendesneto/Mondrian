<?php

/*
 * Standalone Console
 *
 * Uses the Symfony Console Component (which is great !)
 */

namespace Trismegiste\Mondrian;

require_once 'vendor/autoload.php';

use Trismegiste\Mondrian\Command;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Command\DigraphCommand());
$application->add(new Command\UsedCentralityCommand());
$application->add(new Command\DependCentralityCommand());
$application->add(new Command\HiddenCouplingCommand());
$application->add(new Command\SpaghettiCommand());
$application->add(new Command\CyclicCommand());
$application->add(new Command\LiskovCommand());
$application->add(new Command\RefactorCommand());
$application->add(new Command\BadInterfaceCommand());
$application->run();