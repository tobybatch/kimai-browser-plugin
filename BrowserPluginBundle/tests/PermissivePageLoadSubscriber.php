<?php

namespace KimaiPlugin\BrowserPluginBundle\tests;

use KimaiPlugin\BrowserPluginBundle\EventSubscriber\PageLoadSubscriber;

class PermissivePageLoadSubscriber extends PageLoadSubscriber
{
    public function getTopProject(string $tagNames): ?int
    {
        return parent::getTopProject($tagNames);
    }

    public function getTopActivity(array $tagNames, $project_id): ?int
    {
        return parent::getTopActivity( $tagNames, $project_id);
    }

    public function loadTimeSheetsByTag(array $tagNames)
    {
        return parent::loadTimeSheetsByTag($tagNames);
    }

    public function makeTagsFromGithub(array $url): array
    {
        return parent::makeTagsFromGithub($url);
    }

    public function makeTagsFromTrello(array $url): array
    {
        return parent::makeTagsFromTrello($url);
    }
}