<?php
require __DIR__ . "/lib/flight/autoload.php";
\flight\core\Loader::addDirectory(__DIR__ . '/lib' );

/**
 * Greets user.
 *
 * Maybe being somewhat verbose.
 *
 * @param string $name    Person to be greeted.
 * @param bool   $verbose Be a little verbose.
 */
function greet($name, $verbose=false)
{
    echo 'Hello ' . $name . ($verbose ? ', my lord' : '');
}

    /**
     * Computes something.
     *
     * @param int $bar First input.
     * @param int $baz Second input.
     */
    function foo($bar, $baz)
    {

    }

    // TODO - otestovat subcommandy

\severak\cligen\app::run('greet');