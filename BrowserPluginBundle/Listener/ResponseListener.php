<?php

/*
 * This file is part of the Browser Plugin for Kimai 2.
 * All rights reserved by Toby Batch (www.neontribe.co.uk).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\BrowserPluginBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $a = $request->getPathInfo();
        $this->logger->debug("HERE", [$a, $request->get("kimaiBrowserPlugin")]);
        if ($request->get("kimaiBrowserPlugin") && str_ends_with($request->getPathInfo(), "timesheet/create")) {
            $response = $event->getResponse();
            $content = $response->getContent();
            $css = "<style>.box-header.with-border, .toolbar-pad.no-print, .content-header, header { display: none; } .content-wrapper { padding-top: 0px !important; } .row:nth-child(1) { display: inline-flex !important; }</style>";
            $newContent = substr_replace($content, $css, strpos($content, "</head>"), 0);
            $response->setContent($newContent);
        }
    }
}
// display: inline-flex;

