<?php

/**
 * @package     Cybersalt.Component.CsQuirkyDbFixes
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Cybersalt\Component\CsQuirkyDbFixes\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * Component dispatcher for CS Quirky DB Fixes
 *
 * @since  1.2.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Restrict every view and task to Super Users
     *
     * The fixes change database tables and the backups dump them, so the
     * check runs here once instead of relying on each view and task.
     *
     * @return  void
     *
     * @since   1.2.0
     * @throws  NotAllowed
     */
    protected function checkAccess(): void
    {
        parent::checkAccess();

        if (!$this->app->getIdentity()->authorise('core.admin')) {
            throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
