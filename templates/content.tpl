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
<div class="page page_issue">
	<ul class="cmp_article_list articles">
	{foreach from=$forthcoming item=monograph}
		<li>
			{assign var=articlePath value=$monograph->getId()}
			{if !$section.hideAuthor && $monograph->getData('hideAuthor') == $smarty.const.AUTHOR_TOC_DEFAULT || $monograph->getData('hideAuthor') == $smarty.const.AUTHOR_TOC_SHOW}
				{assign var="showAuthor" value=true}
			{/if}

			{assign var=publication value=$monograph->getCurrentPublication()}
			<div class="obj_article_summary">
				<h2 class="title">
					<a id="monograph-{$monograph->getId()}" {if $journal}href="{url journal=$journal->getPath() page="monograph" op="view" path=$monographPath}"{else}href="{url page="monograph" op="view" path=$monographPath}"{/if}>
						{$monograph->getLocalizedTitle()|strip_unsafe_html}
						{if $monograph->getLocalizedSubtitle()}
							<span class="subtitle">
								{$monograph->getLocalizedSubtitle()|escape}
							</span>
						{/if}
					</a>
				</h2>

				{if $showAuthor || ($monograph->getDatePublished() && $showDatePublished)}
				<div class="meta">
					{if $showAuthor}
					<div class="authors">
						{$publication->getAuthorString($authorUserGroups)|escape}
					</div>
					{/if}

					{if $showDatePublished && $monograph->getDatePublished()}
						<div class="published">
							{$monograph->getDatePublished()|date_format:$dateFormatShort}
						</div>
					{/if}
				</div>
				{/if}

				{if !$hideGalleys}
					<ul class="galleys_links">
						{foreach from=$monograph->getData('publicationFormats') item=galley}
							{if $primaryGenreIds}
								{assign var="file" value=$galley->getFile()}
								{if !$galley->getRemoteUrl() && !($file && in_array($file->getGenreId(), $primaryGenreIds))}
									{continue}
								{/if}
							{/if}
							<li>
								{assign var="hasArticleAccess" value=$hasAccess}
								{if $currentContext->getSetting('publishingMode') == \APP\journal\Journal::PUBLISHING_MODE_OPEN || $publication->getData('accessStatus') == \APP\submission\Submission::ARTICLE_ACCESS_OPEN}
									{assign var="hasArticleAccess" value=1}
								{/if}
								{include file="frontend/objects/galley_link.tpl" parent=$monograph labelledBy="monograph-{$monograph->getId()}" hasAccess=$hasArticleAccess purchaseFee=$currentJournal->getData('purchaseArticleFee') purchaseCurrency=$currentJournal->getData('currency')}
							</li>
						{/foreach}
					</ul>
				{/if}

				{call_hook name="Templates::Issue::Issue::Article"}
			</div>
		</li>
	{/foreach}
	</ul>
</div>

{include file="frontend/components/footer.tpl"}
