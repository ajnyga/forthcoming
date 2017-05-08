{**
 * plugins/generic/preprints/templates/edit.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Edit preprint 
 *
 *}
{fbvFormArea id="preprint"}
	{fbvFormSection list="true"}
		{fbvElement type="checkbox" id="preprint" label="plugins.generic.preprints.fieldDescription" checked=$preprint|compare:true}
	{/fbvFormSection}
{/fbvFormArea}