{**
 * templates/article.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view an article
 *
 * @uses $article Article This article
 * @uses $section Section The journal section this article is assigned to
 * @uses $journal Journal The journal currently being viewed.
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$article->getLocalizedTitle()|escape}

<div class="page page_article">

<article class="obj_article_details">
	<h1 class="page_title">
		{$article->getLocalizedTitle()|escape}
	</h1>

	{if $article->getLocalizedSubtitle()}
		<h2 class="subtitle">
			{$article->getLocalizedSubtitle()|escape}
		</h2>
	{/if}

	<div class="row">
		<div class="main_entry">
		
			{if $article->getAuthors()}
				<ul class="item authors">
					{foreach from=$article->getAuthors() item=author}
						<li>
							<span class="name">
								{$author->getFullName()|escape}
							</span>
							{if $author->getLocalizedAffiliation()}
								<span class="affiliation">
									{$author->getLocalizedAffiliation()|escape}
								</span>
							{/if}
							{if $author->getOrcid()}
								<span class="orcid">
									<a href="{$author->getOrcid()|escape}" target="_blank">
										{$author->getOrcid()|escape}
									</a>
								</span>
							{/if}
						</li>
					{/foreach}
				</ul>
			{/if}
			

			{* Abstract *}
			{if $article->getLocalizedAbstract()}
				<div class="item abstract">
					<h3 class="label">{translate key="article.abstract"}</h3>
					{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}
				</div>
			{/if}
			
		</div><!-- .main_entry -->

		<div class="entry_details">			
			
			{* Article Galleys *}
			{assign var=galleys value=$article->getGalleys()}
			{if $galleys}
				<div class="item galleys">
					<ul class="value galleys_links">
						{foreach from=$galleys item=galley}
							<li>
								{include file=$galley_link parent=$article galley=$galley}
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}
			
			{if $article->getDatePublished()}
				<div class="item published">
					<div class="label">
						{translate key="submissions.published"}
					</div>
					<div class="value">
						{$article->getDatePublished()|date_format:$dateFormatShort}
					</div>
				</div>
			{/if}			

		{* Licensing info *}
			{if $copyright || $licenseUrl}
				<div class="item copyright">
					{if $licenseUrl}
						{if $ccLicenseBadge}
							{$ccLicenseBadge}
						{else}
							<a href="{$licenseUrl|escape}" class="copyright">
								{if $copyrightHolder}
									{translate key="submission.copyrightStatement" copyrightHolder=$copyrightHolder copyrightYear=$copyrightYear}
								{else}
									{translate key="submission.license"}
								{/if}
							</a>
						{/if}
					{/if}
					{$copyright}
				</div>
			{/if}

	</div><!-- .row -->

</article>

	
	
	

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
