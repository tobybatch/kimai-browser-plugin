<?php

/*
 * This file is part of the DemoBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\BrowserPluginBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class PageLoadSubscriber
 * @package KimaiPlugin\BrowserPluginBundle\EventSubscriber
 */
/*
 * kernel.controller
 * kernel.request
 * kernel.view
 */
class PageLoadSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 100],
        ];
    }

    // http://localhost:8001/en/timesheet/create?source=https%3A%2F%2Fgithub.com%2Ftobybatch%2Fkimai2%2Fissues%2F235
    public function onController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $source = $request->query->get("source");

        if (!empty($source)) {
            $url = parse_url($source);
            if ($url['host'] === "github.com") {
                $tags = $this->makeTagsFromGithub($url);
                if (count($tags)) {
                    // Try and look up client, project and issue

                    // Collapse the tags array into tag values
                    array_walk($tags, function(&$a, $b) { $a = "$b-$a"; });
                    $request->query->set("tags", implode(",", $tags));
                }
            }
        }
    }

    private function makeTagsFromGithub(array $url): array {
        $parts = explode("/", trim($url['path'], "/"));
        $tags = [];
        if (count($parts) > 0) {
            $tags["client"] = $parts[0];
        }
        if (count($parts) > 1) {
            $tags["project"] = $parts[1];
        }
        if (count($parts) > 3 && $parts[2] === "issues") {
            $tags["issue"] = $parts[3];
        }
        return $tags;
    }
}
