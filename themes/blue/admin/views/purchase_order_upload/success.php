<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-check"></i><?php echo $page_title; ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="alert alert-success">
                    <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php } ?>
                <?php if ($this->session->flashdata('message')) { ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?php echo $this->session->flashdata('message'); ?>
                    </div>
                <?php } ?>
                </div>

                <div class="well">
                    <h3><?php echo $this->lang->line('purchase_order_details'); ?></h3>
                    <p><strong><?php echo $this->lang->line('purchase_id'); ?>:</strong> <?php echo $purchase_id; ?></p>
                </div>

                <div class="form-group">
                    <a href="<?php echo admin_url('purchase_order'); ?>" class="btn btn-primary"><?php echo $this->lang->line('view_purchase_order'); ?></a>
                    <a href="<?php echo admin_url('purchase_order_upload'); ?>" class="btn btn-default"><?php echo $this->lang->line('upload_another'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>