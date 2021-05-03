<?php

/*
 * This file is part of the DemoBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\BrowserPluginBundle\EventSubscriber;

use App\Repository\ActivityRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProjectRepository;
use App\Repository\TagRepository;
use App\Repository\TimesheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
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
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var CustomerRepository
     */
    private CustomerRepository $customerRepository;
    /**
     * @var ProjectRepository
     */
    private ProjectRepository $projectRepository;
    /**
     * @var ActivityRepository
     */
    private ActivityRepository $activityRepository;
    /**
     * @var TagRepository
     */
    private TagRepository $tagRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var TimesheetRepository
     */
    private TimesheetRepository $timesheetRepository;

    public function __construct(
        LoggerInterface $logger,
        CustomerRepository $customerRepository,
        ProjectRepository $projectRepository,
        ActivityRepository $activityRepository,
        TagRepository $tagRepository,
        TimesheetRepository $timesheetRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->projectRepository = $projectRepository;
        $this->activityRepository = $activityRepository;
        $this->tagRepository = $tagRepository;
        $this->entityManager = $entityManager;
        $this->timesheetRepository = $timesheetRepository;
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
                    // Try and look up project and issue
                    $projectId = $this->getTopProject("project-" . $tags['project']);
                    if ($projectId) {
                        $request->query->set("project", $projectId);
                    }
                    $projectId = $this->getTopActivity("issue-" . $tags['issue']);
                    if ($projectId) {
                        $request->query->set("activity", $projectId);
                    }
                    // Collapse the tags array into tag values
                    array_walk(
                        $tags,
                        function (&$a, $b) {
                            $a = "$b-$a";
                        }
                    );
                    $request->query->set("tags", implode(",", $tags));
                }
            }
        }
    }

    private function getTopProject(string $tagName)
    {
        $sql = 'SELECT ts.project_id, count(*) c FROM kimai2_timesheet ts
         INNER JOIN kimai2_timesheet_tags tt ON ts.id=tt.timesheet_id
         INNER JOIN kimai2_tags tags ON tt.tag_id=tags.id
         WHERE tags.name = :tagName
         GROUP BY project_id
         ORDER BY c';
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $statement->execute(["tagName" => $tagName]);
        if ($row = $statement->fetch()) {
            return $row['project_id'];
        }
    }

    private function getTopActivity(string $tagName)
    {
        $sql = 'SELECT ts.activity_id, count(*) c FROM kimai2_timesheet ts
         INNER JOIN kimai2_timesheet_tags tt ON ts.id=tt.timesheet_id
         INNER JOIN kimai2_tags tags ON tt.tag_id=tags.id
         WHERE tags.name = :tagName
         GROUP BY activity_id
         ORDER BY c';
        $statement = $this->entityManager->getConnection()->prepare($sql);
        $statement->execute(["tagName" => $tagName]);
        if ($row = $statement->fetch()) {
            return $row['activity_id'];
        }
        /*
         SELECT ts.activity_id, count(*) c FROM kimai2_timesheet ts
         INNER JOIN kimai2_timesheet_tags tt ON ts.id=tt.timesheet_id
         INNER JOIN kimai2_tags tags ON tt.tag_id=tags.id
         WHERE tags.name="issue-235"
         GROUP BY activity_id
         ORDER BY c
        */
    }

    private function makeTagsFromGithub(array $url): array
    {
        $parts = explode("/", trim($url['path'], "/"));
        $tags = [];
        if (count($parts) > 1) {
            $tags["project"] = $parts[1];
        }
        if (count($parts) > 3 && $parts[2] === "issues") {
            $tags["issue"] = $parts[3];
        }
        return $tags;
    }
}
