
<script type="text/javascript">
$(document).ready(function() {
	/**
	 * On changing the parent group select box check whether the selected value
	 * should show the "Affects Gross Profit/Loss Calculations".
	 */
	$('#GroupParentId').change(function() {
		if ($(this).val() == '3' || $(this).val() == '4') {
			$('#AffectsGross').show();
		} else {
			$('#AffectsGross').hide();
		}

		var id = $(this).val();
		$.ajax({
	    	type:"POST",
	        url: "<?=admin_url(); ?>" + "groups/getNextCode",
	    	data: { id,"<?= $this->security->get_csrf_token_name() ?>":"<?= $this->security->get_csrf_hash() ?>"  }
	    }).done(function(msg){
	    	console.log(msg);
	    	$('#g_code').val(msg);
	    });
	});
	$('#GroupParentId').trigger('change');
	$("#GroupParentId").select2({width:'100%'});


});

function getNumber() {
	var id = $("#GroupParentId option:selected").val()
	$.ajax({
    	type:"POST",
        url: "<?=admin_url(); ?>" + "groups/getNextCode",
    	data: { id,"<?= $this->security->get_csrf_token_name() ?>":"<?= $this->security->get_csrf_hash() ?>"  }
    }).done(function(msg){
    	console.log(msg);
    	$('#g_code').val(msg);
    });

}
</script>

<style type="text/css">
.select2-container--default .select2-results__option {
	font-weight: bold;
	color: #333;
}
</style>

<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('Add Group') ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
     <div class="box-content">
     	<div class="row">
     		 <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= lang('entries_views_add_title') ?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            	<?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>
             	<div class="groups add form">
					<?php
						echo form_open();

						echo "<div class='row'>";
						echo "<div class='col-xs-5'>";
							echo '<div class="form-group">';
							echo form_label(lang('groups_views_add_label_parent_group'), 'parent_id');
							echo form_dropdown('parent_id', $parents, set_value('parent_id'),array('class' => 'form-control', 'id'=>'GroupParentId',  'onchange'=>"getNumber()"));
							echo "</div>";
						echo "</div>";						
						echo "<div class='col-xs-2'>";
							echo '<div class="form-group">';
							echo form_label(lang('groups_views_add_label_group_code'), 'code');
							echo form_input('code', set_value('code') ,array('class' => 'form-control', 'id'=> 'g_code'));
							echo "</div>";	
						echo "</div>";
						echo "<div class='col-xs-5'>";
							echo '<div class="form-group">';
							echo form_label(lang('groups_views_add_label_group_name'), 'name');
							echo form_input('name', set_value('name') ,array('class' => 'form-control'));
							echo "</div>";		
						echo "</div>";
						echo "</div>";

                    echo "<div class='row'>";
                    echo "<div class='col-xs-4'>";
                    echo '<div class="form-group">';
                    echo form_label(lang('Type1'), 'type1');
                    // echo form_input('type1', set_value('type1') ,array('class' => 'form-control', 'id'=> 'g_type1'));
					echo form_dropdown('type1', $accountTypeOne, set_value('type1'),array('class' => 'form-control', 'id'=>'g_type1'));
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='col-xs-4'>";
                    echo '<div class="form-group">';
                    echo form_label(lang('Type2'), 'type2');
                    // echo form_input('type2', set_value('type2') ,array('class' => 'form-control', 'id'=> 'g_type2'));
					echo form_dropdown('type2', $accountTypeTwo, set_value('type2'),array('class' => 'form-control', 'id'=>'g_type2'));
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='col-xs-4'>";
                    echo '<div class="form-group">';
                    echo form_label(lang('Category'), 'category');
                    // echo form_input('category', set_value('category') ,array('class' => 'form-control'));
					echo form_dropdown('category', $accountCategories, set_value('category'),array('class' => 'form-control', 'id'=>'category'));
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";

						
						echo '<div class="form-group required" id="AffectsGross">';
						echo form_label(lang('groups_views_add_label_affects'), 'affects_gross');
						$data = array(
						        'name'          => 'affects_gross',
						        'id'            => 'affects_gross',
						        'value'         => '1',
						        'checked'       => TRUE,
						        'style'         => 'margin:10px'
						);
						echo "<br>";
						echo form_radio($data).lang('groups_views_add_label_gross_profit_loss');

						$data = array(
						        'name'          => 'affects_gross',
						        'id'            => 'affects_gross',
						        'value'         => '0',
						        'style'         => 'margin:10px'
						);
						echo "<br>";

						echo form_radio($data).lang('groups_views_add_label_net_profit_loss');

						echo '<span class="help-block">' . (lang('groups_views_add_note')) . '</span>';
						echo '</div>';

					
						echo '<div class="form-group">';
						echo form_submit('submit', lang('entries_views_add_label_submit_btn'), array('class' => 'btn btn-primary pull-right'));
						echo '<span class="link-pad"></span>';
						echo anchor('admin/accounts/index', lang('entries_views_add_label_cancel_btn'), array('class' => 'btn btn-default pull-right', 'style' => "margin-right: 5px;"));
						echo '</div>';

						echo form_close();
					?>
				</div>
            </div>
          </div>
      </div>
     	</div>
     </div>
</div>