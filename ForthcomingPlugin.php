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
 * ForthcomingPlugin main class
 */

namespace APP\plugins\generic\forthcoming;

use APP\core\Application;
use APP\plugins\generic\forthcoming\classes\Handler;
use APP\plugins\generic\forthcoming\classes\SettingsForm;
use APP\template\TemplateManager;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class ForthcomingPlugin extends GenericPlugin
{
    /**
     * Get the plugin's display (human-readable) name.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.generic.forthcoming.displayName');
    }

    /**
     * Get the plugin's display (human-readable) description.
     *
     * @return string
     */
    public function getDescription()
    {
        return __('plugins.generic.forthcoming.description');
    }


    /**
     * Register the plugin, attaching to hooks as necessary.
     *
     * @param $category string
     * @param $path string
     * @param null|mixed $mainContextId
     *
     * @return boolean
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                Hook::add('LoadHandler', [$this, 'loadHandler']);

                # Add Forthcoming label to article summary, hide Forthcoming issue from public issue archive and mark in backend archive, redirect calls to issue landing page to custom Forthcoming handler
                Hook::add('TemplateManager::display', [$this, 'displayTemplate']);

                # Add Forthcoming label to article summary
                #HookRegistry::register('Templates::Issue::Issue::Article', array($this, 'articleDisplay'));
            }
            return true;
        }
        return false;
    }


    /**
     * Handle Forthcoming issue in archive listings
     */
    public function displayTemplate($hookName, $params)
    {
        $template = $params[1];

        // Redirect default issue toc page to Forthcoming page
        if ($template == 'frontend/pages/issue.tpl') {
            $contextId = Application::get()->getRequest()->getContext()->getId();
            $forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');

            if ($forthcomingIssueId) {
                $templateMgr = $params[0];
                $issueId = $templateMgr->getTemplateVars('issueId');
                if ((int) $forthcomingIssueId == $issueId) {
                    $request = Application::get()->getRequest();
                    $router = $request->getRouter();
                    $request->redirectUrl($router->url($request, null, 'forthcoming'));
                }
            }
        }

        // Article landing page
        if ($template == 'frontend/pages/article.tpl') {
            $contextId = Application::get()->getRequest()->getContext()->getId();
            $forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');

            if ($forthcomingIssueId) {
                $templateMgr = & $params[0];
                $publication = $templateMgr->getTemplateVars('publication');
                if ($publication && $publication->getData('issueId') == $forthcomingIssueId) {
                    $templateMgr->registerFilter('output', [$this, 'articleLandingPageFilter']);
                }
            }
        }

        // Remove Forthcoming issue from the list of issues
        if ($template == 'frontend/pages/issueArchive.tpl') {
            $contextId = Application::get()->getRequest()->getContext()->getId();
            $forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');

            if ($forthcomingIssueId) {
                $templateMgr = $params[0];
                $issues = $templateMgr->getTemplateVars('issues');
                $total = $templateMgr->getTemplateVars('total');
                $filteredIssues = [];
                foreach ($issues as $issue) {
                    if ($issue->getId() == (int) $forthcomingIssueId) {
                        $total = $total - 1;
                        continue;
                    }
                    $filteredIssues[] = $issue;
                }

                $templateMgr->assign([
                    'issues' => $filteredIssues,
                    'total' => $total,
                ]);
            }
        }

        // Backend archive display
        if ($template == 'manageIssues/issues.tpl') {
            $templateMgr = $params[0];
            $contextId = Application::get()->getRequest()->getContext()->getId();
            $forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');
            if ($forthcomingIssueId) {
                $forthcomingIssueBackendStyles = 'span#cell-' . $forthcomingIssueId . '-identification:after { font-family: FontAwesome; content: "\f005"; }';

                $templateMgr->addStylesheet(
                    'forthcomingIssueBackendStyles',
                    $forthcomingIssueBackendStyles,
                    [
                        'inline' => true,
                        'contexts' => 'backend',
                    ]
                );
                return false;
            }
        }
    }


    /**
     * @param $hookName string The name of the invoked hook
     * @param $args array Hook parameters
     *
     * @return boolean Hook handling status
     */
    public function loadHandler($hookName, $args)
    {
        $request = $this->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $page = & $args[0];
        if ($page == 'forthcoming') {
            $forthcomingIssueId = $this->getSetting($request->getContext()->getId(), 'forthcomingIssueId');

            if ($forthcomingIssueId) {
                define('HANDLER_CLASS', 'Handler');
                Handler::setPlugin($this);
                Handler::setForthcomingId($forthcomingIssueId);
                return true;
            }
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, 'index'));
        }
        return false;
    }


    public function articleLandingPageFilter($output, $templateMgr)
    {
        if (preg_match('/<h1[^>]+class="page_title"[^>]*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
            $match = $matches[0][0];
            $offset = $matches[0][1];
            $newOutput = substr($output, 0, $offset);
            $newOutput .= '<div class="forthcomingLabel"><span style="border-radius: 5px; background: #ebebeb; color: #262626; padding: 6px;">' . __('plugins.generic.forthcoming.label') . '</span></div><br />';
            $newOutput .= substr($output, $offset);
            $output = $newOutput;
            $templateMgr->unregisterFilter('output', [$this, 'authorFormFilter']);
        }
        return $output;
    }


    public function getActions($request, $actionArgs)
    {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }

        $router = $request->getRouter();
        $linkAction = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url(
                    $request,
                    null,
                    null,
                    'manage',
                    null,
                    [
                        'verb' => 'settings',
                        'plugin' => $this->getName(),
                        'category' => 'generic'
                    ]
                ),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );

        array_unshift($actions, $linkAction);
        return $actions;
    }

    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $form = new SettingsForm($this);

                if (!$request->getUserVar('save')) {
                    $form->initData();
                    return new JSONMessage(true, $form->fetch($request));
                }

                $form->readInputData();

                if ($form->validate()) {
                    $form->execute();
                    return new JSONMessage(true);
                }
        }
        return parent::manage($args, $request);
    }
}
