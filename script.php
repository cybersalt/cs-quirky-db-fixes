<?php

/**
 * @package     Cybersalt.Component.CsQuirkyDbFixes
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;

/**
 * Component installer script
 *
 * @since  1.0.0
 */
class Com_CsquirkydbfixesInstallerScript
{
    /**
     * Minimum Joomla version required
     *
     * @var    string
     * @since  1.0.0
     */
    protected $minimumJoomla = '5.0.0';

    /**
     * Minimum PHP version required
     *
     * @var    string
     * @since  1.0.0
     */
    protected $minimumPhp = '8.1.0';

    /**
     * Method to check requirements before install
     *
     * @param   string            $type    Type of change (install, update, or discover_install)
     * @param   InstallerAdapter  $parent  The adapter calling this method
     *
     * @return  boolean  True to continue, false to abort
     *
     * @since   1.0.0
     */
    public function preflight($type, $parent): bool
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
            Factory::getApplication()->enqueueMessage(
                sprintf('PHP %s or higher is required. You are running PHP %s.', $this->minimumPhp, PHP_VERSION),
                'error'
            );
            return false;
        }

        // Check Joomla version
        if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Factory::getApplication()->enqueueMessage(
                sprintf('Joomla %s or higher is required. You are running Joomla %s.', $this->minimumJoomla, JVERSION),
                'error'
            );
            return false;
        }

        return true;
    }

    /**
     * Runs after install/update
     *
     * @param   string            $type    Type of change (install, update, or discover_install)
     * @param   InstallerAdapter  $parent  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function postflight($type, $parent): bool
    {
        // Clear autoload cache
        $this->clearAutoloadCache();

        // On fresh install, set permissions to Super Users only
        if ($type === 'install') {
            $this->setDefaultPermissions();
        }

        return true;
    }

    /**
     * Set default component permissions to Super Users only
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function setDefaultPermissions(): void
    {
        try {
            $db = Factory::getDbo();

            // Get the component's asset_id
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = ' . $db->quote('com_csquirkydbfixes'));

            $db->setQuery($query);
            $assetId = (int) $db->loadResult();

            if (!$assetId) {
                return;
            }

            // Set rules: deny all by default, allow Super Users (group 8) for core.admin and core.manage
            $rules = '{"core.admin":{"8":1},"core.manage":{"8":1}}';

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__assets'))
                ->set($db->quoteName('rules') . ' = ' . $db->quote($rules))
                ->where($db->quoteName('id') . ' = ' . $assetId);

            $db->setQuery($query);
            $db->execute();
        } catch (\Exception $e) {
            // Silently fail - permissions can be set manually
        }
    }

    /**
     * Runs on uninstall
     *
     * @param   InstallerAdapter  $parent  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   1.0.0
     */
    public function uninstall($parent): bool
    {
        $this->clearAutoloadCache();

        return true;
    }

    /**
     * Clear the autoload cache to ensure classes are found
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function clearAutoloadCache(): void
    {
        $cacheFile = JPATH_ADMINISTRATOR . '/cache/autoload_psr4.php';

        if (file_exists($cacheFile)) {
            try {
                @unlink($cacheFile);
            } catch (\Exception $e) {
                Factory::getApplication()->enqueueMessage(
                    'Please manually delete administrator/cache/autoload_psr4.php',
                    'warning'
                );
            }
        }
    }
}
