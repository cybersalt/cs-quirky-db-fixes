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
use Joomla\Database\DatabaseInterface;

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
    protected $minimumJoomla = '4.2.0';

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
                Text::sprintf('COM_CSQUIRKYDBFIXES_ERROR_PHP_VERSION', $this->minimumPhp, PHP_VERSION),
                'error'
            );
            return false;
        }

        // Check Joomla version
        if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('COM_CSQUIRKYDBFIXES_ERROR_JOOMLA_VERSION', $this->minimumJoomla, JVERSION),
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

        // Restrict to Super Users on install, and on update for sites that never set permissions
        $this->setDefaultPermissions();

        // Show post-install message with link to the component
        $this->showPostInstallMessage($type);

        return true;
    }

    /**
     * Display a post-install message with a link to the component
     *
     * @param   string  $type  Type of change (install, update, or discover_install)
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function showPostInstallMessage(string $type): void
    {
        $messageKey = $type === 'update'
            ? 'COM_CSQUIRKYDBFIXES_POSTINSTALL_UPDATED'
            : 'COM_CSQUIRKYDBFIXES_POSTINSTALL_INSTALLED';
        $url = 'index.php?option=com_csquirkydbfixes';

        echo '<div class="card mb-3" style="margin: 20px 0;">'
            . '<div class="card-body">'
            . '<h3 class="card-title">' . Text::_('COM_CSQUIRKYDBFIXES') . '</h3>'
            . '<p class="card-text">' . Text::_($messageKey) . '</p>'
            . '<a href="' . $url . '" class="btn btn-primary text-white">'
            . '<span class="icon-wrench" aria-hidden="true"></span> '
            . Text::_('COM_CSQUIRKYDBFIXES_POSTINSTALL_OPEN')
            . '</a>'
            . '</div></div>';
    }

    /**
     * Set default component permissions to Super Users only
     *
     * Denies core.admin and core.manage to the root user group (Public), which
     * every other group inherits from. Super Users pass every permission check,
     * so they keep access while the component and its menu item are hidden from
     * everyone else. Rules that have already been set are left alone.
     *
     * @return  void
     *
     * @since   1.2.0
     */
    protected function setDefaultPermissions(): void
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Get the component's asset
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'rules']))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = ' . $db->quote('com_csquirkydbfixes'));

            $db->setQuery($query);
            $asset = $db->loadObject();

            if (!$asset || !empty(json_decode((string) $asset->rules, true))) {
                return;
            }

            // Get the root user group (Public)
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__usergroups'))
                ->where($db->quoteName('parent_id') . ' = 0');

            $db->setQuery($query);
            $rootGroupId = (int) $db->loadResult();

            if (!$rootGroupId) {
                return;
            }

            $rules = json_encode([
                'core.admin'  => [$rootGroupId => 0],
                'core.manage' => [$rootGroupId => 0],
            ]);

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__assets'))
                ->set($db->quoteName('rules') . ' = ' . $db->quote($rules))
                ->where($db->quoteName('id') . ' = ' . (int) $asset->id);

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
                    Text::_('COM_CSQUIRKYDBFIXES_ERROR_DELETE_CACHE'),
                    'warning'
                );
            }
        }
    }
}
