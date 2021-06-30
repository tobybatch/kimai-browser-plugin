<?php

namespace KimaiPlugin\BrowserPluginBundle\tests;

use App\Entity\Tag;
use App\Entity\Timesheet;
use App\Repository\TagRepository;
use App\Repository\TimesheetRepository;
use App\Tests\KernelTestTrait;
use KimaiPlugin\BrowserPluginBundle\EventSubscriber\PageLoadSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PageLoadSubscriberTest extends WebTestCase
{
    public const GITHUB_ISSUE_URL = "https://github.com/owner/some-project/issues/id";
    public const GITHUB_PROJECT_URL = "https://github.com/owner/some-project/projects/id";

    public const TRELLO_BOARD_URL = "https://trello.com/b/abc123/some-project";
    public const TRELLO_CARD_URL = "https://trello.com/c/def456/123-some-ticket-name";

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;
    /**
     * @var TagRepository|MockObject
     */
    private $tagReo;
    /**
     * @var TimesheetRepository|MockObject
     */
    private $timesheetRepo;
    /**
     * @var PageLoadSubscriber
     */
    private PageLoadSubscriber $pageLoadSubscriber;

    public function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->tagReo = $this->getMockBuilder(TagRepository::class)->disableOriginalConstructor()->getMock();
        $this->timesheetRepo = $this->getMockBuilder(TimesheetRepository::class)->disableOriginalConstructor()->getMock();

        $this->pageLoadSubscriber = new PermissivePageLoadSubscriber($this->logger, $this->tagReo, $this->timesheetRepo);
    }

//        $facebook = $this->getMock('facebook') // object to mock
//            ->expects($this->once())    // number of times to be called
//            ->method('get') // method name
//            ->with(1) // parameters that are expected
//            ->will($this->returnValue($arr)); // return value
    public function testLoadTimeSheetsByTag(): void
    {
        $tags = [];
        for ($i=0; $i<30; $i++) {
            $tag = new PermissiveTag();
            $tag->id = $i;
            $tag->setName("Tag " . ($i % 3));
            $tags[] = $tag;
        }
        $this->tagReo->expects($this->once())->method("findBy")->willReturn(
            $tags
        );

        $timeSheets = [];
        $this->timesheetRepo->expects($this->once())->method("getTimesheetsForQuery")->willReturn(
            $timeSheets
        );


    }

    public function testOnController(): void
    {
        $request = new Request(["source" => "https://github.com/owner/some-project/issues/id"]);

        $event = $this->getMockBuilder(ControllerEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);

        print_r($request->query);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = PageLoadSubscriber::getSubscribedEvents();
        self::assertCount(1, $events);
        self::assertArrayHasKey(KernelEvents::CONTROLLER, $events);
        self::assertCount(2, $events[KernelEvents::CONTROLLER]);
        self::assertContains('onController', $events[KernelEvents::CONTROLLER]);
        self::assertContains(100, $events[KernelEvents::CONTROLLER]);
    }

    public function testMakeTagsFromTrello(): void
    {
        $url = parse_url(self::TRELLO_BOARD_URL);
        $tags = $this->pageLoadSubscriber->makeTagsFromTrello($url);
        self::assertArrayHasKey("project", $tags);
        self::assertContains("some-project", $tags);

        $url = parse_url(self::TRELLO_CARD_URL);
        $tags = $this->pageLoadSubscriber->makeTagsFromTrello($url);
        self::assertArrayHasKey("issue", $tags);
        self::assertContains("123-some-ticket-name", $tags);
    }

    // getTopProject
    // getTopActivity


    public function testMakeTagsFromGit(): void
    {
        $url = parse_url(self::GITHUB_ISSUE_URL);
        $tags = $this->pageLoadSubscriber->makeTagsFromTrello($url);
        self::assertArrayHasKey("project", $tags);
        self::assertArrayHasKey("issue", $tags);
        self::assertContains("some-project", $tags);
        self::assertContains("id", $tags);

        $url = parse_url(self::GITHUB_PROJECT_URL);
        $tags = $this->pageLoadSubscriber->makeTagsFromTrello($url);
        self::assertArrayHasKey("project", $tags);
        self::assertArrayHasKey("issue", $tags);
        self::assertContains("some-project", $tags);
        self::assertContains("id", $tags);
    }

}
