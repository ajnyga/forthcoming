{**
 * templates/content.tpl
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2014-2024 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display Forthcoming monographs
 *}

{capture assign="pageTitle"}{translate key="plugins.generic.forthcoming.pageTitle"}{/capture}

{include file="frontend/components/header.tpl" pageTitleTranslated=$pageTitle}

<h2>{$pageTitle|escape}</h2>
{include file="frontend/components/monographList.tpl" monographs=$forthcoming featured=$featuredMonographIds authorUserGroups=$authorUserGroups}

{include file="frontend/components/footer.tpl"}
    