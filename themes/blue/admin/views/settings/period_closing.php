<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-lock"></i>Period Closing</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext">
                    Close a calendar month per module. While closed, add / edit / delete in that module for dates in the month are blocked where enforcement is enabled.
                    <strong>Enforced now:</strong> Finance (JL), Inventory (transfers), AR (quotes, sale delete/label/RASD, delivery add/edit/mark delivered).
                </p>

                <form method="get" action="<?= admin_url('period_closing') ?>" class="form-inline" style="margin-bottom:15px;">
                    <div class="form-group">
                        <label for="year" style="margin-right:8px;">Year</label>
                        <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                            <?php
                            $current = (int) date('Y');
                            for ($y = $current + 1; $y >= $current - 5; $y--):
                            ?>
                                <option value="<?= $y ?>" <?= ((int) $year === $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <th style="width:120px;">Month</th>
                                <?php foreach ($modules as $key => $label): ?>
                                    <th class="text-center"><?= htmlspecialchars($label) ?></th>
                                <?php endforeach; ?>
                                <th class="text-center" style="width:130px;">All modules</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($months as $m => $monthName): ?>
                                <tr>
                                    <td><strong><?= $monthName ?></strong></td>
                                    <?php
                                    $allClosed = true;
                                    foreach ($modules as $key => $label):
                                        $row = $closures[$key . '-' . $m] ?? null;
                                        $isClosed = $row && ($row->status === 'closed');
                                        if (!$isClosed) {
                                            $allClosed = false;
                                        }
                                    ?>
                                        <td class="text-center" style="vertical-align:middle;">
                                            <?php if ($isClosed): ?>
                                                <span class="label label-danger"><i class="fa fa-lock"></i> Closed</span>
                                                <?php if (!empty($row->closed_at)): ?>
                                                    <div style="font-size:11px;color:#777;margin-top:4px;">
                                                        <?= date('d-M-Y H:i', strtotime($row->closed_at)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <form method="post" action="<?= admin_url('period_closing/reopen') ?>" style="display:inline;margin-top:6px;">
                                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                                    <input type="hidden" name="module" value="<?= $key ?>">
                                                    <input type="hidden" name="period_year" value="<?= (int) $year ?>">
                                                    <input type="hidden" name="period_month" value="<?= (int) $m ?>">
                                                    <button type="submit" class="btn btn-xs btn-success"
                                                            onclick="return confirm('Reopen <?= htmlspecialchars($label) ?> for <?= $monthName ?> <?= (int) $year ?>?');">
                                                        <i class="fa fa-unlock"></i> Reopen
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="label label-info">Open</span>
                                                <form method="post" action="<?= admin_url('period_closing/close') ?>" style="display:inline;margin-top:6px;">
                                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                                    <input type="hidden" name="module" value="<?= $key ?>">
                                                    <input type="hidden" name="period_year" value="<?= (int) $year ?>">
                                                    <input type="hidden" name="period_month" value="<?= (int) $m ?>">
                                                    <button type="submit" class="btn btn-xs btn-danger"
                                                            onclick="return confirm('Close <?= htmlspecialchars($label) ?> for <?= $monthName ?> <?= (int) $year ?>?');">
                                                        <i class="fa fa-lock"></i> Close
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center" style="vertical-align:middle;">
                                        <?php if ($allClosed): ?>
                                            <span class="label label-danger">All closed</span>
                                        <?php else: ?>
                                            <form method="post" action="<?= admin_url('period_closing/close_all') ?>" style="display:inline;">
                                                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                                <input type="hidden" name="period_year" value="<?= (int) $year ?>">
                                                <input type="hidden" name="period_month" value="<?= (int) $m ?>">
                                                <button type="submit" class="btn btn-xs btn-warning"
                                                        onclick="return confirm('Close ALL modules for <?= $monthName ?> <?= (int) $year ?>?');">
                                                    <i class="fa fa-lock"></i> Close all
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
