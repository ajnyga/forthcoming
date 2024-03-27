
# Forthcoming articles plugin for OJS 3.4

This is a complete rewrite of the old Forthcoming plugin which changes the whole concept of the plugin and uses the versioning features of OJS 3.2+. The new version supports statistics and using DOIs. Forthcoming articles also have their own landing pages in the same url as the final article.

## Usage

### Settings
1. Create a new issue and name it for example "Forthcoming" and *publish the issue*
2. Enable the Forthcoming plugin from OJS Settings
3. In the plugin settings, choose the Forthcoming issue from the pull down menu and save settings
4. Go to Navigation menu settings and add the Forthcoming listing page to your menu. If you want a different name, you can just create a new custom url menu item and point it to the Forthcoming listing page.

The plugin will hide the Forthcoming issue from the regular issue archive and redirect all traffic from the issue table of contents page to the custom Forthcoming listing page.

### Workflow
1. When you want to add a new article to Forthcoming listing, just choose the Forthcoming issue from the article metadata settings and publish the article. The article is now visible in Forthcoming page.
2. When the actual issue is being published, *create a new version* for the article, change the issue and publish the new version. The article is no longer visible in the Forthcoming page.

Note. I recommend that you do not use the OJS default pattern for DOIs with this plugin.


***
Plugin created by The Federation of Finnish Learned Societies (https://tsv.fi/en/).
***
