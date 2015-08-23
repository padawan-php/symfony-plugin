<?php

namespace Mkusher\PadawanSymfony;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Complete\Resolver\NodeTypeResolver;
use Complete\Completer\CompleterFactory;
use Generator\IndexGenerator as Generator;
use Parser\UseParser;
use Entity\FQCN;
use Entity\Node\ClassData;

class Plugin
{
    public function __construct(
        EventDispatcher $dispatcher,
        TypeResolver $resolver,
        Completer $completer,
        IndexGenerator $generator
    ) {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
        $this->completer = $completer;
        $this->generator = $generator;
        $this->containerNames = [
            'Symfony\\Component\\DependencyInjection\\Container',
            'Symfony\\Component\\DependencyInjection\\ContainerInterface'
        ];
    }

    public function init()
    {
        $this->dispatcher->addListener(
            NodeTypeResolver::BLOCK_START,
            [$this->resolver, 'handleParentTypeEvent']
        );
        $this->dispatcher->addListener(
            'project.load',
            [$this, 'handleProjectLoadEvent']
        );
        $this->dispatcher->addListener(
            NodeTypeResolver::BLOCK_END,
            [$this, 'handleTypeResolveEvent']
        );
        $this->dispatcher->addListener(
            CompleterFactory::CUSTOM_COMPLETER,
            [$this, 'handleCompleteEvent']
        );
        $this->dispatcher->addListener(
            Generator::BEFORE_GENERATION,
            [$this->generator, 'handleAfterGenerationEvent']
        );
    }

    public function handleProjectLoadEvent($e)
    {
        $this->project = $e->project;
    }

    public function handleTypeResolveEvent($e)
    {
        $index = $this->project->getIndex();
        if ($this->checkForContainerClass($this->resolver->getParentType(), $index)) {
            $this->resolver->handleTypeResolveEvent($e, $this->project);
        }
    }
    public function handleCompleteEvent($e)
    {
        $context = $e->context;
        if ($context->isMethodCall()) {
            list($type, $isThis, $types, $workingNode) = $context->getData();
            if ($this->checkForContainerClass(array_pop($types), $e->project->getIndex())) {
                $e->completer = $this->completer;
            }
        }
    }
    protected function checkForContainerClass($fqcn, $index)
    {
        if (!$fqcn instanceof FQCN) {
            return false;
        }
        if (in_array($fqcn->toString(), $this->containerNames)) {
            return true;
        }
        $class = $index->findClassByFQCN($fqcn);
        if (!$class instanceof ClassData) {
            return false;
        }
        $parent = $class->getParent();
        if ($parent instanceof FQCN) {
            $parentFQCN = $parent;
        }
        if ($parent instanceof ClassData
            && $parent->fqcn instanceof FQCN
        ) {
            $parentFQCN = $parent->fqcn;
        }
        if (empty($parentFQCN) || !$parentFQCN instanceof FQCN) {
            return false;
        }
        $controller = 'Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller';
        if ($parentFQCN->toString() === $controller) {
            return true;
        }
        return false;
    }

    /** @var Completer */
    private $completer;
    /** @var TypeResolver */
    private $resolver;
    /** @var EventDispatcher */
    private $dispatcher;
    /** @var IndexGenerator */
    private $generator;
    private $containerNames;
    private $project;
}
