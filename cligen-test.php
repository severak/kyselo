<?php
require __DIR__ . "/lib/flight/autoload.php";
\flight\core\Loader::addDirectory(__DIR__ . '/lib' );

// tests of command line parsing and stuff

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

/**
 * @param int $a number A
 * @param int $b number B
 */


function plus($a, $b=1)
{
    echo '=' . ($a + $b);
}

/**
 * Easy to use git wrapper.
 *
 * Use with caution.
 */
class ginger
{
    /**
     * Prints info about repository.
     */
    public static function look()
    {
        echo 'looking...';
    }

    /**
     * Switches to branch.
     *
     * Does something else also...
     *
     * @param string $branch Branch name.
     */
    public static function switch($branch)
    {
        echo 'switching to ' . $branch;
    }
}

/**
 * Encodes password.
 *
 * @param string $passwd Password to encode.
 */
function main($passwd)
{
    echo 'hashing '. $passwd . PHP_EOL;
    echo password_hash(trim($passwd), PASSWORD_DEFAULT);
}

    // TODO - otestovat subcommandy

\severak\cligen\app::run();
