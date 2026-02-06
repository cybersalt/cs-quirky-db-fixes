<?php

/**
 * @package     Cybersalt.Component.CsQuirkyDbFixes
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2026 Cybersalt Consulting Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Cybersalt\Component\CsQuirkyDbFixes\Administrator\View\Fixes\HtmlView $this */

?>
<div class="csquirkydbfixes-container">
    <!-- Warning Banner -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-danger">
                <h4 class="alert-heading">
                    <span class="icon-warning" aria-hidden="true"></span>
                    <?php echo Text::_('COM_CSQUIRKYDBFIXES_WARNING_HEADING'); ?>
                </h4>
                <p class="mb-2"><?php echo Text::_('COM_CSQUIRKYDBFIXES_WARNING_DESC'); ?></p>
                <hr>
                <p class="mb-0">
                    <span class="icon-download" aria-hidden="true"></span>
                    <?php echo Text::_('COM_CSQUIRKYDBFIXES_WARNING_BACKUP_TIP'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h4 class="alert-heading">
                    <span class="icon-info-circle" aria-hidden="true"></span>
                    <?php echo Text::_('COM_CSQUIRKYDBFIXES_INFO_HEADING'); ?>
                </h4>
                <p class="mb-0"><?php echo Text::_('COM_CSQUIRKYDBFIXES_INFO_DESC'); ?></p>
            </div>
        </div>
    </div>

    <?php if (empty($this->fixes)) : ?>
        <div class="alert alert-warning">
            <?php echo Text::_('COM_CSQUIRKYDBFIXES_NO_FIXES_AVAILABLE'); ?>
        </div>
    <?php else : ?>
        <div class="accordion" id="fixesAccordion">
            <?php $index = 0; ?>
            <?php foreach ($this->fixes as $fixId => $fix) : ?>
                <?php
                $diagnostic = $this->diagnostics[$fixId] ?? [];
                $isMissing = $diagnostic['is_missing'] ?? false;
                $missingCount = $diagnostic['missing_count'] ?? 0;
                $hasIssues = $isMissing || $missingCount > 0;
                $statusClass = $hasIssues ? 'border-warning' : 'border-success';
                $statusIcon = $hasIssues ? 'icon-warning text-warning' : 'icon-check-circle text-success';
                $collapseId = 'collapse-' . $fixId;
                $headingId = 'heading-' . $fixId;
                ?>
                <div class="accordion-item <?php echo $statusClass; ?>" style="border-width: 2px;">
                    <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?php echo $collapseId; ?>"
                                aria-expanded="false"
                                aria-controls="<?php echo $collapseId; ?>">
                            <span class="<?php echo $statusIcon; ?> me-2" aria-hidden="true"></span>
                            <strong><?php echo $this->escape($fix['name']); ?></strong>
                            <span class="badge bg-secondary ms-2"><?php echo $fix['category']; ?></span>
                            <?php if ($hasIssues) : ?>
                                <span class="badge bg-warning text-dark ms-2">
                                    <?php echo Text::_('COM_CSQUIRKYDBFIXES_ACTION_NEEDED'); ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    </h2>
                    <div id="<?php echo $collapseId; ?>"
                         class="accordion-collapse collapse"
                         aria-labelledby="<?php echo $headingId; ?>"
                         data-bs-parent="#fixesAccordion">
                        <div class="accordion-body">
                            <!-- Description -->
                            <div class="mb-3">
                                <h5><?php echo Text::_('COM_CSQUIRKYDBFIXES_PROBLEM'); ?></h5>
                                <p><?php echo $this->escape($fix['description']); ?></p>
                            </div>

                            <!-- Diagnostic Status -->
                            <div class="mb-3">
                                <h5><?php echo Text::_('COM_CSQUIRKYDBFIXES_DIAGNOSTIC'); ?></h5>
                                <?php if ($hasIssues) : ?>
                                    <div class="alert alert-warning">
                                        <span class="icon-warning" aria-hidden="true"></span>
                                        <?php
                                        $diagnosticKey = $fix['diagnostic_key'] ?? 'COM_CSQUIRKYDBFIXES_ISSUES_DETECTED';
                                        if ($missingCount > 0) {
                                            echo Text::sprintf($diagnosticKey, $missingCount);
                                        } else {
                                            echo Text::_($diagnosticKey);
                                        }
                                        ?>
                                    </div>
                                <?php else : ?>
                                    <div class="alert alert-success">
                                        <span class="icon-check-circle" aria-hidden="true"></span>
                                        <?php echo Text::_('COM_CSQUIRKYDBFIXES_NO_ISSUES_FOUND'); ?>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $workflowEnabled = $diagnostic['workflow_enabled'] ?? 'enabled';
                                if ($workflowEnabled !== 'enabled') :
                                ?>
                                    <div class="alert alert-danger">
                                        <div class="mb-2">
                                            <span class="icon-notification-circle" aria-hidden="true"></span>
                                            <strong><?php echo Text::_('COM_CSQUIRKYDBFIXES_WORKFLOW_NOT_ENABLED_HEADING'); ?></strong>
                                            — <?php echo Text::_('COM_CSQUIRKYDBFIXES_WORKFLOW_NOT_ENABLED'); ?>
                                        </div>
                                        <form action="<?php echo Route::_('index.php?option=com_csquirkydbfixes'); ?>" method="post">
                                            <input type="hidden" name="task" value="fixes.enableworkflows">
                                            <?php echo HTMLHelper::_('form.token'); ?>
                                            <button type="submit" class="btn btn-warning">
                                                <span class="icon-play" aria-hidden="true"></span>
                                                <?php echo Text::_('COM_CSQUIRKYDBFIXES_WORKFLOW_NOT_ENABLED_FIX_BUTTON'); ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $disabledPlugins = $diagnostic['disabled_plugins'] ?? [];
                                if (!empty($disabledPlugins)) :
                                    $pluginNames = array_map(function ($p) { return Text::_($p['name']); }, $disabledPlugins);
                                ?>
                                    <div class="alert alert-danger">
                                        <div class="mb-2">
                                            <span class="icon-notification-circle" aria-hidden="true"></span>
                                            <strong><?php echo Text::_('COM_CSQUIRKYDBFIXES_WORKFLOW_PLUGINS_DISABLED_HEADING'); ?></strong>
                                            — <?php echo Text::sprintf('COM_CSQUIRKYDBFIXES_WORKFLOW_PLUGINS_DISABLED', implode(', ', $pluginNames)); ?>
                                        </div>
                                        <form action="<?php echo Route::_('index.php?option=com_csquirkydbfixes'); ?>" method="post">
                                            <input type="hidden" name="task" value="fixes.enableworkflowplugins">
                                            <?php echo HTMLHelper::_('form.token'); ?>
                                            <button type="submit" class="btn btn-warning">
                                                <span class="icon-play" aria-hidden="true"></span>
                                                <?php echo Text::_('COM_CSQUIRKYDBFIXES_WORKFLOW_PLUGINS_ENABLE_BUTTON'); ?>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- SQL Code -->
                            <?php if (!empty($fix['sql_code'])) : ?>
                                <div class="mb-3">
                                    <h5><?php echo Text::_('COM_CSQUIRKYDBFIXES_SQL_FIX'); ?></h5>
                                    <pre class="bg-dark text-light p-3 rounded" style="overflow-x: auto;"><code><?php echo $this->escape($fix['sql_code']); ?></code></pre>
                                </div>
                            <?php endif; ?>

                            <!-- Reference & Author -->
                            <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <?php if (!empty($fix['author'])) : ?>
                                    <small class="text-muted">
                                        <?php if (!empty($fix['author_url'])) : ?>
                                            <?php echo Text::sprintf('COM_CSQUIRKYDBFIXES_FIX_BY', '<a href="' . $this->escape($fix['author_url']) . '" target="_blank" rel="noopener noreferrer">' . $this->escape($fix['author']) . '</a>'); ?>
                                        <?php else : ?>
                                            <?php echo Text::sprintf('COM_CSQUIRKYDBFIXES_FIX_BY', $this->escape($fix['author'])); ?>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                                <?php if (!empty($fix['reference'])) : ?>
                                    <a href="<?php echo $this->escape($fix['reference']); ?>"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn btn-secondary btn-sm">
                                        <span class="icon-external-link" aria-hidden="true"></span>
                                        <?php echo Text::_('COM_CSQUIRKYDBFIXES_MORE_INFO'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <hr>
                            <div class="mb-3">
                                <!-- Download Backup Button -->
                                <form action="<?php echo Route::_('index.php?option=com_csquirkydbfixes'); ?>" method="post" class="d-inline">
                                    <input type="hidden" name="task" value="fixes.backup">
                                    <input type="hidden" name="fix_id" value="<?php echo $this->escape($fixId); ?>">
                                    <?php echo HTMLHelper::_('form.token'); ?>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="icon-download" aria-hidden="true"></span>
                                        <?php echo Text::_('COM_CSQUIRKYDBFIXES_DOWNLOAD_BACKUP'); ?>
                                    </button>
                                </form>
                            </div>

                            <!-- Backup Confirmation Checkbox -->
                            <div class="form-check mb-3">
                                <input class="form-check-input backup-confirm-checkbox"
                                       type="checkbox"
                                       id="confirm-backup-<?php echo $fixId; ?>"
                                       data-fix-id="<?php echo $this->escape($fixId); ?>">
                                <label class="form-check-label" for="confirm-backup-<?php echo $fixId; ?>">
                                    <?php echo Text::_('COM_CSQUIRKYDBFIXES_CONFIRM_BACKUP'); ?>
                                </label>
                            </div>

                            <!-- Run Fix Button -->
                            <div class="d-flex align-items-center gap-2">
                                <form action="<?php echo Route::_('index.php?option=com_csquirkydbfixes'); ?>" method="post" class="d-inline">
                                    <input type="hidden" name="task" value="fixes.runfix">
                                    <input type="hidden" name="fix_id" value="<?php echo $this->escape($fixId); ?>">
                                    <?php echo HTMLHelper::_('form.token'); ?>
                                    <button type="submit"
                                            class="btn <?php echo $hasIssues ? 'btn-warning' : 'btn-success'; ?> run-fix-btn"
                                            id="run-fix-<?php echo $fixId; ?>"
                                            disabled>
                                        <span class="icon-play" aria-hidden="true"></span>
                                        <?php echo Text::_('COM_CSQUIRKYDBFIXES_RUN_FIX'); ?>
                                    </button>
                                </form>
                                <?php if (!$hasIssues) : ?>
                                    <span class="text-muted">
                                        <?php echo Text::_('COM_CSQUIRKYDBFIXES_NO_ACTION_REQUIRED'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $index++; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle backup confirmation checkboxes
    document.querySelectorAll('.backup-confirm-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var fixId = this.dataset.fixId;
            var runBtn = document.getElementById('run-fix-' + fixId);
            if (runBtn) {
                runBtn.disabled = !this.checked;
            }
        });
    });
});
</script>
