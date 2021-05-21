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
use App\Repository\Query\TimesheetQuery;
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

    public function onController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $source = $request->query->get("source");

        if (!empty($source)) {
            $this->logger->debug("source url detected", [$source]);
            $url = parse_url($source);
            if ($url['host'] === "github.com") {
                $request->query->set("description", $source);
                $tags = $this->makeTagsFromGithub($url);
                if (count($tags)) {
                    // Try and look up project and issue
                    $projectId = $this->getTopProject("project-" . $tags['project']);
                    if ($projectId) {
                        $request->query->set("project", $projectId);
                    }
                    $activityTags = [];
                    if (array_key_exists('issue', $tags)) {
                        $activityTags[] = "issue-" . $tags['issue'];
                    }
                    if (array_key_exists('project', $tags)) {
                        $activityTags[] = "project-" . $tags['project'];
                    }
                    $projectId = $this->getTopActivity($activityTags, $projectId);
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

    private function getTopProject(string $tagNames)
    {
        $timeSheets = $this->loadTimeSheetsByTag([$tagNames]);
        $projects = [];
        foreach ($timeSheets as $timesheet) {
            $project = $timesheet->getProject();
            $id = $project->getId();
            if (key_exists($id, $projects)) {
                $projects[$id] = $projects[$id] + 1;
            } else {
                $projects[$id] = 1;
            }
        }

        $projectId = null;
        $count = 0;
        foreach ($projects as $id => $project_count) {
            if ($project_count > $count) {
                $count = $project_count;
                $projectId = $id;
            }
        }

        return $projectId;
    }

    // project-kimai2, issue-235
    private function getTopActivity(array $tagNames, $project_id): ?int
    {
        // select count(k2t.activity_id), k2t.activity_id from kimai2_timesheet k2t
        // inner join kimai2_timesheet_tags k2tt on k2t.id = k2tt.timesheet_id
        // inner join kimai2_tags k on k2tt.tag_id = k.id
        // where k.name in ("issue-235",  "project-kimai2")
        // group by k2t.activity_id;

        $timeSheets = $this->loadTimeSheetsByTag($tagNames);
        $activities = [];
        foreach ($timeSheets as $timesheet) {
            if ($timesheet->getActivity()->getProject() !== null && $timesheet->getProject()->getId() !== $project_id) {
                continue;
            }
            $activity = $timesheet->getActivity();
            $id = $activity->getId();
            if (key_exists($id, $activities)) {
                $activities[$id] = $activities[$id] + 1;
            } else {
                $activities[$id] = 1;
            }
        }

        $activityId = null;
        $count = 0;
        foreach ($activities as $id => $activity_count) {
            if ($activity_count > $count) {
                $count = $activity_count;
                $activityId = $id;
            }
        }

        return $activityId;
    }

    private function loadTimeSheetsByTag(array $tagNames)
    {
        $tags = $this->tagRepository->findBy(["name" => $tagNames]);
        $timesheetQuery = new TimesheetQuery();
        $timesheetQuery->setTags($tags);
        return $this->timesheetRepository->getTimesheetsForQuery($timesheetQuery);
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
        if (count($parts) > 3 && $parts[2] === "projects") {
            $tags["projects"] = $parts[3];
        }
        return $tags;
    }
}
