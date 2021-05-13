<?php

/*
 * This file is part of the Kimai CustomCSSBundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\BrowserPluginBundle\Controller;

use App\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/kimai-browser-plugin")
 * @Security("is_granted('create_own_timesheet')")
 */
class KimaiBrowserPluginController extends AbstractController
{
    /**
     * @Route(path="", name="kimai-browser-plugin", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        return $this->render('@BrowserPlugin/index.html.twig');
    }

    /**
     * @Route(path="/download", name="kimai-browser-plugin-download", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(Request $request, KernelInterface $kernel)
    {
        $path = $kernel
                ->getRootDir() . '/../var/plugins/BrowserPluginBundle/Resources/assets/KimaiBrowserPlugin.crx';
        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Description', "File Transfer");
        $response->headers->set('Content-Transfer-Encoding', "application/x-chrome-extension");
        $response->headers->set('Content-Type', "binary");
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($path)
        );

        return $response;
    }
}
