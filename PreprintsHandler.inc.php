<?php

/**
 * @file PreprintsHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.preprints
 * @class PreprintsHandler
 * Find preprint content and display it when requested.
 */

import('classes.handler.Handler');

class PreprintsHandler extends Handler {
	/** @var PreprintsPlugin The preprints plugin */
	static $plugin;	

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Provide the preprints plugin to the handler.
	 * @param $plugin PreprintsPlugin
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}

	/**
	 * Handle index request (redirect to "view")
	 * @param $args array Arguments array.
	 * @param $request PKPRequest Request object.
	 */
	function index($args, $request) {
		$request->redirect(null, null, 'view', $request->getRequestedOp());
	}

	/**
	 * Handle view page request (redirect to "view")
	 * @param $args array Arguments array.
	 * @param $request PKPRequest Request object.
	 */
	function view($args, $request) {

		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_USER);
		
		$context = $request->getContext();
		$contextId = $context->getId();
		
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);

		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$preprints = $publishedArticleDao->getBySetting('preprint', 'on', $contextId);		
		
		$templateMgr->assign('preprints', $preprints);
		$templateMgr->assign('galley_link', self::$plugin->getTemplatePath() . "galley_link.tpl");

		$templateMgr->display(self::$plugin->getTemplatePath() . 'content.tpl');
	}
	
	/**
	 * View Article. (Either article landing page or galley view.)
	 * @param $args array
	 * @param $request Request
	 */
	function article($args, $request) {
		$articleId = array_shift($args);
		$galleyId = array_shift($args);
		$fileId = array_shift($args);

		$journal = $request->getJournal();
		
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$article = $publishedArticleDao->getPublishedArticleByBestArticleId((int) $journal->getId(), $articleId, true);

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'article' => $article,
			'fileId' => $fileId,
		));
		$this->setupTemplate($request);
				
		
		if (!$article->getData('preprint')) fatalError('Cannot view galley.');

		// Fetch and assign the galley to the template
		$galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
		$galley = $galleyDao->getByBestGalleyId($galleyId, $article->getId());
		if ($galley && $galley->getRemoteURL()) $request->redirectUrl($galley->getRemoteURL());

		// Copyright and license info
		$templateMgr->assign(array(
			'copyright' => $journal->getLocalizedSetting('copyrightNotice'),
			'copyrightHolder' => $journal->getLocalizedSetting('copyrightHolder'),
			'copyrightYear' => $journal->getSetting('copyrightYear')
		));
		if ($article->getLicenseURL()) $templateMgr->assign(array(
			'licenseUrl' => $article->getLicenseURL(),
			'ccLicenseBadge' => Application::getCCLicenseBadge($article->getLicenseURL()),
		));


		if (!$galley) {
			$templateMgr->assign('galley_link', self::$plugin->getTemplatePath() . "galley_link.tpl");
			return $templateMgr->display(self::$plugin->getTemplatePath() . 'article.tpl');
		} else {
			// Galley: Prepare the galley file download. Problem with PDF.js galley is that the back link is hardcoded
			if (!HookRegistry::call('ArticleHandler::view::galley', array(&$request, &$issue, &$galley, &$article))) {
				$request->redirect(null, null, 'download', array($articleId, $galleyId));
			}			
			
		}
	}

	/**
	 * Download an article file
	 * @param array $args
	 * @param PKPRequest $request
	 */
	function download($args, $request) {
		$articleId = isset($args[0]) ? $args[0] : 0;
		$galleyId = isset($args[1]) ? $args[1] : 0;
		$fileId = isset($args[2]) ? (int) $args[2] : 0;
		
		$journal = $request->getJournal();
		
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$article = $publishedArticleDao->getPublishedArticleByBestArticleId((int) $journal->getId(), $articleId, true);
		
		if (!$article->getData('preprint')) fatalError('Cannot view galley.');
		
		$galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');		
		$galley = $galleyDao->getByBestGalleyId($galleyId, $article->getId());
		
		if ($galley->getRemoteURL()) $request->redirectUrl($galley->getRemoteURL());
			
		if (!$fileId) {
			$submissionFile = $galley->getFile();
			if ($submissionFile) {
				$fileId = $submissionFile->getFileId();
				// The file manager expects the real article id.  Extract it from the submission file.
				$articleId = $submissionFile->getSubmissionId();
			} else { // no proof files assigned to this galley!
				return null;
			}
		}

		if (!HookRegistry::call('ArticleHandler::download', array($article, &$galley, &$fileId))) {
			import('lib.pkp.classes.file.SubmissionFileManager');
			$submissionFileManager = new SubmissionFileManager($article->getContextId(), $article->getId());
			$submissionFileManager->downloadFile($fileId, null, $request->getUserVar('inline')?true:false);
		}
		
	}	
	
	
	
}

?>
