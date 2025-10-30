<style>
    /* =====================
   CLEAN NEW MENU STYLE
   ===================== */

    #sidebar-left{
        padding: 0px !important
    }
    .newmenu-wrapper {
        width: 250px !important; /* increase width as needed */
        margin: 0 !important;    /* remove left/right margin */
        background: #1f2937 !important;
        color: #f3f4f6 !important;
        font-family: "Segoe UI", Roboto, sans-serif !important;
        border-radius: 6px;
        padding: 10px 0;
    }


    .newmenu-nav {
        list-style: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .newmenu-item {
        position: relative;
    }

    .newmenu-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #f3f4f6 !important;
        text-decoration: none !important;
        padding: 10px 16px;
        transition: background 0.2s ease;
    }

    .newmenu-link:hover {
        background: #374151 !important; /* Hover grey */
        color: #fff !important;
    }

    .newmenu-link i {
        margin-right: 8px;
    }

    .newmenu-sub {
        list-style: none !important;
        padding-left: 15px !important;
        display: none;
        background: #111827 !important;
    }

    .newmenu-item.open > .newmenu-sub {
        display: block !important;
    }

    .newmenu-chevron {
        margin-left: auto;
        transition: transform 0.3s ease;
    }

    .newmenu-item.open > .newmenu-link .newmenu-chevron {
        transform: rotate(90deg);
    }

    .newmenu-sub .newmenu-link {
        font-size: 14px;
        padding: 8px 20px;
        color: #d1d5db !important;
    }

    .newmenu-sub .newmenu-link:hover {
        background: #374151 !important;
        color: #fff !important;
    }

    .newmenu-sub .newmenu-link {
        font-size: 14px !important;
        padding: 8px 25px !important; /* reduced indent */
    }

    .newmenu-sub .newmenu-sub .newmenu-link {
        padding-left: 40px !important; /* consistent nested indentation */
    }

    .newmenu-link i.fa {
        width: 18px;
        text-align: center;
    }

    /* Make top-level text always left-aligned */
    .newmenu-link span {
        text-align: left !important;
        flex: 1; /* ensures span takes full space next to icon */
    }

    /* Keep top-level li items open by default */
    .newmenu-wrapper > .newmenu-nav > .newmenu-item.has-sub {
        display: block !important;
    }

    .newmenu-wrapper > .newmenu-nav > .newmenu-item.has-sub > .newmenu-sub {
        display: block !important;
    }

    .newmenu-wrapper > .newmenu-nav > .newmenu-item.has-sub > .newmenu-link .newmenu-chevron {
        transform: rotate(90deg); /* keep chevron pointing right for open state */
    }

    .bluecolor {
        background-color: #428bca !important;
        color: white !important;
        border-color: #357ebd !important;
        border-top: 1px solid #357ebd !important;
    }

</style>

<div class="newmenu-wrapper">
    <ul class="newmenu-nav">

        <?php 
        
        if($Admin || $Owner || $this->GP['sales-coordinator'] || $this->GP['accountant'] || $this->GP['sales-warehouse_supervisor']){
        ?>
        <!-- ==================== -->
        <!-- ACCOUNT RECEIVABLE -->
        <!-- ==================== -->
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-hand-o-up"></i>
                <span><?= lang('Account Receivable'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">

                <!-- Sale -->
                <li class="newmenu-item has-sub">
                    <a href="<?= admin_url('Sale'); ?>" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Sale'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php 
                        if($Admin || $Owner || $this->GP['sales-coordinator'] || $this->GP['accountant']){ ?>
                        <li><a href="<?= admin_url('quotes/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Quote'); ?></a></li>
                        <li><a href="<?= admin_url('quotes'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Quotes List'); ?></a></li>
                        <?php } ?>
                        <li><a href="<?= admin_url('sales'); ?>" class="newmenu-link"><i class="fa fa-file"></i> <?= lang('Sale Orders'); ?></a></li>
                    </ul>
                </li>

                <!-- Delivery -->
                <!--<li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-truck"></i>
                        <span><?= lang('Delivery'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('deliveries/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Delivery'); ?></a></li>
                        <li><a href="<?= admin_url('deliveries'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List Deliveries'); ?></a></li>
                    </ul>
                </li>-->

                <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
                <!-- Collection -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-money"></i>
                        <span><?= lang('Collection'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('customers/payment_from_customer'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('Collect Payment'); ?></a></li>
                        <li><a href="<?= admin_url('customers/list_payments'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Payment List'); ?></a></li>
                    </ul>
                </li>
                <?php } ?>

                <!-- Returns -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-undo"></i>
                        <span><?= lang('Returns'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('returns/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Mark Returns'); ?></a></li>
                        <li><a href="<?= admin_url('returns'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List Returns'); ?></a></li>
                    </ul>
                </li>

                <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
                <!-- Reports -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-bar-chart"></i>
                        <span><?= lang('Reports'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('reports/customers_trial_balance'); ?>" class="newmenu-link"><i class="fa fa-balance-scale"></i> <?= lang('Trial Balance'); ?></a></li>
                        <li><a href="<?= admin_url('reports/customer_statement'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Statement'); ?></a></li>
                    </ul>
                </li>

                <?php } ?>

                <li><a href="<?= admin_url('customers'); ?>" class="newmenu-link"><i class="fa fa-users"></i> <?= lang('Customers'); ?></a></li>

            </ul>
        </li>

        <?php } ?>

        <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
        <!-- ==================== -->
        <!-- ACCOUNT PAYABLE -->
        <!-- ==================== -->
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-hand-o-down"></i>
                <span><?= lang('Account Payable'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">

                <!-- Purchase Contract Deals -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Purchase Contract Deals'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('purchase_contract_deals/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Contract Deals'); ?></a></li>
                        <li><a href="<?= admin_url('purchase_contract_deals'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List Contract Deals'); ?></a></li>
                    </ul>
                </li>

                <!-- Purchase Requisition -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Purchase Requisition'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('purchase_requisition/save'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Create PR'); ?></a></li>
                        <li><a href="<?= admin_url('purchase_requisition'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List PR'); ?></a></li>
                    </ul>
                </li>

                <!-- Purchase Order -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Purchase Orders'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('purchase_order/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Create PO'); ?></a></li>
                        <li><a href="<?= admin_url('purchase_order'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List PO'); ?></a></li>
                    </ul>
                </li>

                <!-- Purchases -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Purchase Invoices'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('purchases/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Request Purchase'); ?></a></li>
                        <li><a href="<?= admin_url('purchases'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Purchase List'); ?></a></li>
                    </ul>
                </li>

                <!-- Returns -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-undo"></i>
                        <span><?= lang('Returns'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('returns_supplier'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Supplier Returns List'); ?></a></li>
                        <li><a href="<?= admin_url('returns_supplier/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Supplier Return'); ?></a></li>
                    </ul>
                </li>


                <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
                <!-- Payments -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-credit-card"></i>
                        <span><?= lang('Payments'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('suppliers/add_payment'); ?>" class="newmenu-link"><i class="fa fa-hand-holding-usd"></i> <?= lang('Pay Supplier'); ?></a></li>
                        <li><a href="<?= admin_url('suppliers/list_payments'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Supplier Payments'); ?></a></li>
                    </ul>
                </li>
                

                <!-- Reports -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-bar-chart"></i>
                        <span><?= lang('Reports'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('reports/suppliers_trial_balance'); ?>" class="newmenu-link"><i class="fa fa-balance-scale"></i> <?= lang('Trial Balance'); ?></a></li>
                        <li><a href="<?= admin_url('reports/supplier_statement'); ?>" class="newmenu-link"><i class="fa fa-balance-scale"></i> <?= lang('Statement'); ?></a></li>
                        <!--<li><a href="<?= admin_url('reports/purchase_item'); ?>" class="newmenu-link"><i class="fa fa-boxes"></i> <?= lang('Purchase Per Item'); ?></a></li>
                        <li><a href="<?= admin_url('reports/purchase_deals'); ?>" class="newmenu-link"><i class="fa fa-gift"></i> <?= lang('Purchase Deals & Discounts'); ?></a></li>
                        <li><a href="<?= admin_url('reports/purchase_status'); ?>" class="newmenu-link"><i class="fa fa-clipboard-check"></i> <?= lang('Purchase Status'); ?></a></li>-->
                    </ul>
                </li>

                <?php } ?>

                <li><a href="<?= admin_url('suppliers'); ?>" class="newmenu-link"><i class="fa fa-users"></i> <?= lang('suppliers'); ?></a></li>

            </ul>
        </li>

        <?php } ?>

        <!-- ==================== -->
        <!-- INVENTORY -->
        <!-- ==================== -->
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-archive"></i>
                <span><?= lang('Inventory'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link"><i class="fa fa-exchange"></i> <?= lang('Transactions'); ?> <i class="fa fa-chevron-right newmenu-chevron"></i></a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('transfers/add'); ?>" class="newmenu-link"><i class="fa fa-random"></i> <?= lang('Transfer Inventory'); ?></a></li>
                        <li><a href="<?= admin_url('transfers'); ?>" class="newmenu-link"><i class="fa fa-cart-plus"></i> <?= lang('Transfer List'); ?></a></li>
                        <!--<li><a href="<?= admin_url('inventory/returns'); ?>" class="newmenu-link"><i class="fa fa-undo"></i> <?= lang('Returns'); ?></a></li>-->
                    </ul>
                </li>

                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link"><i class="fa fa-bar-chart"></i> <?= lang('Reports'); ?> <i class="fa fa-chevron-right newmenu-chevron"></i></a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('reports/item_movement_report'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Inventory Reports'); ?></a></li>
                        <!--<li><a href="<?= admin_url('reports/aging'); ?>" class="newmenu-link"><i class="fa fa-hourglass-half"></i> <?= lang('Aging'); ?></a></li>-->
                        <li><a href="<?= admin_url('reports/stock'); ?>" class="newmenu-link"><i class="fa fa-calendar-times-o"></i> <?= lang('Stock'); ?></a></li>
                        <li><a href="<?= admin_url('reports/inventory_trial_balance'); ?>" class="newmenu-link"><i class="fa fa-arrows-alt"></i> <?= lang('Trial Balance'); ?></a></li>
                    </ul>
                </li>

                <!--<li><a href="<?= admin_url('reports/cost_analysis'); ?>" class="newmenu-link"><i class="fa fa-balance-scale"></i> <?= lang('Cost Analysis'); ?></a></li>-->
            </ul>
        </li>

        <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
        <!-- FINANCE -->
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-money"></i>
                <span><?= lang('Finance'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">
                <li><a href="<?= admin_url('accounts_dashboard'); ?>" class="newmenu-link"><i class="fa fa-sliders"></i> <?= lang('Accounts Dashboard'); ?></a></li>
                <li><a href="<?= admin_url('accounts'); ?>" class="newmenu-link"><i class="fa fa-calculator"></i> <?= lang('Charts Of Accounts'); ?></a></li>
                <li><a href="<?= admin_url('entries'); ?>" class="newmenu-link"><i class="fa fa-pencil-square-o"></i> <?= lang('GL Entry'); ?></a></li>
                
            </ul>
        </li>
        <?php } ?>

        <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
        <!-- SETTINGS -->
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-cogs"></i>
                <span><?= lang('Settings'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">
                <?php if($Admin || $Owner){ ?>
                <li><a href="<?= admin_url('users'); ?>" class="newmenu-link"><i class="fa fa-users"></i> <?= lang('People'); ?></a></li>
                <?php } ?>
                <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
                <li><a href="<?= admin_url('system_settings/add_ledgers'); ?>" class="newmenu-link"><i class="fa fa-sliders"></i> <?= lang('Account Settings'); ?></a></li>
                
                <?php } ?>
            </ul>
        </li>
        <?php } ?>

        <?php 
        
        if($Admin || $Owner || $this->GP['sales-warehouse_supervisor']){
        ?>
        <!-- Services -->
         <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-cogs"></i>
                <span><?= lang('Services'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">
                <li><a href="<?= admin_url('notifications/rasd'); ?>" class="newmenu-link"><i class="fa fa-users"></i> <?= lang('Rasd Notifications'); ?></a></li>
            </ul>
        <?php } ?>
    </ul>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.newmenu-item.has-sub > .newmenu-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('open');
        });
    });
});
</script>

