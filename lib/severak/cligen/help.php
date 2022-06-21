<?php
namespace severak\cligen;

class help
{
    public $paramHelp = [];
    public $types = [];
    public $shortDesc = '';
    public $longDesc = '';

    public function __construct($docComment)
    {
        // TODO - better parsing of doc comments, this is toooo hackish
        foreach (explode(PHP_EOL, $docComment) as $ord=>$line) {
            $matches = [];
            if (preg_match('~\s*\*\s*(.+)~', $line, $matches)) {
                $docLine = $matches[1];

                $matches2 = [];
                if (preg_match('~@param\s+(\w+)\s+\$(\w+)\s+(.+)~', $docLine, $matches2)) {
                    $this->types[$matches2[2]] = $matches2[1];
                    $this->paramHelp[$matches2[2]] = $matches2[3];
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
    }

    public function getUsage()
    {
        $usage =  $this->longDesc . PHP_EOL;
        foreach ($this->paramHelp as $paramName=>$help) {
            // TODO - odmezerovat místo tabu
            $usage .= '--' . $paramName . "\t" . $help . PHP_EOL;
        }
        return $usage;
    }

}