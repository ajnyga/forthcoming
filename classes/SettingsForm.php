<?php

/**
 * @file classes/SettingsForm.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2014-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.forthcoming
 *
 * @class SettingsForm
 *
 * @brief ForthcomingPlugin settings class
 */

namespace APP\plugins\generic\forthcoming\classes;

use APP\core\Application;
use APP\facades\Repo;
use APP\notification\Notification;
use APP\notification\NotificationManager;
use APP\plugins\generic\forthcoming\ForthcomingPlugin;
use APP\template\TemplateManager;
use PKP\form\Form;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorPost;
use APP\section\Section;

class SettingsForm extends Form
{
    /**
     * Constructor
     */
    public function __construct(public ForthcomingPlugin $plugin)
    {
        parent::__construct($plugin->getTemplateResource('settings.tpl'));
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * @copydoc Form::initData()
     */
    public function initData(): void
    {
        $contextId = Application::get()->getRequest()->getContext()->getId();
        $this->setData('forthcomingId', $this->plugin->getSetting($contextId, 'forthcomingId'));
        parent::initData();
    }

    /**
     * @copydoc Form::readInputData()
     */
    public function readInputData(): void
    {
        $this->readUserVars(['forthcomingId']);
        parent::readInputData();
    }

    /**
     * @copydoc Form::fetch()
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false): string
    {
        $templateMgr = TemplateManager::getManager($request);
        $contextId = Application::get()->getRequest()->getContext()->getId();

        $press = $request->getPress();

        $collector = Repo::section()->getCollector();
        $series = $collector
            ->filterByContextIds([$press->getId()])
            ->getMany()
            ->mapWithKeys(fn (Section $section) => [$section->getId() => $section->getLocalizedTitle()])
            ->collect()
            ->prepend(__('common.none'), 0)
            ->toArray();

        $templateMgr->assign(['series' => $series, 'pluginName' => $this->plugin->getName()]);
        return parent::fetch($request, $template, $display);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        $contextId = Application::get()->getRequest()->getContext()->getId();
        $this->plugin->updateSetting($contextId, 'forthcomingId', $this->getData('forthcomingId'));

        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification(
            Application::get()->getRequest()->getUser()->getId(),
            Notification::NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('common.changesSaved')]
        );
        return parent::execute();
    }
}
