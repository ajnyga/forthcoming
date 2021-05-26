{**
 * plugins/generic/forthcoming/settings.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * ForthcomingPlugin settings
 *
 *}
<script>
	$(function() {ldelim}
		$('#forthcomingPluginSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="forthcomingPluginSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">

	{csrf}

	{fbvFormArea id="forthcomingDescriptionFormArea"}
		<div id="description">{translate key="plugins.generic.forthcoming.settings.description"}</div>
	{/fbvFormArea}

	{fbvFormArea id="forthcomingIssueIdFormArea"}
		{fbvElement type="select" id="forthcomingIssueId" translate=false from=$issues selected=$forthcomingIssueId}
	{/fbvFormArea}

	{fbvFormButtons}

</form>
