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
							<img src="{$article->getLocalizedCoverImageUrl()|escape}"{if $article->getLocalizedCoverImageAltText() != ''} alt="{$article->getLocalizedCoverImageAltText()|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if}>
					</div>
				{/if}

				<div class="title">
						{$article->getLocalizedTitle()|strip_unsafe_html}
					{if $article->getDatePublished()}({$article->getDatePublished()|date_format:$dateFormatShort}){/if}
				</div>

				<div class="meta">
					{if $article->getAuthors()}
						<div class="authors">
							{foreach from=$article->getAuthors() item=author}
								<span>
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
								</span>
							{/foreach}
						</div>
					{/if}
				</div>
				
				{if $article->getLocalizedAbstract()}
					<div class="abstract">
						{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}
					</div>
				{/if}

				{if $licenseUrl}
					<div class="copyright">
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
					</div>
				{/if}		

				<ul class="galleys_links">
					{foreach from=$article->getGalleys() item=galley}
						<li>
							{if $galley->isPdfGalley()}
								{assign var="type" value="pdf"}
							{else}
								{assign var="type" value="file"}
							{/if}

							<a class="obj_galley_link {$type}" href="{url page="forthcoming" op="article" path=$articlePath|to_array:$galley->getBestGalleyId()}">
								{$galley->getGalleyLabel()|escape}
							</a>
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
