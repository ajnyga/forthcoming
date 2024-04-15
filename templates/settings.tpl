{**
 * plugins/generic/forthcoming/settings.tpl
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2014-2024 John Willinsky
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

	{fbvFormArea id="forthcomingSeriesIdFormArea"}
		{fbvElement type="select" id="forthcomingSeriesId" translate=false from=$series selected=$forthcomingSeriesId}
	{/fbvFormArea}

	{fbvFormButtons}
</form>
