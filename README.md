# CS Quirky DB Fixes

A Joomla 5/6 component that provides fixes for quirky database problems that occur from time to time, particularly after migrations from Joomla 3 to Joomla 4/5.

## Features

- **Diagnostic checks** - Each fix includes a diagnostic that shows whether the issue exists on your site
- **Table backup** - Download a SQL backup of affected tables before running any fix
- **Safe execution** - Run Fix button is protected by a checkbox confirmation to ensure backups are taken
- **Visual status** - Indicators show which fixes need attention vs which are already resolved

## Included Fixes

### 1. Fix Missing Default Workflow
**Problem**: After migrating from Joomla 3 to Joomla 4/5, NO articles appear in the Article Manager.

**Cause**: The default workflow record is missing from the `#__workflows` table.

**Solution**: Inserts the required default workflow record along with its stage and transition records.

*Fix by: [Brian Teeman](https://brian.teeman.net/)*

### 2. Fix Missing Workflow Associations
**Problem**: After migrating from Joomla 3 to Joomla 4/5, SOME or ALL articles don't appear in the Article Manager.

**Cause**: Articles lack entries in the `#__workflow_associations` table.

**Solution**: Creates the missing workflow association records for all articles that need them.

*Fix by: [Brian Teeman](https://brian.teeman.net/)*

### 3. Fix Missing Smart Search Menu Items
**Problem**: The Smart Search (Finder) submenu items (Index, Maps, Filters, Searches) disappear from the Components menu in the administrator.

**Cause**: Menu records for these items are missing from the `#__menu` table.

**Solution**: Recreates the missing submenu items under Components > Smart Search.

*Fix by: Tim Davis*

## Installation

1. Download the latest release ZIP file
2. In Joomla Administrator, go to System > Install > Extensions
3. Upload and install the ZIP file
4. Access via Components > CS Quirky DB Fixes

## Usage

1. Review each fix and its diagnostic status
2. Click "Download Backup" to save a backup of affected tables
3. Check the confirmation checkbox
4. Click "Run Fix" to apply the fix
5. Review the results message

## Requirements

- Joomla 5.0+ or Joomla 6.0+
- PHP 8.1+

## License

This project is released under the GNU General Public License v2 or later.

See the [LICENSE](LICENSE) file for details.

## Credits

- **Developer**: [Cybersalt Consulting Ltd.](https://cybersalt.com)
- **Workflow fixes**: Based on solutions by [Brian Teeman](https://brian.teeman.net/)
