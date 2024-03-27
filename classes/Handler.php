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
 * Find forthcoming content and display it when requested.
 */

namespace APP\plugins\generic\forthcoming\classes;

use APP\core\Services;
use APP\submission\Submission;
use APP\template\TemplateManager;

class Handler extends \APP\handler\Handler
{
    public static $plugin;
    public static $forthcomingIssueId;

    public static function setPlugin($plugin)
    {
        self::$plugin = $plugin;
    }

    public static function setForthcomingId($forthcomingIssueId)
    {
        self::$forthcomingIssueId = $forthcomingIssueId;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle index request (redirect to "view")
     *
     * @param $args array Arguments array.
     * @param $request PKPRequest Request object.
     */
    public function index($args, $request)
    {
        $context = $request->getContext();
        $contextId = $context->getId();
        $templateMgr = TemplateManager::getManager($request);
        $this->setupTemplate($request);

        if (self::$forthcomingIssueId) {
            $forthcomingIterator = iterator_to_array(
                Services::get('submission')->getMany([
                    'contextId' => $contextId,
                    'issueIds' => [self::$forthcomingIssueId],
                    'status' => [Submission::STATUS_PUBLISHED],
                    'orderBy' => 'datePublished',
                    'orderDirection' => 'ASC',
                ])
            );

            $forthcomingSubmissions = [];
            foreach ($forthcomingIterator as $submission) {
                if ($submission->getCurrentPublication()->getData('issueId') == self::$forthcomingIssueId && $submission->getCurrentPublication()->getData('datePublished')) {
                    $forthcomingSubmissions[] = $submission;
                }
            }

            $templateMgr->assign('forthcoming', $forthcomingSubmissions);
            $templateMgr->display(self::$plugin->getTemplateResource('content.tpl'));
        }
    }
}
