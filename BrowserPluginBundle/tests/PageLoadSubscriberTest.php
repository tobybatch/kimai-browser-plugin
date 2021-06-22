<?php

namespace KimaiPlugin\BrowserPluginBundle\tests;

use App\Repository\TagRepository;
use App\Repository\TimesheetRepository;
use KimaiPlugin\BrowserPluginBundle\EventSubscriber\PageLoadSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PageLoadSubscriberTest extends TestCase
{

    public function testOnController(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $tagReo = $this->getMockBuilder(TagRepository::class)->disableOriginalConstructor()->getMock();
        $timesheetRepo = $this->getMockBuilder(TimesheetRepository::class)->disableOriginalConstructor()->getMock();

        $request = new Request(["source" => "https://github.com/OWNER/REPONAME/issues/ID"]);

        $event = $this->getMockBuilder(ControllerEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);

        $pageLoadSubscriber = new PageLoadSubscriber($logger, $tagReo, $timesheetRepo);
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
}
