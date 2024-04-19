<?php

/**
 * @file ForthcomingPlugin.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2014-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.forthcoming
 *
 * @class ForthcomingPlugin
 *
 * @brief ForthcomingPlugin main class
 */

namespace APP\plugins\generic\forthcoming;

use APP\core\Application;
use APP\plugins\generic\forthcoming\classes\Handler;
use APP\plugins\generic\forthcoming\classes\SettingsForm;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class ForthcomingPlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName(): string
    {
        return __('plugins.generic.forthcoming.displayName');
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDescription(): string
    {
        return __('plugins.generic.forthcoming.description');
    }

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null): bool
    {
        if (!parent::register($category, $path, $mainContextId)) {
            return false;
        }

        if ($this->getEnabled($mainContextId)) {
            Hook::add('LoadHandler', [$this, 'loadHandler']);

            # Add Forthcoming label to article summary, hide Forthcoming issue from public issue archive and mark in backend archive, redirect calls to issue landing page to custom Forthcoming handler
            Hook::add('TemplateManager::display', [$this, 'displayTemplate']);
        }
        return true;
    }

    /**
     * Handle Forthcoming issue in archive listings
     */
    public function displayTemplate(string $hookName, array $params): bool
    {
        /** @var \APP\template\TemplateManager $templateManager */
        [$templateManager, $template] = $params;
        $request = Application::get()->getRequest();
        $contextId = $request->getContext()?->getId();
        $forthcomingId = (int) $this->getSetting($contextId, 'forthcomingId');
        if (!$forthcomingId) {
            return Hook::CONTINUE;
        }

        switch ($template) {
            // Redirect default issue toc page to Forthcoming page
            case 'frontend/pages/issue.tpl':
                $issueId = $templateManager->getTemplateVars('issueId');
                if ($forthcomingId === (int) $issueId) {
                    $router = $request->getRouter();
                    $request->redirectUrl($router->url($request, null, 'forthcoming'));
                }
                break;

                // Article landing page
            case 'frontend/pages/article.tpl':
                $publication = $templateManager->getTemplateVars('publication');
                if ((int) $publication?->getData('issueId') === $forthcomingId) {
                    $templateManager->registerFilter('output', [$this, 'articleLandingPageFilter']);
                }
                break;

                // Remove Forthcoming issue from the list of issues
            case 'frontend/pages/issueArchive.tpl':
                $issues = $templateManager->getTemplateVars('issues');
                $total = $templateManager->getTemplateVars('total');
                foreach ($issues as $key => $issue) {
                    if ($issue->getId() === (int) $forthcomingId) {
                        unset($issues[$key]);
                        --$total;
                        break;
                    }
                }
                $templateManager->assign(['issues' => $issues, 'total' => $total]);
                break;

                // Backend archive display
            case 'manageIssues/issues.tpl':
                $forthcomingIssueBackendStyles = "span#cell-{$forthcomingId}-identification:after { font-family: FontAwesome; content: \"\f005\"; }";
                $templateManager->addStylesheet(
                    'forthcomingIssueBackendStyles',
                    $forthcomingIssueBackendStyles,
                    ['inline' => true, 'contexts' => 'backend']
                );
                break;

                // Book landing page
            case 'frontend/pages/book.tpl':
                $series = $templateManager->getTemplateVars('series');
                if ((int) $series->getId() === $forthcomingId) {
                    $templateManager->registerFilter('output', [$this, 'bookLandingPageFilter']);
                }
                break;

                // Remove Forthcoming monograph from the catalog
            case 'frontend/pages/catalog.tpl':
                $submissions = $templateManager->getTemplateVars('publishedSubmissions');
                foreach ($submissions as $key => $submission) {
                    if ($submission->getSeriesId() === (int) $forthcomingId) {
                        unset($submissions[$key]);
                    }
                }
                $templateManager->assign(['publishedSubmissions' => $submissions]);
                break;
        }

        return Hook::CONTINUE;
    }

    /**
     * Setup the plugin handler
     */
    public function loadHandler($hookName, $args): bool
    {
        $page = $args[0];
        if ($page !== 'forthcoming') {
            return Hook::CONTINUE;
        }

        $request = $this->getRequest();
        $forthcomingId = (int) $this->getSetting($request->getContext()->getId(), 'forthcomingId');

        if ($forthcomingId) {
            define('HANDLER_CLASS', Handler::class);
            Handler::setPlugin($this);
            Handler::setForthcomingId($forthcomingId);
            return Hook::ABORT;
        }

        $router = $request->getRouter();
        $request->redirectUrl($router->url($request, null, 'index'));
        return Hook::CONTINUE;
    }

    /**
     * Removed the authorFormFilter and adds a "Forthcoming" label to the article's landing page
     */
    public function articleLandingPageFilter($output, $templateMgr): string
    {
        if (!preg_match('/<h1[^>]+class="page_title"[^>]*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            return $output;
        }

        $offset = $matches[0][1];
        $newOutput = substr($output, 0, $offset);
        $newOutput .= '<div class="forthcomingLabel"><span style="border-radius: 5px; background: #ebebeb; color: #262626; padding: 6px;">' . __('plugins.generic.forthcoming.label') . '</span></div><br />';
        $newOutput .= substr($output, $offset);
        $output = $newOutput;
        $templateMgr->unregisterFilter('output', [$this, 'authorFormFilter']);
        return $output;
    }

    /**
     * Removed the authorFormFilter and adds a "Forthcoming" label to the book's landing page
     */
    public function bookLandingPageFilter($output, $templateMgr): string
    {
        if (!preg_match('/<h1[^>]+class="title"[^>]*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            return $output;
        }

        $offset = $matches[0][1];
        $newOutput = substr($output, 0, $offset);
        $newOutput .= '<div class="forthcomingLabel"><span style="border-radius: 5px; background: #ebebeb; color: #262626; padding: 6px;">' . __('plugins.generic.forthcoming.label') . '</span></div><br />';
        $newOutput .= substr($output, $offset);
        $output = $newOutput;
        $templateMgr->unregisterFilter('output', [$this, 'authorFormFilter']);
        return $output;
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $actionArgs): array
    {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }

        $router = $request->getRouter();
        $url = $router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic']);
        array_unshift($actions, new LinkAction('settings', new AjaxModal($url, $this->getDisplayName()), __('manager.plugins.settings')));
        return $actions;
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request): JSONMessage
    {
        if ($request->getUserVar('verb') !== 'settings') {
            return parent::manage($args, $request);
        }

        $form = new SettingsForm($this);
        if (!$request->getUserVar('save')) {
            $form->initData();
            return new JSONMessage(true, $form->fetch($request));
        }

        $form->readInputData();
        if (!$form->validate()) {
            return new JSONMessage(true, $form->fetch($request));
        }

        $form->execute();
        return new JSONMessage(true);
    }
}
