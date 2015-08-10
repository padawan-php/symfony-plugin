<?php

namespace Mkusher\PadawanSymfony;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Complete\Resolver\NodeTypeResolver;
use Complete\CompleteEngine;
use Parser\UseParser;
use Entity\FQCN;

class Plugin
{
    public function __construct(
        EventDispatcher $dispatcher,
        TypeResolver $resolver,
        Completer $completer
    ) {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
        $this->completer = $completer;
    }

    public function init()
    {
        //$this->dispatcher->addListener(
            //NodeTypeResolver::BLOCK_START,
            //[$this->resolver, 'handleParentTypeEvent']
        //);
        //$this->dispatcher->addListener(
            //NodeTypeResolver::BLOCK_END,
            //[$this->resolver, 'handleTypeResolveEvent']
        //);
        $this->dispatcher->addListener(
            CompleteEngine::CUSTOM_COMPLETER,
            [$this, 'handleCompleteEvent']
        );
    }

    public function handleCompleteEvent($e)
    {
        $context = $e->context;
        if ($context->isMethodCall()) {
            list($type, $isThis, $types, $workingNode) = $context->getData();
            $fqcn = array_pop($types);
            printf("Symfony fqcn: %s\n", $fqcn->toString());
        }
    }

    /** @var Completer */
    private $completer;
    /** @var TypeResolver */
    private $resolver;
    /** @var EventDispatcher */
    private $dispatcher;
}
