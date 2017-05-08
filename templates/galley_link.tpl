{**
 * templates/galley_link.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}


{if $galley->isPdfGalley()}
	{assign var="type" value="pdf"}
{else}
	{assign var="type" value="file"}
{/if}

{assign var="page" value="forthcoming"}
{assign var="parentId" value=$parent->getBestArticleId()}

<a class="obj_galley_link {$type}" href="{url page=$page op="article" path=$parentId|to_array:$galley->getBestGalleyId()}">
	{$galley->getGalleyLabel()|escape}
</a>
