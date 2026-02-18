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
        background: #134dabff !important; /* Hover grey */
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
    /*.newmenu-wrapper > .newmenu-nav > .newmenu-item.has-sub {
        display: block !important;
    }

    .newmenu-wrapper > .newmenu-nav > .newmenu-item.has-sub > .newmenu-sub {
        display: block !important;
    }

    .newmenu-wrapper > .newmenu-nav > .newmenu-item.has-sub > .newmenu-link .newmenu-chevron {
        transform: rotate(90deg); 
    }*/

    .bluecolor {
        background-color: #0b4476 !important;
        color: white !important;
        border-color: #357ebd !important;
        border-top: 1px solid #357ebd !important;
    }

</style>

<div class="newmenu-wrapper">
    <ul class="newmenu-nav">
        <?php if($Admin || $Owner || $GP['products-index']){ ?>
        <!-- ==================== -->
        <!-- ACCOUNT PAYABLE -->
        <!-- ==================== -->
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-archive"></i>
                <span><?= lang('Master Data'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">

                <!-- Products -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Products'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $GP['products-add']){
                            if($this->Settings->site_name == 'Avnzor'){ ?>
                                <li><a href="<?= admin_url('products/add_new'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Product'); ?></a></li>
                            <?php }else{ ?>
                                <li><a href="<?= admin_url('products/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Product'); ?></a></li>
                            <?php }
                         } ?>
<!--                        --><?php //if($Admin || $Owner || $GP['products-index']){ ?>
<!--                            <li><a href="--><?php //= admin_url('products'); ?><!--" class="newmenu-link"><i class="fa fa-list"></i> --><?php //= lang('List Products'); ?><!--</a></li>-->
<!--                        --><?php //} ?>

                        <?php if($Admin || $Owner || $GP['products-index']){
                            if($this->Settings->site_name == 'Avnzor'){ ?>
                                <li><a href="<?= admin_url('products/list_products'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('List Products'); ?></a></li>
                            <?php }else{ ?>
                                <li><a href="<?= admin_url('products'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('List Products'); ?></a></li>
                            <?php }
                        } ?>
                    </ul>
                </li>
            </ul>
        </li>

        <?php } ?>


        <?php 
        
        if($Admin || $Owner || $GP['sales-index'] || $GP['reports-customer-aging']){
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
                        if($Admin || $Owner || $GP['quotes-add']){ ?>
                        <li><a href="<?= admin_url('quotes/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Quote'); ?></a></li>
                        <?php } ?>
                        <?php 
                        if($Admin || $Owner || $GP['quotes-index']){ ?>
                        <li><a href="<?= admin_url('quotes'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Quotes List'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['sales-index']){ ?>
                        <li><a href="<?= admin_url('sales'); ?>" class="newmenu-link"><i class="fa fa-file"></i> <?= lang('Sale Orders'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['sales-index']){ ?>
                        <li><a href="<?= admin_url('sales/shop_sales'); ?>" class="newmenu-link"><i class="fa fa-shopping-cart"></i> <?= lang('Shop Sales'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>

                <?php if($Admin || $Owner || $this->GP['customer-payment-index']){ ?>
                <!-- Collection -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-money"></i>
                        <span><?= lang('Collection'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['customer-payment-add']){ ?>
                        <li><a href="<?= admin_url('customers/payment_from_customer_new'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('Collection New'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['customer-payment-index']){ ?>
                        <li><a href="<?= admin_url('customers/list_payments'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Payment List'); ?></a></li>
                        <?php } ?>
                        
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $this->GP['customer-payment-add']){ ?>
                <!-- Collection -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-money"></i>
                        <span><?= lang('Advances'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['customer-payment-add']){ ?>
                        <li><a href="<?= admin_url('customers/add_advance'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('Add Advance'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $this->GP['customer-payment-index']){ ?>
                <!-- Collection -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-money"></i>
                        <span><?= lang('Customer Memo'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                       
                        <?php if($Admin || $Owner || $this->GP['customer-payment-add']){ ?>
                        <li><a href="<?= admin_url('customers/credit_memo'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('Add Customer Memo'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['customer-payment-index']){ ?>
                        <li><a href="<?= admin_url('customers/list_credit_memo'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('List Customer Memos'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $this->GP['customer-payment-index']){ ?>
                <!-- Service Invoice -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-credit-card"></i>
                        <span><?= lang('Service Invoice'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        
                        <?php if($Admin || $Owner || $this->GP['customer-payment-add']){ ?>
                        <li><a href="<?= admin_url('customers/service_invoice'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('Add Service Invoice'); ?></a></li>
                        <?php } ?>

                        <?php if($Admin || $Owner || $this->GP['customer-payment-index']){ ?>
                        <li><a href="<?= admin_url('customers/list_service_invoice'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('List Service Invoices'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $this->GP['returns-index']){ ?>
                <!-- Returns -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-undo"></i>
                        <span><?= lang('Returns'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['returns-add']){ ?>
                        <li><a href="<?= admin_url('returns/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Mark Returns'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['returns-index']){ ?>
                        <li><a href="<?= admin_url('returns'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List Returns'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <!-- Reports -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-bar-chart"></i>
                        <span><?= lang('Reports'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['reports-customer-tb']){ ?>
                            <li><a href="<?= admin_url('reports/customers_trial_balance'); ?>" class="newmenu-link"><i class="fa fa-balance-scale"></i> <?= lang('Trial Balance'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['reports-customer-statement']){ ?>
                            <li><a href="<?= admin_url('reports/customer_statement'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Statement'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['reports-customer-aging']){ ?>
                            <li><a href="<?= admin_url('reports/customer_aging'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Aging'); ?></a></li>
                        <?php } ?>
                        
                        <li><a href="<?= admin_url('reports/collections_by_location'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Collection Report'); ?></a></li>
                        <li><a href="<?= admin_url('reports/invoice_status'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Invoice Status'); ?></a></li>
                        <li><a href="<?= admin_url('reports/sales_per_invoice'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Sales Per Invoice'); ?></a></li>
                        <li><a href="<?= admin_url('reports/sales_per_item'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Sales Per Item'); ?></a></li>
                        
                    </ul>
                </li>

                <?php if($Admin || $Owner || $this->GP['customers-index']){ ?>
                <li><a href="<?= admin_url('customers'); ?>" class="newmenu-link"><i class="fa fa-users"></i> <?= lang('Customers'); ?></a></li>
                <?php } ?>
            </ul>
        </li>

        <?php } ?>

        <?php if($Admin || $Owner || $GP['purchases-index'] || $GP['po-index'] || $GP['reports-supplier-aging']){ ?>
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

                <?php if($Admin || $Owner || $GP['contract-deals-index']){ ?>
                <!-- Purchase Contract Deals -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Purchase Contract Deals'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $GP['contract-deals-add']){ ?>
                            <li><a href="<?= admin_url('purchase_contract_deals/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Contract Deals'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $GP['contract-deals-index']){ ?>
                            <li><a href="<?= admin_url('purchase_contract_deals'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List Contract Deals'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $GP['pr-index']){ ?>
                <!-- Purchase Requisition -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Purchase Requisition'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $GP['pr-add']){ ?>
                            <li><a href="<?= admin_url('purchase_requisition/save'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Create PR'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $GP['pr-index']){ ?>
                            <li><a href="<?= admin_url('purchase_requisition'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List PR'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $GP['po-index']){ ?>
                <!-- Purchase Order -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Purchase Orders'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $GP['po-add']){ ?>
                            <li><a href="<?= admin_url('purchase_order/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Create PO'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $GP['po-index']){ ?>
                            <li><a href="<?= admin_url('purchase_order'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List PO'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <!-- Purchases -->
                 <?php if($Admin || $Owner || $GP['purchases-index']){ ?>
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-shopping-cart"></i>
                        <span><?= lang('Purchase Invoices'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $GP['purchases-add']){ ?>
                            <li><a href="<?= admin_url('purchases/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Request Purchase'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $GP['purchases-index']){ ?>
                            <li><a href="<?= admin_url('purchases'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Purchase List'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $GP['supplier-returns-index']){ ?>
                <!-- Returns -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-undo"></i>
                        <span><?= lang('Returns'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $GP['supplier-returns-index']){ ?>
                            <li><a href="<?= admin_url('returns_supplier'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Supplier Returns List'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $GP['supplier-returns-add']){ ?>
                            <li><a href="<?= admin_url('returns_supplier/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Supplier Return'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>


                <?php if($Admin || $Owner || $this->GP['supplier-payment-index']){ ?>
                <!-- Payments -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-credit-card"></i>
                        <span><?= lang('Payments'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['supplier-payment-add']){ ?>
                            <li><a href="<?= admin_url('suppliers/add_payment'); ?>" class="newmenu-link"><i class="fa fa-hand-holding-usd"></i> <?= lang('Pay Supplier'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['supplier-payment-index']){ ?>
                            <li><a href="<?= admin_url('suppliers/list_payments'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Supplier Payments'); ?></a></li>
                        <?php } ?>
                        
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $this->GP['supplier-payment-index']){ ?>
                <!-- Debit Memos -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-credit-card"></i>
                        <span><?= lang('Supplier Memo'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        
                        <?php if($Admin || $Owner || $this->GP['supplier-payment-add']){ ?>
                        <li><a href="<?= admin_url('suppliers/debit_memo'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('Add Supplier Memo'); ?></a></li>
                        <?php } ?>

                        <?php if($Admin || $Owner || $this->GP['supplier-payment-index']){ ?>
                        <li><a href="<?= admin_url('suppliers/list_debit_memo'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('List Supplier Memos'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $this->GP['supplier-payment-index']){ ?>
                <!-- Service Invoice -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-credit-card"></i>
                        <span><?= lang('Service Invoice'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        
                        <?php if($Admin || $Owner || $this->GP['supplier-payment-add']){ ?>
                        <li><a href="<?= admin_url('suppliers/service_invoice'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('Add Service Invoice'); ?></a></li>
                        <?php } ?>

                        <?php if($Admin || $Owner || $this->GP['supplier-payment-index']){ ?>
                        <li><a href="<?= admin_url('suppliers/list_service_invoice'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('List Service Invoices'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $this->GP['supplier-payment-index']){ ?>
                <!-- Service Invoice -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-credit-card"></i>
                        <span><?= lang('Petty Cash'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        
                        <?php if($Admin || $Owner || $this->GP['supplier-payment-add']){ ?>
                        <li><a href="<?= admin_url('suppliers/petty_cash'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('Add Petty Cash'); ?></a></li>
                        <?php } ?>

                        <?php if($Admin || $Owner || $this->GP['supplier-payment-index']){ ?>
                        <li><a href="<?= admin_url('suppliers/list_petty_cash'); ?>" class="newmenu-link"><i class="fa fa-hand-o-up"></i> <?= lang('List Petty Cash'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                
               
                <!-- Reports -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-bar-chart"></i>
                        <span><?= lang('Reports'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['reports-supplier-tb']){ ?>
                        <li><a href="<?= admin_url('reports/suppliers_trial_balance'); ?>" class="newmenu-link"><i class="fa fa-balance-scale"></i> <?= lang('Trial Balance'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['reports-supplier-statement']){ ?>
                        <li><a href="<?= admin_url('reports/supplier_statement'); ?>" class="newmenu-link"><i class="fa fa-balance-scale"></i> <?= lang('Statement'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['reports-supplier-aging']){ ?>
                        <li><a href="<?= admin_url('reports/supplier_aging'); ?>" class="newmenu-link"><i class="fa fa-boxes"></i> <?= lang('Aging'); ?></a></li>
                        <?php } ?>
                        <li><a href="<?= admin_url('reports/consumption_report'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Consumption Report'); ?></a></li>
                        <li><a href="<?= admin_url('reports/purchase_per_item'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Purchase Per Item'); ?></a></li>
                        <li><a href="<?= admin_url('reports/purchase_per_invoice'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Purchase Per Invoice'); ?></a></li>
                        <!--<li><a href="<?= admin_url('reports/purchase_deals'); ?>" class="newmenu-link"><i class="fa fa-gift"></i> <?= lang('Purchase Deals & Discounts'); ?></a></li>
                        <li><a href="<?= admin_url('reports/purchase_status'); ?>" class="newmenu-link"><i class="fa fa-clipboard-check"></i> <?= lang('Purchase Status'); ?></a></li>-->
                    </ul>
                </li>

                <?php if($Admin || $Owner || $this->GP['suppliers-index']){ ?>
                    <li><a href="<?= admin_url('suppliers'); ?>" class="newmenu-link"><i class="fa fa-users"></i> <?= lang('suppliers'); ?></a></li>
                <?php } ?>
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
                <?php if($Admin || $Owner || $this->GP['transfers-index']){ ?>
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link"><i class="fa fa-exchange"></i> <?= lang('Transactions'); ?> <i class="fa fa-chevron-right newmenu-chevron"></i></a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['transfers-add']){ ?>
                        <li><a href="<?= admin_url('transfers/add'); ?>" class="newmenu-link"><i class="fa fa-random"></i> <?= lang('Transfer Inventory'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['transfers-index']){ ?>
                        <li><a href="<?= admin_url('transfers'); ?>" class="newmenu-link"><i class="fa fa-cart-plus"></i> <?= lang('Transfer List'); ?></a></li>
                        <?php } ?>
                        <!--<li><a href="<?= admin_url('inventory/returns'); ?>" class="newmenu-link"><i class="fa fa-undo"></i> <?= lang('Returns'); ?></a></li>-->
                    </ul>
                </li>
                <?php } ?>
                

                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link"><i class="fa fa-bar-chart"></i> <?= lang('Reports'); ?> <i class="fa fa-chevron-right newmenu-chevron"></i></a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['reports-item-movement']){ ?>
                        <li><a href="<?= admin_url('reports/item_movement_report'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Inventory Reports'); ?></a></li>
                        <?php } ?>
                        <!--<li><a href="<?= admin_url('reports/aging'); ?>" class="newmenu-link"><i class="fa fa-hourglass-half"></i> <?= lang('Aging'); ?></a></li>-->
                        <?php if($Admin || $Owner || $this->GP['report-stock']){ ?>
                        <li><a href="<?= admin_url('reports/stock'); ?>" class="newmenu-link"><i class="fa fa-calendar-times-o"></i> <?= lang('Stock'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['reports-inventory-tb']){ ?>
                        <li><a href="<?= admin_url('reports/inventory_trial_balance'); ?>" class="newmenu-link"><i class="fa fa-arrows-alt"></i> <?= lang('Trial Balance'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['reports-revenue']){ ?>
                        <li><a href="<?= admin_url('reports/revenue_report'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Revenue Report'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['reports-purchase']){ ?>
                        <li><a href="<?= admin_url('reports/purchase_report'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Purchase Report'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['reports-transfer']){ ?>
                        <li><a href="<?= admin_url('reports/transfer_report'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i> <?= lang('Transfer Report'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>

                <!--<li><a href="<?= admin_url('reports/cost_analysis'); ?>" class="newmenu-link"><i class="fa fa-balance-scale"></i> <?= lang('Cost Analysis'); ?></a></li>-->
            </ul>
        </li>

        <?php if($Admin || $Owner || $this->GP['customer-payment-index'] || $this->GP['supplier-payment-index']){ ?>
        <!-- FINANCE -->
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-money"></i>
                <span><?= lang('Finance'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">
               
                <li><a href="<?= admin_url('cost_center/dashboard'); ?>" class="newmenu-link"><i class="fa fa-dashboard"></i><?= lang('Cost Center'); ?></a></li>

                <li><a href="<?= admin_url('accounts_dashboard'); ?>" class="newmenu-link"><i class="fa fa-sliders"></i> <?= lang('Accounts Dashboard'); ?></a></li>
                <li><a href="<?= admin_url('accounts'); ?>" class="newmenu-link"><i class="fa fa-calculator"></i> <?= lang('Charts Of Accounts'); ?></a></li>
                <li><a href="<?= admin_url('entries'); ?>" class="newmenu-link"><i class="fa fa-pencil-square-o"></i> <?= lang('GL Entry'); ?></a></li>
                <li><a href="<?= admin_url('reports/general_ledger_trial_balance'); ?>" class="newmenu-link"><i class="fa fa-file-text-o"></i><?= lang('Trial Balance'); ?></a></li>
            </ul>
        </li>
        <?php } ?>

         
        <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
        <!-- ==================== -->
        <!-- LOYALTY & BUDGET -->
        <!-- ==================== -->
        <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-heart"></i>
                <span><?= lang('Loyalty & Budget'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">
                <!-- Budget Management -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-money"></i>
                        <span><?= lang('Budget Management'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('loyalty/budget_definition'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Define Budget'); ?></a></li>
                        <li><a href="<?= admin_url('loyalty/budget_allocation'); ?>" class="newmenu-link"><i class="fa fa-sitemap"></i> <?= lang('Allocate Budget'); ?></a></li>
                        <li><a href="<?= admin_url('loyalty/burn_rate_dashboard'); ?>" class="newmenu-link"><i class="fa fa-fire"></i> <?= lang('Burn Rate Dashboard'); ?></a></li>
                        <li><a href="<?= admin_url('loyalty/budget_distribution'); ?>" class="newmenu-link"><i class="fa fa-chart-bar"></i> <?= lang('Budget Tracking'); ?></a></li>
                    </ul>
                </li>

                <!-- Loyalty Rules - HIDDEN -->
                <?php if (false): ?>
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-cogs"></i>
                        <span><?= lang('Loyalty Rules'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('loyalty/rules'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('Manage Rules'); ?></a></li>
                        <li><a href="<?= admin_url('loyalty/rules_management'); ?>" class="newmenu-link"><i class="fa fa-cogs"></i> <?= lang('Advanced Rules'); ?></a></li>
                        <li><a href="<?= admin_url('loyalty/loyalty_stages'); ?>" class="newmenu-link"><i class="fa fa-star"></i> <?= lang('Loyalty Stages'); ?></a></li>
                        <li><a href="<?= admin_url('loyalty/promotion_campaigns'); ?>" class="newmenu-link"><i class="fa fa-bullhorn"></i> <?= lang('Campaigns'); ?></a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Loyalty Dashboard -->
                <li><a href="<?= admin_url('loyalty/dashboard'); ?>" class="newmenu-link"><i class="fa fa-dashboard"></i> <?= lang('Loyalty Dashboard'); ?></a></li>
            </ul>
        </li>
        <?php } ?>

        <!-- SETTINGS -->
        <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-cogs"></i>
                <span><?= lang('Settings'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">
                <?php if($Admin || $Owner){ ?>
                <li><a href="<?= admin_url('users'); ?>" class="newmenu-link"><i class="fa fa-users"></i>
                <span class="text"> <?= lang('People'); ?></span>
               </a></li>
                <?php } ?>
                <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
               
                <li><a href="<?= admin_url('system_settings/add_ledgers'); ?>" class="newmenu-link">
                    <i class="fa fa-sliders"></i> <span class="text"><?= lang('Account Settings'); ?></span></a></li>
                
                <?php } ?>
                <?php if($Admin || $Owner || $this->GP['accountant']){ ?>
               
               <li id="organization_setup_pharmacy_hierarchy">
                    <a href="<?= admin_url('organization_setup/pharmacy_hierarchy') ?>" class="newmenu-link">
                        <i class="fa fa-hospital-o"></i><span class="text"> <?= lang('Org Setup'); ?></span>
                    </a>
                </li>   
                <li id="user_groups">
                    <a href="<?= admin_url('system_settings/user_groups') ?>" class="newmenu-link">
                        <i class="fa fa-hospital-o"></i><span class="text"> <?= lang('User Groups'); ?></span>
                    </a>
                </li>                
                <?php } ?>
                 
            </ul>
        </li>
        <?php } ?>

        <?php 
        
        if($Admin || $Owner || $this->GP['sales-deliveries'] || $this->GP['truck_registration_view'] ||  $this->GP['products-adjustments'] || $this->GP['inventory-check'] || $this->GP['rasd-notifications']){
            
        if($this->GP['rasd-notifications']){

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
        </li>
        <?php } ?>

        <!-- Warehouse Management -->
         <li class="newmenu-item has-sub">
            <a href="#" class="newmenu-link bluecolor">
                <i class="fa fa-cogs"></i>
                <span><?= lang('Warehouse Management'); ?></span>
                <i class="fa fa-chevron-right newmenu-chevron"></i>
            </a>
            <ul class="newmenu-sub">
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-truck"></i>
                        <span><?= lang('Inventory Check'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['inventory-check']){ ?>
                            <li><a href="<?= admin_url('stock_request/hills_inventory_check'); ?>" class="newmenu-link"><i class="fa fa-check-circle"></i> <?= lang('Inventory Check'); ?></a></li>
                        <?php } ?>
                        <?php //if($Admin || $Owner || $this->GP['inventory-requests']){ ?>
                            <li><a href="<?= admin_url('stock_request/inventory_check'); ?>" class="newmenu-link"><i class="fa fa-check-circle"></i> <?= lang('Inventory Requests'); ?></a></li>
                        <?php //} ?>
                    </ul>
                </li>
                <?php if($Admin || $Owner || $this->GP['sales-deliveries']){ ?>
                <!-- Delivery -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-truck"></i>
                        <span><?= lang('Delivery'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <?php if($Admin || $Owner || $this->GP['sales-add_delivery']){ ?>
                        <li><a href="<?= admin_url('delivery/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Delivery'); ?></a></li>
                        <?php } ?>
                        <?php if($Admin || $Owner || $this->GP['sales-deliveries']){ ?>
                        <li><a href="<?= admin_url('delivery'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List Deliveries'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>
                <?php if($Admin || $Owner || $this->GP['truck_registration_view']){ ?>
                <!-- Truck Registration -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-truck"></i>
                        <span><?= lang('Truck Registration'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('truck_registration/add'); ?>" class="newmenu-link"><i class="fa fa-plus-circle"></i> <?= lang('Add Truck'); ?></a></li>
                        <li><a href="<?= admin_url('truck_registration'); ?>" class="newmenu-link"><i class="fa fa-list"></i> <?= lang('List Trucks'); ?></a></li>
                    </ul>
                </li>
                <?php } ?>

                <?php if($Admin || $Owner || $this->GP['products-adjustments']){ ?>
                <!-- Adjustments -->
                <li class="newmenu-item has-sub">
                    <a href="#" class="newmenu-link">
                        <i class="fa fa-archive"></i>
                        <span><?= lang('Inventory Adjustment'); ?></span>
                        <i class="fa fa-chevron-right newmenu-chevron"></i>
                    </a>
                    <ul class="newmenu-sub">
                        <li><a href="<?= admin_url('products/add_adjustment'); ?>" class="newmenu-link"><i class="fa fa-users"></i> <?= lang('Add Adjustment'); ?></a></li>
                        <li><a href="<?= admin_url('products/quantity_adjustments'); ?>" class="newmenu-link"><i class="fa fa-users"></i> <?= lang('List Adjustments'); ?></a></li>
                    </ul>
                </li>
                <?php } ?>
            </ul>

        </li>
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

