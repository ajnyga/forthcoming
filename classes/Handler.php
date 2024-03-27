<?php
/**
 * @file classes/Handler.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2014-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.forthcoming
 *
 * @class Handler
 *
 * @brief Find forthcoming content and display it when requested.
 */

namespace APP\plugins\generic\forthcoming\classes;

use APP\core\Request;
use APP\facades\Repo;
use APP\plugins\generic\forthcoming\ForthcomingPlugin;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\security\Role;

class Handler extends \APP\handler\Handler
{
    public static ForthcomingPlugin $plugin;
    public static ?int $forthcomingIssueId;

    public static function setPlugin(ForthcomingPlugin $plugin): void
    {
        static::$plugin = $plugin;
    }

    public static function setForthcomingId(?int $forthcomingIssueId): void
    {
        static::$forthcomingIssueId = $forthcomingIssueId;
    }

    /**
     * @copydoc PKPHandler::index()
     *
     * @param Request $request Request
     */
    public function index($args, $request): void
    {
        $contextId = $request->getContext()->getId();
        $templateMgr = TemplateManager::getManager($request);
        $this->setupTemplate($request);

        if (!static::$forthcomingIssueId) {
            return;
        }

        $collector = Repo::submission()->getCollector();

        $submissions = $collector
            ->filterByContextIds([$contextId])
            ->filterByIssueIds([static::$forthcomingIssueId])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->orderBy($collector::ORDERBY_DATE_PUBLISHED, $collector::ORDER_DIR_ASC)
            ->getMany()
            ->filter(fn (Submission $submission) => (int) ($publication = $submission->getCurrentPublication())?->getData('issueId') === static::$forthcomingIssueId && $publication->getData('datePublished'))
            ->toArray();

        $authorUserGroups = Repo::userGroup()->getByRoleIds([Role::ROLE_ID_AUTHOR], $contextId);

        $templateMgr->assign(['forthcoming' => $submissions, 'authorUserGroups' => $authorUserGroups]);
        $templateMgr->display(static::$plugin->getTemplateResource('content.tpl'));
    }
}
