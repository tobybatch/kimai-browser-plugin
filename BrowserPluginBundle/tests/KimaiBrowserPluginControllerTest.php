<?php

namespace KimaiPlugin\BrowserPluginBundle\tests;

use App\DataFixtures\UserFixtures;
use App\Tests\KernelTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class KimaiBrowserPluginControllerTest extends WebTestCase
{
    use KernelTestTrait;

    public const DEFAULT_LANGUAGE = 'en';

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testIndexAction(): void
    {
        $client = $this->getLoggedInClient();
        $client->request("GET", $this->createUrl("/kimai-browser-plugin"));
        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());

        $crawler = $client->getCrawler();

        $topDiv = $crawler->filter('div#browser-plugin');
        $this->assertEquals(1, $topDiv->count());

        $actualLinks = ["KimaiBrowserPlugin.crx", "https://github.com/tobybatch/kimai-browser-plugin"];
        $links = $topDiv->filter("a");
        foreach ($links as $link) {
            self::assertContains($link->textContent, $actualLinks);
        }
    }

    public function testDownloadAction(): void
    {
        $client = $this->getLoggedInClient();
        $client->request("GET", $this->createUrl("/kimai-browser-plugin/download"));
        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertEquals("application/x-chrome-extension", $response->headers->get("content-transfer-encoding"));
        self::assertEquals("File Transfer", $response->headers->get("content-description"));
        self::assertEquals("attachment; filename=KimaiBrowserPlugin.crx", $response->headers->get("content-disposition"));
    }

    private function createUrl(string $url): string
    {
        return '/' . self::DEFAULT_LANGUAGE . '/' . ltrim($url, '/');
    }

    private function getLoggedInClient(): KernelBrowser
    {
        return self::createClient(
            [],
            [
                'PHP_AUTH_USER' => UserFixtures::USERNAME_USER,
                'PHP_AUTH_PW' => UserFixtures::DEFAULT_PASSWORD,
            ]
        );
    }
}
