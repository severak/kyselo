<?php
namespace severak\cligen;

class app
{
    public static function parseArgv($args)
    {
        $params = [];
        foreach ($args as $arg) {
            // TODO - parse argv in better way, this is too strict (but somehow works)
            $matches = [];
            if (preg_match('~--([\w]+)=(.+)~', $arg, $matches)) {
                $params[$matches[1]] = $matches[2];
            } elseif (preg_match('~--([\w]+)~', $arg, $matches)) {
                $params[$matches[1]] = true;
            } else {
                $params['_'][] = $arg;
            }
        }
        return $params;
    }

    /**
     * @param \ReflectionParameter[] $params
     * @param array                  $args
     */
    public static function makeParams($params, $args)
    {
        $funParams = [];
        foreach ($params as $param) {
            $paramName = $param->getName();
            if (isset($args[$paramName])) {
                $funParams[] = $args[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $funParams[] = $param->getDefaultValue();
            } else {
                echo sprintf('Missing parameter %s!', $paramName) . PHP_EOL . PHP_EOL;
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
            // TODO - implementovat subcomandy
        } else {
            $params = self::parseArgv($args);
            $function = new \ReflectionFunction($app);

            if (isset($params['help'])) {
                $help = new \severak\cligen\help($function->getDocComment());
                echo $help->getUsage();
                exit(0);
            } else {
                $funParams = self::makeParams($function->getParameters(), self::parseArgv($args));
                if (!$funParams) {
                    $help = new \severak\cligen\help($function->getDocComment());
                    echo $help->getUsage();
                    exit(0);
                }
                $function->invokeArgs($funParams);
            }
        }
        exit(1);
    }
}