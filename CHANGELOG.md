# Changelog

## 🚀 Version 1.2.0 (November 2026)

### 🔒 Security

- **Super Users only**: The component now only opens for Super Users. Before, any administrator who could manage components could run the database fixes and download table backups. Thanks to [Phil Taylor](https://github.com/PhilETaylor) for the pull request (#1)
- **Checked on every request**: The Super User check runs in the component's dispatcher, so it covers every view and task, with extra checks in the view and in the Run Fix and Download Backup actions
- **Menu item hidden for everyone else**: On install, and on update for sites that never set permissions for the component, Public is denied Configure and Access Administration Interface. Every user group inherits that, so only Super Users see Components > CS Quirky DB Fixes. Permissions you have already set are left alone

### 📦 New Features

- **Joomla 4.2+ support**: The same ZIP now installs on Joomla 4.2 and later, so you can repair the workflow and Smart Search tables on a Joomla 4 site before upgrading it to Joomla 5

### 🐛 Bug Fixes

- **Joomla changelog**: The changelog link in the manifest now points to a Joomla changelog XML file, so System > Manage > Extensions > Changelog works

## 🚀 Version 1.1.0 (February 2026)

### 📦 New Features

- **Fix Missing Smart Search Tokens Table**: Recreates the `finder_tokens` MEMORY table that is lost when MySQL restarts, causing "Table finder_tokens doesn't exist" errors
- **Fix Missing Smart Search Tokens Aggregate Table**: Recreates the `finder_tokens_aggregate` MEMORY table that is also lost on MySQL restart
- **Auto-detect collation**: Smart Search table fixes automatically detect the correct collation from existing `finder_terms` table to prevent collation mismatch errors

### 🔧 Improvements

- **Expanded Workflow System Restore (Fix 1)**: Now checks and creates all 4 workflow tables (`workflows`, `workflow_stages`, `workflow_transitions`, `workflow_associations`) instead of just inserting a workflow record. Creates missing tables with full schema and inserts all default records (1 workflow, 1 stage, 7 transitions)
- **Full multi-lingual support**: All user-facing strings now use Joomla language constants, including installation script messages (`script.php`), fix detail messages, and post-install UI
- **15 language translations**: Includes translations for English, Dutch, German, Spanish, French, Italian, Portuguese (Brazil), Russian, Polish, Japanese, Chinese (Simplified), Turkish, Greek, Czech, and Swedish
- **Post-install link**: Installation/update success page now shows a clickable button to open the component directly
- **Atum dark mode compatibility**: Fixed button visibility in Atum dark mode template

### 🐛 Bug Fixes

- **Removed incorrect workflow-enabled diagnostic**: Removed the "Enable Workflows" button and diagnostic since `workflow_enabled` does NOT need to be enabled for articles to save properly
- **Fixed recurring workflow issues**: Root cause identified - the `workflow_stages` and `workflow_transitions` tables were completely missing after J3-to-J4 migration, causing new articles to never receive workflow associations even after Fix 2 was applied

## 🚀 Version 1.0.0 (February 2026)

### 📦 Initial Release

- **Fix Missing Default Workflow**: Fixes the issue where NO articles appear in the Article Manager after migrating from Joomla 3 to Joomla 4 because the default workflow record is missing from the `#__workflows` table
- **Fix Missing Workflow Associations**: Fixes the issue where SOME or ALL articles don't appear in the Article Manager after migration because they lack entries in the `#__workflow_associations` table
- **Fix Missing Smart Search Menu Items**: Recreates the missing Index, Maps, Filters, and Searches submenu items under Components > Smart Search when they disappear from the administrator menu

### 🔧 Features

- **Diagnostic checks**: Each fix includes a diagnostic that shows whether the issue exists on your site before running
- **Table backup**: Download a SQL backup of affected tables before running any fix
- **Confirmation required**: Run Fix button is protected by a checkbox confirmation to ensure backups are taken
- **Status indicators**: Visual indicators show which fixes need attention (warning) vs which are already resolved (success)
