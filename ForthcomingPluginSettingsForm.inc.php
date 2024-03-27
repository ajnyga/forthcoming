<?php

/**
 * @file ForthcomingPluginSettingsForm.inc.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2014-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.forthcoming
 * @class ForthcomingPluginSettingsForm
 * ForthcomingPlugin settings class
 */

import('lib.pkp.classes.form.Form');

class ForthcomingPluginSettingsForm extends Form {
	/** @var TutorialExamplePlugin  */
	public $plugin;

	public function __construct($plugin) {
		parent::__construct($plugin->getTemplateResource('settings.tpl'));
		$this->plugin = $plugin;
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	public function initData() {
		$contextId = Application::get()->getRequest()->getContext()->getId();
		$this->setData('forthcomingIssueId', $this->plugin->getSetting($contextId, 'forthcomingIssueId'));
		parent::initData();
	}

	public function readInputData() {
		$this->readUserVars(['forthcomingIssueId']);
		parent::readInputData();
	}

	public function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$contextId = Application::get()->getRequest()->getContext()->getId();

		$params = array(
			'contextId' => $contextId,
			'orderBy' => 'seq',
			'orderDirection' => 'ASC',
			'isPublished' => true,
		);
		$issues = iterator_to_array(Services::get('issue')->getMany($params));

        $issuesList = [0 => __('common.none')];
        foreach ($issues as $issue) {
            $issuesList[$issue->getId()] = $issue->getIssueIdentification();
        }
		$templateMgr->assign('issues', $issuesList);
		$templateMgr->assign('pluginName', $this->plugin->getName());
		return parent::fetch($request, $template, $display);
	}

	public function execute(...$functionArgs) {
		$contextId = Application::get()->getRequest()->getContext()->getId();
		$this->plugin->updateSetting($contextId, 'forthcomingIssueId', $this->getData('forthcomingIssueId'));

		import('classes.notification.NotificationManager');
		$notificationMgr = new NotificationManager();
		$notificationMgr->createTrivialNotification(
			Application::get()->getRequest()->getUser()->getId(),
			NOTIFICATION_TYPE_SUCCESS,
			['contents' => __('common.changesSaved')]
		);
		return parent::execute();
	}
}

