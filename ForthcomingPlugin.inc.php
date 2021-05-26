<?php

/**
 * @file ForthcomingPlugin.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.forthcoming
 * @class ForthcomingPlugin
 * ForthcomingPlugin main class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class ForthcomingPlugin extends GenericPlugin {
	/**
	 * Get the plugin's display (human-readable) name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.forthcoming.displayName');
	}

	/**
	 * Get the plugin's display (human-readable) description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.forthcoming.description');
	}


	/**
	 * Register the plugin, attaching to hooks as necessary.
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path, $mainContextId = NULL) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {

				HookRegistry::register('LoadHandler', array($this, 'loadForthcomingHandler'));

				# Add Forthcoming label to article summary, hide Forthcoming issue from public issue archive and mark in backend archive, redirect calls to issue landing page to custom Forthcoming handler
				HookRegistry::register('TemplateManager::display', array($this, 'issueDisplay'));

				# Add Forthcoming label to article summary
				HookRegistry::register('Templates::Issue::Issue::Article', array($this, 'articleDisplay'));

				# Add Forthcoming label to article landing page


				#

			}
			return true;
		}
		return false;
	}


	/**
	 * Handle Forthcoming issue in archive listings
	 */
	public function articleDisplay($hookName, $params) {
		$request = Application::get()->getRequest();
		$contextId = $request->getContext()->getId();
		$forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');
		if ($forthcomingIssueId) {
			$templateMgr = $params[1];
			$publication = $templateMgr->getTemplateVars('publication');
			if ($publication && $publication->getData('issueId') == $forthcomingIssueId) {
				#$output =& $params[2];
				#$output .= '<div class="forthcomingLabel"><span style="border-radius: 5px; background: #ebebeb; color: #262626; padding: 6px;">'.__('plugins.generic.forthcoming.label').'</span></div>';
			}
		}
		return false;
	}


	/**
	 * Handle Forthcoming issue in archive listings
	 */
	function issueDisplay($hookName, $params) {
		$template = $params[1];

		// Redirect issue toc page
		if ($template == "frontend/pages/issue.tpl"){
			$contextId = Application::get()->getRequest()->getContext()->getId();
			$forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');

			if ($forthcomingIssueId) {
				$templateMgr = $params[0];
				$issueId = $templateMgr->getTemplateVars('issueId');
				if ((int) $forthcomingIssueId == $issueId){
					$request = PKPApplication::get()->getRequest();
					$router = $request->getRouter();
					$request->redirectUrl($router->url($request, null, 'forthcoming'));
				}
			}
		}

		// Article landing page
		if ($template == "frontend/pages/article.tpl"){

			$contextId = Application::get()->getRequest()->getContext()->getId();
			$forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');

			if ($forthcomingIssueId){
				$templateMgr =& $params[0];
				$publication = $templateMgr->getTemplateVars('publication');
				if ($publication && $publication->getData('issueId') == $forthcomingIssueId) {
					$templateMgr->registerFilter("output", array($this, 'articleLandingPageFilter'));
				}
			}
		}

		// Public archive
		if ($template == "frontend/pages/issueArchive.tpl"){
			$contextId = Application::get()->getRequest()->getContext()->getId();
			$forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');

			if ($forthcomingIssueId) {
				$templateMgr = $params[0];
				$issues = $templateMgr->getTemplateVars('issues');
				$total = $templateMgr->getTemplateVars('total');
				$filteredIssues = [];
				foreach ($issues as $issue) {
					if ($issue->getId() == (int) $forthcomingIssueId){
						$total = $total-1;
						continue;
					}
					$filteredIssues[] = $issue;
				}

				$templateMgr->assign(array(
					'issues' => $filteredIssues,
					'total' => $total,
				));
			}
		}

		// Backend archive
		if ($template == "manageIssues/issues.tpl"){
			$templateMgr = $params[0];
			$contextId = Application::get()->getRequest()->getContext()->getId();
			$forthcomingIssueId = $this->getSetting($contextId, 'forthcomingIssueId');
			if ($forthcomingIssueId) {
				$forthcomingIssueBackendStyles = 'span#cell-'.$forthcomingIssueId.'-identification:after { font-family: FontAwesome; content: "\f005"; }';

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
	 * @return boolean Hook handling status
	 */
	function loadForthcomingHandler($hookName, $args) {
		$request = $this->getRequest();
		$templateMgr = TemplateManager::getManager($request);
		$page =& $args[0];
		if ($page == "forthcoming"){

			$forthcomingIssueId = $this->getSetting($request->getContext()->getId(), 'forthcomingIssueId');

			if ($forthcomingIssueId) {
				define('HANDLER_CLASS', 'ForthcomingHandler');
				$this->import('ForthcomingHandler');
	            ForthcomingHandler::setPlugin($this);
	            ForthcomingHandler::setForthcomingId($forthcomingIssueId);
				return true;
			}
			$router = $request->getRouter();
			$request->redirectUrl($router->url($request, null, 'index'));
			# Get latest publication -> if not Forthcoming issue, do not show
			# When published in new issue,
			# Or delete the whole publication?

		}
		return false;
	}


	function articleLandingPageFilter($output, $templateMgr) {
		if (preg_match('/<h1[^>]+class="page_title"[^>]*>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$match = $matches[0][0];
			$offset = $matches[0][1];
			$newOutput = substr($output, 0, $offset);
			$newOutput .= '<div class="forthcomingLabel"><span style="border-radius: 5px; background: #ebebeb; color: #262626; padding: 6px;">'.__('plugins.generic.forthcoming.label').'</span></div><br />';
			$newOutput .= substr($output, $offset);
			$output = $newOutput;
			$templateMgr->unregisterFilter('output', array($this, 'authorFormFilter'));
		}
		return $output;
	}


	public function getActions($request, $actionArgs) {
		$actions = parent::getActions($request, $actionArgs);
		if (!$this->getEnabled()) {
			return $actions;
		}

		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$linkAction = new LinkAction(
			'settings',
			new AjaxModal(
				$router->url(
					$request,
					null,
					null,
					'manage',
					null,
					array(
						'verb' => 'settings',
						'plugin' => $this->getName(),
						'category' => 'generic'
					)
				),
				$this->getDisplayName()
			),
			__('manager.plugins.settings'),
			null
		);

		array_unshift($actions, $linkAction);
		return $actions;
	}

	public function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$this->import('ForthcomingPluginSettingsForm');
				$form = new ForthcomingPluginSettingsForm($this);

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

