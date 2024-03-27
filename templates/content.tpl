{**
 * templates/content.tpl
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2014-2024 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display Forthcoming articles
 *}

{capture assign="pageTitle"}{translate key="plugins.generic.forthcoming.pageTitle"}{/capture}

{include file="frontend/components/header.tpl" pageTitleTranslated=$pageTitle}

<h2>{$pageTitle|escape}</h2>
<div class="page page_issue">
	<ul class="cmp_article_list articles">
	{foreach from=$forthcoming item=article}
		<li>
			{assign var=articlePath value=$article->getBestId()}
			{if (!$section.hideAuthor && $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
				{assign var="showAuthor" value=true}
			{/if}

			{assign var=publication value=$article->getCurrentPublication()}
			<div class="obj_article_summary">
				<h2 class="title">
					<a id="article-{$article->getId()}" {if $journal}href="{url journal=$journal->getPath() page="article" op="view" path=$articlePath}"{else}href="{url page="article" op="view" path=$articlePath}"{/if}>
						{$article->getLocalizedTitle()|strip_unsafe_html}
						{if $article->getLocalizedSubtitle()}
							<span class="subtitle">
								{$article->getLocalizedSubtitle()|escape}
							</span>
						{/if}
					</a>
				</h2>

				{if $showAuthor || ($article->getDatePublished() && $showDatePublished)}
				<div class="meta">
					{if $showAuthor}
					<div class="authors">
						{$article->getAuthorString()|escape}
					</div>
					{/if}

					{if $showDatePublished && $article->getDatePublished()}
						<div class="published">
							{$article->getDatePublished()|date_format:$dateFormatShort}
						</div>
					{/if}
				</div>
				{/if}

				{if !$hideGalleys}
					<ul class="galleys_links">
						{foreach from=$article->getGalleys() item=galley}
							{if $primaryGenreIds}
								{assign var="file" value=$galley->getFile()}
								{if !$galley->getRemoteUrl() && !($file && in_array($file->getGenreId(), $primaryGenreIds))}
									{continue}
								{/if}
							{/if}
							<li>
								{assign var="hasArticleAccess" value=$hasAccess}
								{if $currentContext->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN || $publication->getData('accessStatus') == $smarty.const.ARTICLE_ACCESS_OPEN}
									{assign var="hasArticleAccess" value=1}
								{/if}
								{include file="frontend/objects/galley_link.tpl" parent=$article labelledBy="article-{$article->getId()}" hasAccess=$hasArticleAccess purchaseFee=$currentJournal->getData('purchaseArticleFee') purchaseCurrency=$currentJournal->getData('currency')}
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
