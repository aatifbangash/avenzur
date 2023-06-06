
<?php

  function print_account_chart($account, $c = 0, $THIS) {
    $CI =& get_instance();

    $counter = $c;
    /* Print groups */
    if ($account->id != 0) {
      if ($account->id <= 4) {
        echo '<tr class="tr-group tr-root-group">';
      } else {
        echo '<tr class="tr-group">';
      }
      echo '<td>';
      echo print_space($counter);
      echo $account->code;
      echo '</td>';
      echo '<td class="td-group">';
      echo print_space($counter);
      echo $account->name;
      echo '</td>';

      echo '<td>'.lang('accounts_index_td_label_group').'</td>';

      echo '<td style="text-align:center;">-</td>';
      echo '<td style="text-align:center;">-</td>';

      /* If group id less than 4 dont show edit and delete links */
      if ($account->id <= 4) {
        echo '<td class="td-actions"></td>';
      } else {
        echo '<td class="td-actions">';
        echo anchor('admin/groups/edit/'.$account->id, '<i class="fa fa-edit"></i>'.lang('accounts_index_edit_btn'), array('class' => 'no-hover font-normal', 'escape' => false));
        echo "<span class='link-pad'></span>";

        echo anchor('admin/groups/delete/'.$account->id, '<i class="fa fa-trash-o"></i>'.lang('accounts_index_delete_btn'), 
            array('class' => 'no-hover font-normal',
                  'escape' => false,
                  'confirm' => lang('accounts_index_delete_group_alert'))
        );

        echo '</td>';
      }
      echo '</tr>';
    }

    /* Print child ledgers */
    if (count($account->children_ledgers) >= 1) {
      $counter++;
      foreach ($account->children_ledgers as $id => $data) {
        echo '<tr class="tr-ledger">';
        echo '<td class="td-ledger">';
        echo print_space($counter);
        echo anchor('admin/areports/ledgerstatement/ledgerid/'.$data['id'], $data['code']);
        echo '</td>';
        echo '<td class="td-ledger">';
        echo print_space($counter);
        //to change later
        echo anchor('admin/areports/ledgerstatement/ledgerid/'.$data['id'], $data['name']); 
        echo '</td>';
        echo '<td>'.lang('accounts_index_td_label_ledger').'</td>';

        echo '<td style="text-align:right">';
        echo $CI->functionscore->toCurrency($data['op_total_dc'], $data['op_total']);
        echo '</td>';

        echo '<td style="text-align:right">';
        echo $CI->functionscore->toCurrency($data['cl_total_dc'], $data['cl_total']);
        echo '</td>';

        echo '<td class="td-actions">';
        echo anchor('admin/ledgers/edit/'.$data['id'], '<i class="fa fa-edit"></i>'.lang('accounts_index_edit_btn'), 
            array('class' => 'no-hover', 'escape' => false)
        );
        echo "<span class='link-pad'></span>";
        echo anchor('admin/ledgers/delete/'.$data['id'], '<i class="fa fa-trash-o"></i>'.lang('accounts_index_delete_btn'), 
            array('class' => 'no-hover', 'escape' => false, 'confirm' => (lang('accounts_index_delete_ledger_alert')))
        );
        echo '</tr>';
      }
      $counter--;
    }
    
    /* Print child groups recursively */
    foreach ($account->children_groups as $id => $data) {
      $counter++;
      print_account_chart($data, $counter, $THIS);
      $counter--;
    }
  }

  function print_space($count) {
    $html = '';
    for ($i = 1; $i <= $count; $i++) {
      $html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    return $html;
  }

?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('Chart of Accounts') ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('groups/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('accounts_index_add_group_btn') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('ledgers/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('accounts_index_add_ledger_btn') ?>
                            </a>
                        </li>
                     
                    </ul>
                </li>
               
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
           <div class="col-lg-12"> 
            <?php if ($this->functionscore->calculate($opdiff['opdiff_balance'], 0, '!=')) {
        echo '<div><div role="alert" class="alert alert-danger">' .
          sprintf(lang('accounts_index_label_difference_bw_balance'), $this->functionscore->toCurrency($opdiff['opdiff_balance_dc'], $opdiff['opdiff_balance'])) .
          
          '</div></div>';
      }; ?>
  </div>
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                   <?php
                echo '<table id="ledgertable" class="table table-bordered table-condensed table-striped">';
                echo '<thead>';
                echo '<th>' . (lang('accounts_index_account_code')) . '</th>';
                echo '<th>' . (lang('accounts_index_account_name')) . '</th>';
                echo '<th>' . (lang('accounts_index_type')) . '</th>';
                echo '<th>' . (lang('accounts_index_op_balance')) . ' (' .$this->mAccountSettings->currency_symbol. ')' . '</th>';
                echo '<th>' . (lang('accounts_index_cl_balance')) . ' (' .$this->mAccountSettings->currency_symbol. ')' . '</th>';
                echo '<th>' . (lang('accounts_index_action')) . '</th>';
                echo '</thead>';
                echo "<tbody>";
                print_account_chart($accountlist, -1, $this);
                echo "</tbody>";
                echo '</table>';
              ?>
                </div>
            </div>
        </div>
    </div>
</div>
