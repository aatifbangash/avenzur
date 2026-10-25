<div class="box">
  <div class="box-header">
    <h2 class="blue"><i class="fa fa-link"></i> Map Excel Columns to Database Fields</h2>
  </div>
  <div class="box-content">
    <?= admin_form_open_multipart('suppliers/process_import'); ?>
      <input type="hidden" name="file_path" value="<?= $file_path ?>">

      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Excel Column</th>
            <th>Sample Data</th>
            <th>Database Field</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($headers as $index => $header): ?>
            <tr>
              <td><?= html_escape($header) ?></td>
              <td><?= isset($sample_rows[1][$index]) ? html_escape($sample_rows[1][$index]) : '' ?></td>
              <td>
                <select name="mapping[<?= $index ?>]" class="form-control">
                  <option value="">-- Ignore this column --</option>
                  <?php foreach ($db_fields as $db_field => $label): ?>
                    <option value="<?= $db_field ?>"><?= $label ?> (<?= $db_field ?>)</option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php echo form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
    <?= form_close(); ?>
  </div>
</div>
