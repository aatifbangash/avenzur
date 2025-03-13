<ul class="nav main-menu">

    <li class="mm_welcome">
            <a href="<?= admin_url() ?>">
                <i class="fa fa-dashboard"></i>
                <span class="text"> <?= lang('dashboard'); ?></span>
            </a>
    </li>

    <li class="mm_products">
                <a class="dropmenu" href="#">
                    <i class="fa fa-barcode"></i>
                    <span class="text"> <?= lang('products'); ?> </span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <li id="products_index">
                        <a class="submenu" href="<?= admin_url('products'); ?>">
                            <i class="fa fa-barcode"></i>
                            <span class="text"> <?= lang('list_products'); ?></span>
                        </a>
                    </li>
                    <li id="products_add">
                        <a class="submenu" href="<?= admin_url('products/add'); ?>">
                            <i class="fa fa-plus-circle"></i>
                            <span class="text"> <?= lang('add_product'); ?></span>
                        </a>
                    </li>
                    <li id="products_import_csv">
                        <a class="submenu" href="<?= admin_url('products/import_csv'); ?>">
                            <i class="fa fa-file-text"></i>
                            <span class="text"> <?= lang('import_products'); ?></span>
                        </a>
                    </li>
                    
                </ul>
            </li>

            <li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                <a class="dropmenu" href="#">
                    <i class="fa fa-heart"></i>
                    <span class="text"> <?= lang('sales'); ?>
                    </span> <span class="chevron closed"></span>
                </a>
                <ul>
                    
                    
                    <li id="pos_sales">
                        <a class="submenu" href="<?= admin_url('pos/sales'); ?>">
                            <i class="fa fa-heart"></i>
                            <span class="text"> <?= lang('pos_sales'); ?></span>
                        </a>
                    </li>
                    <li id="pos_sales_wise">
                        <a class="submenu" href="<?= admin_url('pos/sales_date_wise'); ?>">
                            <i class="fa fa-heart"></i><span class="text"> <?= lang('POS_Sales_Date_Wise'); ?></span>
                        </a>
                    </li>
                        
                    
                </ul>
            </li>
            <li class="mm_purchases">
                <a class="dropmenu" href="#">
                    <i class="fa fa-star"></i>
                    <span class="text"> <?= lang('purchases'); ?>
                    </span> <span class="chevron closed"></span>
                </a>
                <ul>
                    <li id="purchases_index">
                        <a class="submenu" href="<?= admin_url('purchases'); ?>">
                            <i class="fa fa-star"></i>
                            <span class="text"> <?= lang('list_purchases'); ?></span>
                        </a>
                    </li>
                    <li id="purchases_add">
                        <a class="submenu" href="<?= admin_url('purchases/add'); ?>">
                            <i class="fa fa-plus-circle"></i>
                            <span class="text"> <?= lang('add_purchase'); ?></span>
                        </a>
                    </li>
                    
                    
                </ul>
            </li>

            <li class="mm_transfers">
                <a class="dropmenu" href="#">
                    <i class="fa fa-star-o"></i>
                    <span class="text"> <?= lang('transfers'); ?> </span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <li id="transfers_index">
                        <a class="submenu" href="<?= admin_url('transfers'); ?>">
                            <i class="fa fa-star-o"></i><span class="text"> <?= lang('list_transfers'); ?></span>
                        </a>
                    </li>
                    <li id="transfers_add">
                        <a class="submenu" href="<?= admin_url('transfers/add'); ?>">
                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer'); ?></span>
                        </a>
                    </li>
                    
                </ul>
            </li>

            <li class="mm_returns">
                <a class="dropmenu" href="#">
                    <i class="fa fa-random"></i>
                    <span class="text"> <?= lang('returns'); ?> </span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <li id="returns_index">
                        <a class="submenu" href="<?= admin_url('returns'); ?>">
                            <i class="fa fa-random"></i><span class="text"> <?= lang('list_returns'); ?></span>
                        </a>
                    </li>
                    
                    <li id="returns_add">
                        <a class="submenu" href="<?= admin_url('returns/add'); ?>">
                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('Add_Return_Customer'); ?></span>
                        </a>
                    </li>


                    <li id="returns_index">
                        <a class="submenu" href="<?= admin_url('returns_supplier'); ?>">
                            <i class="fa fa-random"></i><span class="text"> <?= lang('List_Returns_Suppliers'); ?></span>
                        </a>
                    </li>

                    <li id="returns_add">
                        <a class="submenu" href="<?= admin_url('returns_supplier/add'); ?>">
                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('Add_Return_Supplier'); ?></span>
                        </a>
                    </li>
                    
                </ul>
            </li>  

            <li class="mm_auth mm_customers mm_suppliers mm_billers">
                <a class="dropmenu" href="#">
                    <i class="fa fa-users"></i>
                    <span class="text"> <?= lang('suppliers'); ?> </span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    
                    <li id="suppliers_index">
                        <a class="submenu" href="<?= admin_url('suppliers'); ?>">
                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
                        </a>
                    </li>
                    
                    <li id="suppliers_index">
                        <a class="submenu" href="<?= admin_url('suppliers/add'); ?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
                        </a>
                    </li>
                    
                </ul>
            </li> 
            

            <li class="mm_stock_requests">
                <a class="dropmenu" href="#">
                    <i class="fa fa-star-o"></i>
                    <span class="text"> <?= lang('Stock Requests'); ?> </span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <li id="stock_requests_index">
                        <a class="submenu" href="<?= admin_url('stock_request/stock_order'); ?>">
                            <i class="fa fa-star-o"></i><span class="text"> <?= lang('New Stock Request'); ?></span>
                        </a>
                    </li>
                    <li id="stock_requests_index">
                        <a class="submenu" href="<?= admin_url('stock_request'); ?>">
                            <i class="fa fa-star-o"></i><span class="text"> <?= lang('List Stock Requests'); ?></span>
                        </a>
                    </li>
                </ul>
            </li>

            <?php 

            if(($this->Owner || $this->Admin) || $GP['stock_request_view']){
                ?>

            <li class="mm_stock_requests">
                <a class="dropmenu" href="#">
                    <i class="fa fa-star-o"></i>
                    <span class="text"> <?= lang('Purchase Requests'); ?> </span>
                    <span class="chevron closed"></span>
                </a>
                <ul>
                    <li id="stock_requests_index">
                        <a class="submenu" href="<?= admin_url('stock_request/current_pr'); ?>">
                            <i class="fa fa-star-o"></i><span class="text"> <?= lang('Opened PR'); ?></span>
                        </a>
                    </li>
                    <li id="stock_requests_index">
                        <a class="submenu" href="<?= admin_url('stock_request/purchase_requests'); ?>">
                            <i class="fa fa-star-o"></i><span class="text"> <?= lang('List Purchase Requests'); ?></span>
                        </a>
                    </li>
                </ul>
            </li>

            <?php } ?>

            <?php
            if (($this->Owner || $this->Admin)) {
                ?>
                    <li class="mm_auth mm_customers mm_suppliers mm_billers">
                        <a class="dropmenu" href="#">
                        <i class="fa fa-users"></i>
                        <span class="text"> <?= lang('users'); ?> </span>
                        <span class="chevron closed"></span>
                        </a>
                        <ul>
                            <li id="auth_users">
                                <a class="submenu" href="<?= admin_url('users'); ?>">
                                    <i class="fa fa-users"></i><span class="text"> <?= lang('list_users'); ?></span>
                                </a>
                            </li>
                            <li id="auth_create_user">
                                <a class="submenu" href="<?= admin_url('users/create_user'); ?>">
                                    <i class="fa fa-user-plus"></i><span class="text"> <?= lang('new_user'); ?></span>
                                </a>
                            </li>
                        </ul>
                <?php
            }
            ?>
</ul>