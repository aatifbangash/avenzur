<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('All Entries') ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url(); ?>entries/add/journal">Add Journal</a></li>
                         <?php 
                    /*foreach($this->db->get('sma_accounts_entrytypes')->result_array() as $entrytype): ?>
                        <li><a href="<?= admin_url(); ?>entries/add/<?=$entrytype['label']?>"><?= $entrytype['name']; ?></a></li>
                    <?php endforeach; */?>
                     
                    </ul>
                </li>
               
            </ul>
        </div>
    </div>

    <div class="box-content">


       

        
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'method' => 'get'];
            echo admin_form_open('entries', $attrib);
            ?>

            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                    <label class="control-label" for="eid"><?= lang('ID'); ?></label>
                    <?php echo form_input('eid', (isset($_GET['eid']) ? $_GET['eid'] : ''), 'class="form-control" id="eid"'); ?>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                    <label class="control-label" for="trans_number"><?= lang('entries_views_index_th_number'); ?></label>
                    <?php echo form_input('tran_number', (isset($_GET['tran_number']) ? $_GET['tran_number'] : ''), 'class="form-control" id="tran_number"'); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="control-label" for="start_date"><?= lang('From Date'); ?></label>
                        <?php echo form_input('start_date', (isset($_GET['start_date']) ? $_GET['start_date'] : ''), 'class="form-control date" id="start_date"'); ?>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="form-group">
                    <label class="control-label" for="end_date"><?= lang('To Date'); ?></label>
                        <?php echo form_input('end_date', (isset($_GET['end_date']) ? $_GET['end_date'] : ''), 'class="form-control date" id="end_date"'); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <button class="btn btn-primary" name="submit" type="submit">Submit</button>
                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>
       
        

     	<div class="row">
            <div class="col-xs-12">
          <div class="box">
           
            <!-- /.box-header -->
            <div class="box-body">
              <table class="table table-bordered table-condensed table-striped">
                <thead>
                    <tr>
                        <th><?= lang('Id'); ?></th>
                        <th><?= lang('entries_views_index_th_date'); ?></th>
                        <th><?= lang('entries_views_index_th_number'); ?></th>
                        <th><?= lang('entries_views_index_th_ledger'); ?></th>
                        <th><?= lang('entries_views_index_th_type'); ?></th>
                        <th><?= lang('entries_views_index_th_tag'); ?></th>
                        <th><?= lang('entries_views_index_th_debit_amount'); ?></th>
                        <th><?= lang('entries_views_index_th_credit_amount'); ?></th>
                        <th><?= lang('entries_views_index_th_actions'); ?></th>
                    </tr>
                </thead>
                <tbody>

                <?php
                foreach ($entries as $entry) {
                    $this->db->where('id', $entry['entrytype_id']);
                    $q = $this->db->get('sma_accounts_entrytypes')->row();
                    $entryTypeName = $q->name;
                    $entryTypeLabel = $q->label;
                    ?>
                        <tr>
                            <td><?= $entry['id'] ?></td>
                            <td><?=  $this->functionscore->dateFromSql($entry['date']) ?></td>
                            <td><?= $entry['number'] ?></td>
                            <td><?= ($this->functionscore->entryLedgers($entry['id'])) ?></td>
                            <td><?= ($entryTypeName) ?></td>
                            <td><?= $this->functionscore->showTag($entry['tag_id']) ?></td>
                            <td><?= $this->functionscore->toCurrency('D', $entry['dr_total']) ?></td>
                            <td><?= $this->functionscore->toCurrency('C', $entry['cr_total']) ?></td>
                            <td>
                                <a href="<?= admin_url();?>entries/view/<?= ($entryTypeLabel); ?>/<?= $entry['id']; ?>" class="no-hover" escape="false"><i class="fa fa-log-in"></i><?= lang('entries_views_index_th_actions_view_btn'); ?></a>
                                <span class="link-pad"></span>
                                <a href="<?= admin_url();?>entries/edit/<?= ($entryTypeLabel); ?>/<?= $entry['id']; ?>" class="no-hover" escape="false"><i class="fa fa-edit"></i><?= lang('entries_views_index_th_actions_edit_btn'); ?></a>
                                <span class="link-pad"></span>
                                <a href="<?= admin_url();?>entries/delete/<?= ($entryTypeLabel); ?>/<?= $entry['id']; ?>" class="no-hover" escape="false"><i class="fa fa-trash"></i><?= lang('entries_views_index_th_actions_delete_btn'); ?></a>
                                
                            </td>
                        </tr>
                <?php } ?>
                    </tbody>
                </table>
            </div>
          </div>
      </div>
     		
     	</div>
     </div>
</div>