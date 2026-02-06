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
