<?php

namespace Mkusher\PadawanSymfony;

class IndexGenerator
{
    public function handleAfterGenerationEvent($event)
    {
        $project = $event->getProject();
        $output = [];
        $container = [];
        try {
            exec(sprintf("%s/app/console container:debug", $project->getRootFolder()), $output);
            $output = array_slice($output, 2);
            array_pop($output);
            foreach ($output as $containerStr) {
                $service = explode(" ", $containerStr);
                preg_match('/^\s*(\S*)\s*(\S*)$/i', $containerStr, $matches);
                if ($matches && count($matches) === 3) {
                    $container[$matches[1]] = $matches[2];
                }
            }
            $project->addPlugin('padawan-symfony', $container);
        } catch (\Exception $e) {
            return;
        }
    }
}
