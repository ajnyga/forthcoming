{**
 * templates/content.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display Preprints
 *}

{capture assign="pageTitle"}{translate key="plugins.generic.preprints.pageTitle"}{/capture}
 
{include file="frontend/components/header.tpl" pageTitleTranslated=$pageTitle}

<h2>{$pageTitle|escape}</h2>
<div class="page page_issue">
	<div class="obj_issue_toc">
	<ul class="articles">
		{foreach from=$preprints item=article}
			<li>
				
			{assign var=articlePath value=$article->getBestArticleId()}

			<div class="obj_article_summary">
				{if $article->getLocalizedCoverImage()}
					<div class="cover">
						<a href="{url page="forthcoming" op="article" path=$articlePath}" class="file">
							<img src="{$article->getLocalizedCoverImageUrl()|escape}"{if $article->getLocalizedCoverImageAltText() != ''} alt="{$article->getLocalizedCoverImageAltText()|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if}>
						</a>
					</div>
				{/if}

				<div class="title">
					<a href="{url page="forthcoming" op="article" path=$articlePath}">
						{$article->getLocalizedTitle()|strip_unsafe_html}
					</a>
					{if $article->getDatePublished()}({$article->getDatePublished()|date_format:$dateFormatShort}){/if}
				</div>

				<div class="meta">
					<div class="authors">
						{$article->getAuthorString()}
					</div>

				</div>

				<ul class="galleys_links">
					{foreach from=$article->getGalleys() item=galley}
						<li>
							{include file=$galley_link parent=$article galley=$galley}
						</li>
					{/foreach}
				</ul>

			</div>
				
			</li>
		{/foreach}
	</ul>
	</div>
</div>

{include file="frontend/components/footer.tpl"}
