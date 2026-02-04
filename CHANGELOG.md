# Changelog

## 🚀 Version 1.0.0 (February 2026)

### 📦 Initial Release

- **Fix Missing Default Workflow**: Fixes the issue where NO articles appear in the Article Manager after migrating from Joomla 3 to Joomla 4/5 because the default workflow record is missing from the `#__workflows` table
- **Fix Missing Workflow Associations**: Fixes the issue where SOME or ALL articles don't appear in the Article Manager after migration because they lack entries in the `#__workflow_associations` table
- **Fix Missing Smart Search Menu Items**: Recreates the missing Index, Maps, Filters, and Searches submenu items under Components > Smart Search when they disappear from the administrator menu

### 🔧 Features

- **Diagnostic checks**: Each fix includes a diagnostic that shows whether the issue exists on your site before running
- **Table backup**: Download a SQL backup of affected tables before running any fix
- **Confirmation required**: Run Fix button is protected by a checkbox confirmation to ensure backups are taken
- **Status indicators**: Visual indicators show which fixes need attention (warning) vs which are already resolved (success)
