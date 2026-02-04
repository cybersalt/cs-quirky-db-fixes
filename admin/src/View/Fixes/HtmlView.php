<?php

/**
 * @package     Cybersalt.Component.CsQuirkyDbFixes
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Cybersalt\Component\CsQuirkyDbFixes\Administrator\View\Fixes;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Fixes View
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Array of available fixes
     *
     * @var    array
     * @since  1.0.0
     */
    protected $fixes;

    /**
     * Diagnostics data for each fix
     *
     * @var    array
     * @since  1.0.0
     */
    protected $diagnostics;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function display($tpl = null): void
    {
        /** @var \Cybersalt\Component\CsQuirkyDbFixes\Administrator\Model\FixesModel $model */
        $model = $this->getModel();

        $this->fixes       = $model->getFixes();
        $this->diagnostics = $model->getDiagnostics();

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_CSQUIRKYDBFIXES'), 'wrench');
        ToolbarHelper::preferences('com_csquirkydbfixes');
    }
}
