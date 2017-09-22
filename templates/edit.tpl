{**
 * plugins/generic/forthcoming/templates/edit.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Edit forthcoming 
 *
 *}
{fbvFormArea id="forthcoming"}
	{fbvFormSection list="true"}
		{fbvElement type="checkbox" id="forthcoming" label="plugins.generic.forthcoming.fieldDescription" checked=$forthcoming|compare:true}
	{/fbvFormSection}
{/fbvFormArea}