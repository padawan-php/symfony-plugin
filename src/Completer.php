<?php

namespace Mkusher\PadawanSymfony;

use Complete\Completer\CompleterInterface;
use Entity\Completion\Entry;
use Entity\Project;
use Entity\Completion\Context;

class Completer implements CompleterInterface
{
    public function getEntries(Project $project, Context $context)
    {
        $services = $project->getPlugin('padawan-symfony');
        return array_map(function ($serviceName) {
            return new Entry(
                sprintf('"%s"', $serviceName),
                "",
                "",
                $serviceName
            );
        }, array_keys($services));
    }
}
