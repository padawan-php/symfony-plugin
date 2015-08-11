<?php

namespace Mkusher\PadawanSymfony;

use Parser\UseParser;
use Complete\Resolver\TypeResolveEvent;
use Entity\FQCN;
use PhpParser\Node\Arg;
use PhpParser\Node\Scalar\String_;
use Entity\Project;

class TypeResolver
{
    public function __construct(
        UseParser $useParser
    ) {
        $this->useParser = $useParser;
    }

    public function handleParentTypeEvent(TypeResolveEvent $e)
    {
        $this->parentType = $e->getType();
    }

    public function handleTypeResolveEvent(TypeResolveEvent $e, Project $project)
    {
        /** @var \Entity\Chain\MethodCall */
        $chain = $e->getChain();
        if ($chain->getType() === 'method' && count($chain->getArgs()) > 0) {
            $firstArg = array_pop($chain->getArgs())->value;
            if ($firstArg instanceof String_) {
                $serviceName = $firstArg->value;
                $container = $project->getPlugin("padawan-symfony");
                if (array_key_exists($serviceName, $container)) {
                    $fqcn = $this->useParser->parseFQCN($container[$serviceName]);
                    $e->setType($fqcn);
                }
            }
        }
    }
    public function getParentType()
    {
        return $this->parentType;
    }

    /** @var UseParser */
    private $useParser;
    private $parentType;
}
