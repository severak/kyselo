<?php
namespace severak\cligen;

class help
{
    public $paramHelp = [];
    public $types = [];
    public $defaults = [];
    public $shortDesc = '';
    public $longDesc = '';

    /**
     * help constructor.
     * @param $docComment
     * @param \ReflectionParameter[]|null $paramsDef
     * @throws \Exception
     */
    public function __construct($docComment, $paramsDef=null)
    {
        // TODO - maybe change constructor to accept reflectionmethod or reflection function
        if (empty($docComment)) {
            throw new \Exception('Please define some help text while defining cligen app.');
        }

        // TODO - better parsing of doc comments, this is toooo hackish
        foreach (explode("\n", $docComment) as $ord=>$line) {
            $matches = [];
            if (preg_match('~\s*\*\s*(.+)~', $line, $matches)) {
                $docLine = $matches[1];

                $matches2 = [];
                if (preg_match('~@param\s+(\w+)\s+\$(\w+)\s+(.+)~', $docLine, $matches2)) {
                    $this->types[$matches2[2]] = $matches2[1];
                    $this->paramHelp[$matches2[2]] = trim($matches2[3]);
                } elseif (strpos($docLine, '@param')!==false){
                    // TODO - implement better exception throwing with link to bug in code
                    throw new \BadMethodCallException('Function/method doc not properly encoded: ' . PHP_EOL . $docComment);
                } elseif (in_array($docLine, ['*', '/'])) {
                    // ignore these
                } elseif ($ord==1) {
                    $this->shortDesc = $docLine;
                    $this->longDesc = $docLine . PHP_EOL;
                } else {
                    $this->longDesc = $this->longDesc . $docLine . PHP_EOL;
                }
            }
        }

        if ($paramsDef) {
            foreach ($paramsDef as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $this->defaults[$param->getName()] = $param->getDefaultValue();
                }
            }
        }
    }

    public function getShortDesc()
    {
        return $this->shortDesc;
    }

    protected function _printable($value)
    {
        if (is_null($value)) {
            return 'NULL';
        } elseif ($value===true) {
            return 'TRUE';
        } elseif ($value===false) {
            return 'FALSE';
        }
        return strval($value);
    }

    public function getUsage($appName='')
    {
        $usage = '';

        if ($appName) {
            $usage = 'Usage: ' . $appName . ' ';

            foreach ($this->paramHelp as $paramName=>$help) {
                if (isset($this->defaults[$paramName])) {
                    $usage .= '[--' . $paramName . '] ';
                } else {
                    $usage .= '--' . $paramName . ' ';
                }
            }

            $usage .= PHP_EOL. PHP_EOL;
        }

        if (!empty($this->paramHelp)) {
            $lengths = array_map('strlen', array_keys($this->paramHelp));
            $longest = max($lengths);

            $usage .=  $this->longDesc . PHP_EOL;
            foreach ($this->paramHelp as $paramName=>$help) {
                $default = '';
                if (isset($this->defaults[$paramName])) {
                    $default = ' (default: ' . $this->_printable($this->defaults[$paramName]) . ')';
                }

                $usage .= ' --' . $paramName . str_repeat(' ', $longest - strlen($paramName)) . " " . $help . $default .  PHP_EOL;
            }
        }

        return $usage;
    }


    public function listSubcommands(\ReflectionObject $object, $appName='')
    {
        $usage = $this->longDesc . PHP_EOL . PHP_EOL;

        if ($appName) {
            $usage .= 'Usage: ' . $appName . ' <subcommand>' . PHP_EOL . PHP_EOL;
        }

        $usage .= 'Possible subcommands: ' . PHP_EOL . PHP_EOL;

        $_methodNameLens = [];
        foreach ($object->getMethods() as $method) {
            $_methodNameLens[] = strlen($method->getName());
        }
        $longest = max($_methodNameLens);

        foreach ($object->getMethods() as $method) {
            $methodName = $method->getName();
            if (substr($methodName, 0, 2)=='__') {
                continue;
            }

            $methodHelp = new help($method->getDocComment());
            $usage .= '  ' . $methodName . str_repeat(' ', $longest - strlen($methodName)) . ' ' . $methodHelp->getShortDesc() . PHP_EOL;
        }

        return $usage;

    }

}
