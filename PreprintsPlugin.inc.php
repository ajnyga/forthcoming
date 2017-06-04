<?php

/**
 * @file PreprintsPlugin.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.preprints
 * @class PreprintsPlugin
 * Static pages plugin main class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PreprintsPlugin extends GenericPlugin {
	/**
	 * Get the plugin's display (human-readable) name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.preprints.displayName');
	}

	/**
	 * Get the plugin's display (human-readable) description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.preprints.description');
	}


	/**
	 * Register the plugin, attaching to hooks as necessary.
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {

				// Intercept the LoadHandler hook to present preprints toc when requested.
				HookRegistry::register('LoadHandler', array($this, 'callbackHandleContent'));
												
				// Handle metadata forms
				HookRegistry::register('TemplateManager::display', array($this, 'metadataFieldEdit'));
				HookRegistry::register('issueentrypublicationmetadataform::readuservars', array($this, 'metadataReadUserVars'));
				HookRegistry::register('issueentrypublicationmetadataform::execute', array($this, 'metadataExecute'));
				HookRegistry::register('articledao::getAdditionalFieldNames', array($this, 'articleSubmitGetFieldNames'));
				

			}
			return true;
		}
		return false;
	}

	
	/**
	 * Insert selection into schedule publication form
	 */
	function metadataFieldEdit($hookName, $params) {
		$template =& $params[1];
		
		if ($template != "controllers/tab/issueEntry/form/publicationMetadataFormFields.tpl") return false;
		$templateMgr =& $params[0];		
		$templateMgr->register_outputfilter(array($this, 'formFilter'));
		
		return false;
		
	}
		
	
	
	/**
	 * Concern preprint field in the form
	 */
	function metadataReadUserVars($hookName, $params) {
		$userVars =& $params[1];
		$userVars[] = 'preprint';
		return false;
	}	
		
	/**
	 * Set preprint
	 */
	function metadataExecute($hookName, $params) {
		$form =& $params[0];
		$article = $form->getSubmission();
		$preprint = $form->getData('preprint');
		$article->setData('preprint', $preprint);
		return false;
	}	
	

	/**
	 * @param $hookName string The name of the invoked hook
	 * @param $args array Hook parameters
	 * @return boolean Hook handling status
	 */
	function callbackHandleContent($hookName, $args) {
		$request = $this->getRequest();
		$templateMgr = TemplateManager::getManager($request);
		$page =& $args[0];
		
		if ($page == "forthcoming"){
			define('HANDLER_CLASS', 'PreprintsHandler');
			$this->import('PreprintsHandler');
			PreprintsHandler::setPlugin($this);
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Add preprint element to the article
	 */
	function articleSubmitGetFieldNames($hookName, $params) {
		$fields =& $params[1];
		$fields[] = 'preprint';
		return false;
	}
	

	/**
	 * Output filter adds form field
	 */
	function formFilter($output, &$templateMgr) {
		
		if (preg_match('/<div class=\"section formButtons/', $output, $matches, PREG_OFFSET_CAPTURE) AND !strpos($output, 'id="preprint"')) {
			$match = $matches[0][0];
			$offset = $matches[0][1];				
			
			$fbv = $templateMgr->getFBV();
			$form = $fbv->getForm();
			$article = $form->getSubmission();		
			$preprint = $article->getData('preprint');
						
			$templateMgr->assign(array(
				'preprint' => $preprint,
			));
			
			$newOutput = substr($output, 0, $offset);	
			$newOutput .= $templateMgr->fetch($this->getTemplatePath() . 'edit.tpl');
			$newOutput .= substr($output, $offset);
			$output = $newOutput;
			
		}
		$templateMgr->unregister_outputfilter('formFilter');
		return $output;
	}			

	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates/';
	}
	
}

?>
