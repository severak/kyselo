<?php
namespace severak\cligen;

// TODO - get inspiration here:
// - https://github.com/vlang/v/tree/master/vlib/flag
// - https://modules.vlang.io/flag.html

class app
{
    public static function parseArgv($args)
    {
        $params = [];
        while ($arg = array_shift($args)) {
            // TODO - parse argv in better way, this is too strict (but somehow works)
            $matches = [];
            if (preg_match('~--?([\w]+)=(.+)~', $arg, $matches)) {
                $params[$matches[1]] = $matches[2];
            } elseif (preg_match('~--?([\w]+)~', $arg, $matches)) {
                $params[$matches[1]] = true;
                if (isset($args[0]) && substr($args[0], 0, 1)!='-') {
                   $params[$matches[1]] = array_shift($args);
                }
            } else {
                $params['_'][] = $arg;
            }
        }
        return $params;
    }

    /**
     * @param \ReflectionParameter[] $params
     * @param array $args
     */
    public static function makeParams($params, $args)
    {
        $funParams = [];
        foreach ($params as $param) {
            $paramName = $param->getName();
            if (isset($args[$paramName])) {
                // TODO  - cry when we got true but true it's not expected
                $funParams[] = $args[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $funParams[] = $param->getDefaultValue();
            } else {
                return false;
            }
        }
        return $funParams;
    }

    public static function run($app='main')
    {
        global $argv;
        $args = $argv;
        $scriptName = array_shift($args);
        //var_dump($args);

        if (is_object($app)) {
            $object = new \ReflectionObject($app);
            $mainHelp = new help($object->getDocComment());

            if (empty($args) || (isset($args[0]) && $args[0]=='--help')) {
                echo $mainHelp->listSubcommands($object, $scriptName);
                exit(1);
            }

            $subcommand = array_shift($args);
            if ($object->hasMethod($subcommand)) {
                // is valid subcommand
                $function = $object->getMethod($subcommand);
                $help = new help($function->getDocComment(), $function->getParameters());
                $funParams = self::makeParams($function->getParameters(), self::parseArgv($args));
                if ($funParams===false) {
                    echo $help->getUsage($scriptName. ' ' . $subcommand);
                    exit(1);
                }
                $function->invokeArgs($app, $funParams);
                exit(0);
            } else {
                echo $mainHelp->listSubcommands($object, $scriptName);
                exit(1);
            }
        } else {
            $params = self::parseArgv($args);
            $function = new \ReflectionFunction($app);
            $help = new help($function->getDocComment(), $function->getParameters());

            if (isset($params['help'])) {
                echo $help->getUsage($scriptName);
                exit(0);
            } else {
                $funParams = self::makeParams($function->getParameters(), self::parseArgv($args));
                if ($funParams===false) {
                    echo $help->getUsage($scriptName);
                    exit(1);
                }
                $function->invokeArgs($funParams);
                exit(0);
            }
        }
    }
}
