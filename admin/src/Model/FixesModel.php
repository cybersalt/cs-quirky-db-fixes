<?php

/**
 * @package     Cybersalt.Component.CsQuirkyDbFixes
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Cybersalt\Component\CsQuirkyDbFixes\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Fixes Model
 *
 * @since  1.0.0
 */
class FixesModel extends BaseDatabaseModel
{
    /**
     * Get all available fixes
     *
     * @return  array  Array of fix definitions
     *
     * @since   1.0.0
     */
    public function getFixes(): array
    {
        return [
            'missing_workflow' => [
                'id'              => 'missing_workflow',
                'name'            => Text::_('COM_CSQUIRKYDBFIXES_FIX_MISSING_WORKFLOW_NAME'),
                'description'     => Text::_('COM_CSQUIRKYDBFIXES_FIX_MISSING_WORKFLOW_DESC'),
                'category'        => Text::_('COM_CSQUIRKYDBFIXES_CATEGORY_MIGRATION'),
                'author'          => 'Brian Teeman',
                'author_url'      => 'https://forum.joomla.org/viewtopic.php?t=991923',
                'reference'       => 'https://www.cybersalt.com/joomla-training-cohort/no-articles-appearing-in-the-article-manager-after-migrating-to-joomla-4',
                'diagnostic_key'  => 'COM_CSQUIRKYDBFIXES_WORKFLOW_RECORD_MISSING',
                'sql_code'        => "INSERT IGNORE INTO `#__workflows` \n"
                    . "  (`id`, `asset_id`, `published`, `title`, `description`, \n"
                    . "   `extension`, `default`, `ordering`, `created`, `created_by`, \n"
                    . "   `modified`, `modified_by`, `checked_out_time`, `checked_out`) \n"
                    . "VALUES \n"
                    . "  (1, 0, 1, 'COM_WORKFLOW_BASIC_WORKFLOW', '', \n"
                    . "   'com_content.article', 1, 1, CURRENT_TIMESTAMP(), 0, \n"
                    . "   CURRENT_TIMESTAMP(), 0, NULL, NULL);",
            ],
            'workflow_associations' => [
                'id'              => 'workflow_associations',
                'name'            => Text::_('COM_CSQUIRKYDBFIXES_FIX_WORKFLOW_ASSOCIATIONS_NAME'),
                'description'     => Text::_('COM_CSQUIRKYDBFIXES_FIX_WORKFLOW_ASSOCIATIONS_DESC'),
                'category'        => Text::_('COM_CSQUIRKYDBFIXES_CATEGORY_MIGRATION'),
                'author'          => 'Brian Teeman',
                'author_url'      => 'https://forum.joomla.org/viewtopic.php?t=991923',
                'reference'       => 'https://www.cybersalt.com/joomla-training-cohort/some-or-all-articles-not-appearing-in-the-article-manager-after-migrating-to-joomla-4',
                'diagnostic_key'  => 'COM_CSQUIRKYDBFIXES_ARTICLES_MISSING_ASSOCIATIONS',
                'sql_code'        => "INSERT INTO #__workflow_associations \n"
                    . "  (item_id, stage_id, extension) \n"
                    . "SELECT c.id as item_id, '1', 'com_content.article' \n"
                    . "FROM #__content AS c \n"
                    . "WHERE NOT EXISTS (\n"
                    . "  SELECT wa.item_id \n"
                    . "  FROM #__workflow_associations AS wa \n"
                    . "  WHERE wa.item_id = c.id\n"
                    . ");",
            ],
            'smart_search_menu' => [
                'id'              => 'smart_search_menu',
                'name'            => Text::_('COM_CSQUIRKYDBFIXES_FIX_SMART_SEARCH_MENU_NAME'),
                'description'     => Text::_('COM_CSQUIRKYDBFIXES_FIX_SMART_SEARCH_MENU_DESC'),
                'category'        => Text::_('COM_CSQUIRKYDBFIXES_CATEGORY_MAINTENANCE'),
                'author'          => 'Tim Davis',
                'author_url'      => 'https://cybersalt.com/',
                'reference'       => '',
                'diagnostic_key'  => 'COM_CSQUIRKYDBFIXES_SMART_SEARCH_MENU_MISSING',
                'sql_code'        => "-- This fix dynamically:\n"
                    . "-- 1. Finds the Smart Search parent menu ID\n"
                    . "-- 2. Gets the com_finder component ID\n"
                    . "-- 3. Calculates next available menu IDs\n"
                    . "-- 4. Inserts missing submenu items:\n"
                    . "--    - Index, Maps, Filters, Searches\n"
                    . "-- 5. Rebuilds the menu tree structure\n\n"
                    . "INSERT INTO #__menu (...) VALUES\n"
                    . "  (nextId, 'main', 'com_finder_index', ...),\n"
                    . "  (nextId+1, 'main', 'com_finder_maps', ...),\n"
                    . "  (nextId+2, 'main', 'com_finder_filters', ...),\n"
                    . "  (nextId+3, 'main', 'com_finder_searches', ...);",
            ],
        ];
    }

    /**
     * Run a specific fix
     *
     * @param   string  $fixId  The fix identifier
     *
     * @return  array  Result array with 'success', 'affected_rows', and 'message' keys
     *
     * @since   1.0.0
     * @throws  \Exception
     */
    public function runFix(string $fixId): array
    {
        $fixes = $this->getFixes();

        if (!isset($fixes[$fixId])) {
            return [
                'success'       => false,
                'affected_rows' => 0,
                'message'       => Text::_('COM_CSQUIRKYDBFIXES_ERROR_FIX_NOT_FOUND'),
            ];
        }

        $method = 'fix' . str_replace('_', '', ucwords($fixId, '_'));

        if (!method_exists($this, $method)) {
            return [
                'success'       => false,
                'affected_rows' => 0,
                'message'       => Text::_('COM_CSQUIRKYDBFIXES_ERROR_FIX_METHOD_NOT_FOUND'),
            ];
        }

        return $this->$method();
    }

    /**
     * Fix missing default workflow record for com_content.article
     *
     * After migrating from Joomla 3 to Joomla 4/5, articles don't appear in the
     * Article Manager because the default workflow record is missing from the
     * #__workflows table.
     *
     * @return  array  Result array
     *
     * @since   1.0.0
     */
    protected function fixMissingWorkflow(): array
    {
        $db = $this->getDatabase();

        try {
            // Use INSERT IGNORE to only insert if the record doesn't exist
            $query = "INSERT IGNORE INTO " . $db->quoteName('#__workflows') . " "
                . "(" . $db->quoteName('id') . ", "
                . $db->quoteName('asset_id') . ", "
                . $db->quoteName('published') . ", "
                . $db->quoteName('title') . ", "
                . $db->quoteName('description') . ", "
                . $db->quoteName('extension') . ", "
                . $db->quoteName('default') . ", "
                . $db->quoteName('ordering') . ", "
                . $db->quoteName('created') . ", "
                . $db->quoteName('created_by') . ", "
                . $db->quoteName('modified') . ", "
                . $db->quoteName('modified_by') . ", "
                . $db->quoteName('checked_out_time') . ", "
                . $db->quoteName('checked_out') . ") "
                . "VALUES (1, 0, 1, 'COM_WORKFLOW_BASIC_WORKFLOW', '', 'com_content.article', 1, 1, "
                . "CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, NULL, NULL)";

            $db->setQuery($query);
            $db->execute();

            $affectedRows = $db->getAffectedRows();

            return [
                'success'       => true,
                'affected_rows' => $affectedRows,
                'message'       => Text::sprintf('COM_CSQUIRKYDBFIXES_FIX_MISSING_WORKFLOW_RESULT', $affectedRows),
            ];
        } catch (\Exception $e) {
            return [
                'success'       => false,
                'affected_rows' => 0,
                'message'       => $e->getMessage(),
            ];
        }
    }

    /**
     * Fix missing workflow associations for articles migrated from Joomla 3
     *
     * After migrating from Joomla 3 to Joomla 4/5, some or all articles don't appear
     * in the Article Manager because they lack entries in the #__workflow_associations table.
     *
     * @return  array  Result array
     *
     * @since   1.0.0
     */
    protected function fixWorkflowAssociations(): array
    {
        $db = $this->getDatabase();

        try {
            // Insert workflow associations for articles that don't have them
            $query = "INSERT INTO " . $db->quoteName('#__workflow_associations') . " "
                . "(" . $db->quoteName('item_id') . ", "
                . $db->quoteName('stage_id') . ", "
                . $db->quoteName('extension') . ") "
                . "SELECT c." . $db->quoteName('id') . " AS " . $db->quoteName('item_id') . ", "
                . "'1', 'com_content.article' "
                . "FROM " . $db->quoteName('#__content', 'c') . " "
                . "WHERE NOT EXISTS ("
                . "SELECT wa." . $db->quoteName('item_id') . " "
                . "FROM " . $db->quoteName('#__workflow_associations', 'wa') . " "
                . "WHERE wa." . $db->quoteName('item_id') . " = c." . $db->quoteName('id')
                . ")";

            $db->setQuery($query);
            $db->execute();

            $affectedRows = $db->getAffectedRows();

            return [
                'success'       => true,
                'affected_rows' => $affectedRows,
                'message'       => Text::sprintf('COM_CSQUIRKYDBFIXES_FIX_WORKFLOW_ASSOCIATIONS_RESULT', $affectedRows),
            ];
        } catch (\Exception $e) {
            return [
                'success'       => false,
                'affected_rows' => 0,
                'message'       => $e->getMessage(),
            ];
        }
    }

    /**
     * Fix missing Smart Search submenu items in administrator menu
     *
     * Sometimes the Smart Search submenu items (Index, Maps, Filters, Searches)
     * go missing from the Components menu. This fix recreates them.
     *
     * @return  array  Result array
     *
     * @since   1.0.0
     */
    protected function fixSmartSearchMenu(): array
    {
        $db = $this->getDatabase();

        try {
            // Define the 4 submenu items that should exist
            $submenus = [
                'index'    => ['title' => 'com_finder_index', 'alias' => 'Smart-Search-Index', 'view' => 'index', 'icon' => 'class:finder'],
                'maps'     => ['title' => 'com_finder_maps', 'alias' => 'Smart-Search-Maps', 'view' => 'maps', 'icon' => 'class:finder-maps'],
                'filters'  => ['title' => 'com_finder_filters', 'alias' => 'Smart-Search-Filters', 'view' => 'filters', 'icon' => 'class:finder-filters'],
                'searches' => ['title' => 'com_finder_searches', 'alias' => 'Smart-Search-Searches', 'view' => 'searches', 'icon' => 'class:finder-searches'],
            ];

            // Get the parent Smart Search menu item
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('rgt')])
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('menutype') . ' = ' . $db->quote('main'))
                ->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_finder'))
                ->where($db->quoteName('client_id') . ' = 1');
            $db->setQuery($query);
            $parent = $db->loadObject();

            if (!$parent) {
                return [
                    'success'       => false,
                    'affected_rows' => 0,
                    'message'       => Text::_('COM_CSQUIRKYDBFIXES_ERROR_SMART_SEARCH_PARENT_NOT_FOUND'),
                ];
            }

            $parentId = (int) $parent->id;

            // Get the component_id for com_finder
            $query = $db->getQuery(true)
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_finder'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            $db->setQuery($query);
            $componentId = (int) $db->loadResult();

            if (!$componentId) {
                return [
                    'success'       => false,
                    'affected_rows' => 0,
                    'message'       => Text::_('COM_CSQUIRKYDBFIXES_ERROR_FINDER_COMPONENT_NOT_FOUND'),
                ];
            }

            // Check which submenu items are missing
            $missingItems = [];
            foreach ($submenus as $key => $item) {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('menutype') . ' = ' . $db->quote('main'))
                    ->where($db->quoteName('link') . ' LIKE ' . $db->quote('%com_finder&view=' . $item['view'] . '%'))
                    ->where($db->quoteName('client_id') . ' = 1');
                $db->setQuery($query);

                if ((int) $db->loadResult() === 0) {
                    $missingItems[$key] = $item;
                }
            }

            if (empty($missingItems)) {
                return [
                    'success'       => true,
                    'affected_rows' => 0,
                    'message'       => Text::_('COM_CSQUIRKYDBFIXES_SMART_SEARCH_MENU_ALREADY_EXISTS'),
                ];
            }

            // Get the max menu ID
            $query = $db->getQuery(true)
                ->select('MAX(' . $db->quoteName('id') . ')')
                ->from($db->quoteName('#__menu'));
            $db->setQuery($query);
            $maxId = (int) $db->loadResult();

            // Get the max lft value
            $query = $db->getQuery(true)
                ->select('MAX(' . $db->quoteName('lft') . ')')
                ->from($db->quoteName('#__menu'));
            $db->setQuery($query);
            $maxLft = (int) $db->loadResult();

            // Insert the missing menu items
            $insertedCount = 0;
            $nextId = $maxId + 1;
            $nextLft = $maxLft + 1;

            foreach ($missingItems as $key => $item) {
                $columns = [
                    'id', 'menutype', 'title', 'alias', 'note', 'path', 'link', 'type',
                    'published', 'parent_id', 'level', 'component_id', 'checked_out',
                    'checked_out_time', 'browserNav', 'access', 'img', 'template_style_id',
                    'params', 'lft', 'rgt', 'home', 'language', 'client_id', 'publish_up', 'publish_down'
                ];

                $values = [
                    $nextId,
                    $db->quote('main'),
                    $db->quote($item['title']),
                    $db->quote($item['alias']),
                    $db->quote(''),
                    $db->quote('Smart Search/' . $item['alias']),
                    $db->quote('index.php?option=com_finder&view=' . $item['view']),
                    $db->quote('component'),
                    1,
                    $parentId,
                    2,
                    $componentId,
                    'NULL',
                    'NULL',
                    0,
                    0,
                    $db->quote($item['icon']),
                    0,
                    $db->quote(''),
                    $nextLft,
                    $nextLft + 1,
                    0,
                    $db->quote('*'),
                    1,
                    'NULL',
                    'NULL'
                ];

                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__menu'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                $db->execute();

                $insertedCount++;
                $nextId++;
                $nextLft += 2;
            }

            // Rebuild the menu tree using Joomla's table class
            $this->rebuildMenuTree();

            return [
                'success'       => true,
                'affected_rows' => $insertedCount,
                'message'       => Text::sprintf('COM_CSQUIRKYDBFIXES_FIX_SMART_SEARCH_MENU_RESULT', $insertedCount),
            ];
        } catch (\Exception $e) {
            return [
                'success'       => false,
                'affected_rows' => 0,
                'message'       => $e->getMessage(),
            ];
        }
    }

    /**
     * Rebuild the menu tree to fix lft/rgt values
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function rebuildMenuTree(): void
    {
        try {
            $table = Factory::getApplication()->bootComponent('com_menus')
                ->getMVCFactory()
                ->createTable('Menu', 'Administrator');

            if ($table) {
                $table->rebuild();
            }
        } catch (\Exception $e) {
            // Silently fail - the menu items are inserted, just tree might need manual rebuild
        }
    }

    /**
     * Check if the default workflow record exists
     *
     * @return  bool  True if the workflow is missing, false if it exists
     *
     * @since   1.0.0
     */
    public function isDefaultWorkflowMissing(): bool
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__workflows'))
            ->where($db->quoteName('id') . ' = 1')
            ->where($db->quoteName('extension') . ' = ' . $db->quote('com_content.article'));

        $db->setQuery($query);

        return (int) $db->loadResult() === 0;
    }

    /**
     * Get count of articles missing workflow associations
     *
     * @return  int  Number of articles without workflow associations
     *
     * @since   1.0.0
     */
    public function getMissingWorkflowAssociationsCount(): int
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__content', 'c'))
            ->where(
                'NOT EXISTS ('
                . $db->getQuery(true)
                    ->select('1')
                    ->from($db->quoteName('#__workflow_associations', 'wa'))
                    ->where('wa.' . $db->quoteName('item_id') . ' = c.' . $db->quoteName('id'))
                . ')'
            );

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Get count of missing Smart Search submenu items
     *
     * @return  int  Number of missing submenu items (0-4)
     *
     * @since   1.0.0
     */
    public function getMissingSmartSearchMenuCount(): int
    {
        $db = $this->getDatabase();

        $views = ['index', 'maps', 'filters', 'searches'];
        $missingCount = 0;

        foreach ($views as $view) {
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('menutype') . ' = ' . $db->quote('main'))
                ->where($db->quoteName('link') . ' LIKE ' . $db->quote('%com_finder&view=' . $view . '%'))
                ->where($db->quoteName('client_id') . ' = 1');
            $db->setQuery($query);

            if ((int) $db->loadResult() === 0) {
                $missingCount++;
            }
        }

        return $missingCount;
    }

    /**
     * Get a SQL backup of tables affected by a specific fix
     *
     * @param   string  $fixId  The fix identifier
     *
     * @return  array  Result array with 'success', 'filename', 'content', and 'message' keys
     *
     * @since   1.0.0
     */
    public function getBackup(string $fixId): array
    {
        // Map fix IDs to their affected tables
        $tableMap = [
            'missing_workflow'      => ['#__workflows', '#__workflow_stages', '#__workflow_transitions'],
            'workflow_associations' => ['#__workflow_associations'],
            'smart_search_menu'     => ['#__menu'],
        ];

        if (!isset($tableMap[$fixId])) {
            return [
                'success' => false,
                'message' => Text::_('COM_CSQUIRKYDBFIXES_ERROR_FIX_NOT_FOUND'),
            ];
        }

        $db = $this->getDatabase();
        $tables = $tableMap[$fixId];
        $output = [];

        // Build real table names with prefix for filename
        $realTableNames = [];
        foreach ($tables as $table) {
            $realTableNames[] = str_replace('#__', $db->getPrefix(), $table);
        }

        // Add header
        $output[] = "-- CS Quirky DB Fixes - Backup";
        $output[] = "-- Fix: " . $fixId;
        $output[] = "-- Date: " . Factory::getDate()->format('Y-m-d H:i:s');
        $output[] = "-- Tables: " . implode(', ', $realTableNames);
        $output[] = "";
        $output[] = "SET FOREIGN_KEY_CHECKS=0;";
        $output[] = "";

        foreach ($tables as $table) {
            $realTable = str_replace('#__', $db->getPrefix(), $table);

            // Get table structure
            $output[] = "-- --------------------------------------------------------";
            $output[] = "-- Table structure for table `" . $realTable . "`";
            $output[] = "-- --------------------------------------------------------";
            $output[] = "";

            try {
                $db->setQuery("SHOW CREATE TABLE " . $db->quoteName($realTable));
                $createTable = $db->loadRow();

                if ($createTable && isset($createTable[1])) {
                    $output[] = "DROP TABLE IF EXISTS `" . $realTable . "`;";
                    $output[] = $createTable[1] . ";";
                    $output[] = "";
                }

                // Get table data
                $output[] = "-- --------------------------------------------------------";
                $output[] = "-- Data for table `" . $realTable . "`";
                $output[] = "-- --------------------------------------------------------";
                $output[] = "";

                $query = $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName($realTable));
                $db->setQuery($query);
                $rows = $db->loadAssocList();

                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $columns = array_keys($row);
                        $values = array_map(function ($value) use ($db) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return $db->quote($value);
                        }, array_values($row));

                        $output[] = "INSERT INTO `" . $realTable . "` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
                    }
                    $output[] = "";
                }
            } catch (\Exception $e) {
                $output[] = "-- Error backing up table " . $realTable . ": " . $e->getMessage();
                $output[] = "";
            }
        }

        $output[] = "SET FOREIGN_KEY_CHECKS=1;";
        $output[] = "";
        $output[] = "-- End of backup";

        // Generate filename - use "table" for single, "tables" for multiple
        if (count($realTableNames) === 1) {
            $tableNamePart = $realTableNames[0] . '_table';
        } else {
            // Use a short descriptive name for multiple tables
            $tableNamePart = $db->getPrefix() . 'workflow_tables';
        }
        $filename = 'backup_of_' . $tableNamePart . '_' . Factory::getDate()->format('Y-m-d_His') . '.sql';

        return [
            'success'  => true,
            'filename' => $filename,
            'content'  => implode("\n", $output),
            'message'  => '',
        ];
    }

    /**
     * Get list of disabled workflow plugins
     *
     * If core workflow plugins are disabled, new articles won't get workflow
     * associations, causing the same "missing associations" problem to recur
     * after it has been fixed.
     *
     * @return  array  Array of disabled plugin names (empty if all are enabled)
     *
     * @since   1.0.0
     */
    public function getDisabledWorkflowPlugins(): array
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select([$db->quoteName('name'), $db->quoteName('element')])
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('workflow'))
            ->where($db->quoteName('enabled') . ' = 0');

        $db->setQuery($query);
        $results = $db->loadAssocList();

        return $results ?: [];
    }

    /**
     * Get diagnostics for all fixes
     *
     * @return  array  Array of fix IDs to diagnostic info
     *
     * @since   1.0.0
     */
    public function getDiagnostics(): array
    {
        return [
            'missing_workflow' => [
                'is_missing'    => $this->isDefaultWorkflowMissing(),
                'missing_count' => null,
            ],
            'workflow_associations' => [
                'is_missing'            => false,
                'missing_count'         => $this->getMissingWorkflowAssociationsCount(),
                'disabled_plugins'      => $this->getDisabledWorkflowPlugins(),
            ],
            'smart_search_menu' => [
                'is_missing'    => false,
                'missing_count' => $this->getMissingSmartSearchMenuCount(),
            ],
        ];
    }
}
