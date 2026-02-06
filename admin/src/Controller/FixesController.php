<?php

/**
 * @package     Cybersalt.Component.CsQuirkyDbFixes
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Cybersalt\Component\CsQuirkyDbFixes\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Fixes Controller
 *
 * @since  1.0.0
 */
class FixesController extends BaseController
{
    /**
     * Execute a database fix
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function runfix()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $app = Factory::getApplication();
        $fixId = $app->input->getString('fix_id', '');

        if (empty($fixId)) {
            $app->enqueueMessage(Text::_('COM_CSQUIRKYDBFIXES_ERROR_NO_FIX_SELECTED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_csquirkydbfixes&view=fixes', false));
            return;
        }

        /** @var \Cybersalt\Component\CsQuirkyDbFixes\Administrator\Model\FixesModel $model */
        $model = $this->getModel('Fixes');

        try {
            $result = $model->runFix($fixId);

            if ($result['success']) {
                $app->enqueueMessage(
                    Text::sprintf('COM_CSQUIRKYDBFIXES_FIX_SUCCESS', $result['affected_rows']),
                    'success'
                );
            } else {
                $app->enqueueMessage(
                    Text::sprintf('COM_CSQUIRKYDBFIXES_FIX_ERROR', $result['message']),
                    'error'
                );
            }
        } catch (\Exception $e) {
            $app->enqueueMessage(
                Text::sprintf('COM_CSQUIRKYDBFIXES_FIX_ERROR', $e->getMessage()),
                'error'
            );
        }

        $this->setRedirect(Route::_('index.php?option=com_csquirkydbfixes&view=fixes', false));
    }

    /**
     * Download a backup of tables affected by a fix
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function backup()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        $app = Factory::getApplication();
        $fixId = $app->input->getString('fix_id', '');

        if (empty($fixId)) {
            $app->enqueueMessage(Text::_('COM_CSQUIRKYDBFIXES_ERROR_NO_FIX_SELECTED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_csquirkydbfixes&view=fixes', false));
            return;
        }

        /** @var \Cybersalt\Component\CsQuirkyDbFixes\Administrator\Model\FixesModel $model */
        $model = $this->getModel('Fixes');

        try {
            $backup = $model->getBackup($fixId);

            if ($backup['success']) {
                // Set headers for file download
                $app->setHeader('Content-Type', 'application/sql', true);
                $app->setHeader('Content-Disposition', 'attachment; filename="' . $backup['filename'] . '"', true);
                $app->setHeader('Cache-Control', 'no-cache, must-revalidate', true);
                $app->setHeader('Pragma', 'no-cache', true);
                $app->sendHeaders();

                echo $backup['content'];
                $app->close();
            } else {
                $app->enqueueMessage($backup['message'], 'error');
                $this->setRedirect(Route::_('index.php?option=com_csquirkydbfixes&view=fixes', false));
            }
        } catch (\Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $this->setRedirect(Route::_('index.php?option=com_csquirkydbfixes&view=fixes', false));
        }
    }

}
