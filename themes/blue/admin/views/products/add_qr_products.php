<!-- QR Scan Input -->

<?php
$attrib = ['data-toggle' => 'validator', 'role' => 'form'];
echo admin_form_open('products/add_qr_products', $attrib)
    ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Scan QR Code'); ?></h2>
    </div>
    <div class="box-content">
        <div class="form-row">
            <div class="form-group col-md-4 all">
                <?= lang('category', 'category') ?>
                <?php
                $cat[''] = '';
                foreach ($categories as $category) {
                    $cat[$category->id] = $category->name;
                }
                echo form_dropdown(
                    'category',
                    $cat,
                    ($_POST['category'] ?? ($product ? $product->category_id : '')),
                    'class="form-control select" id="category" placeholder="' . lang('select') . ' ' . lang('category') . '" required="required" style="width:100%"'
                );
                ?>
            </div>
            <div class="form-group col-md-4 all">
                <?= lang('subcategory', 'subcategory') ?>
                <div class="controls" id="subcat_data">
                    <?php
                    echo form_input(
                        'subcategory',
                        ($product ? $product->subcategory_id : ''),
                        'class="form-control" id="subcategory" placeholder="' . lang('select_category_to_load') . '"'
                    );
                    ?>
                </div>
            </div>
        </div>

        <div class="form-row">

            <div class="form-group col-md-6">
                <div class="controls" >
                <input type="text" id="qrInput" class="form-control scan-input" placeholder="Scan QR Code here..."
                    autofocus>
                    </div>
            </div>
        </div>


        <!-- Products Table -->
        <table class="table table-bordered align-middle" style="margin-top:10px;">
            <thead class="table-light">
                <tr>
                    <th>GTIN</th>
                    <th>Product Name</th>
                    <th>Sale Price</th>
                    <th>Cost Price</th>
                    <th>Vat</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="productsTable">
                <!-- Rows will be appended here -->
            </tbody>
        </table>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" ><?= lang('submit') ?></button>
        </div>

    </div>
</div>
<?= form_close(); ?>

<script>
    const qrInput = document.getElementById('qrInput');
    const productsTable = document.getElementById('productsTable');

    function parseGS1(data) {
        //console.log("data"+data)
        const result = { gtin: null}; //serial: null, expiry: null, batch: null };
         if (data == null || data.trim() === '') return result; 

        // Try a strict ordered pattern first (non-greedy for variable fields)
        let m = data.match(/01(\d{14})/);
        console.log("fsf"+m)
        if (m) {

            result.gtin = m[1];
            // result.serial = stripGS(m[2]);
            // result.expiry = formatExpiry(m[3]);
            // result.batch = stripGS(m[4]);
            return result;
        }
    }

    function stripGS(s) {
        if (!s) return s;
        // remove group separator chars and whitespace
        return s.replace(/\x1D/g, '').trim();
    }

    function formatExpiry(yyMMdd) {
        if (!yyMMdd || yyMMdd.length !== 6) return null;
        let yy = yyMMdd.slice(0, 2);
        const mm = yyMMdd.slice(2, 4);
        const dd = yyMMdd.slice(4, 6);
        // Basic century heuristic: >=50 => 19xx else 20xx
        const century = parseInt(yy, 10) >= 50 ? '19' : '20';
        return `${century}${yy}-${mm}-${dd}`;
    }

    qrInput.addEventListener('change', () => {
        const data = parseGS1(qrInput.value.trim());
        console.log(data)
 //   <td><input  type="text" name="product_name[]" class="form-control"></td>
        const row = document.createElement('tr');
        row.innerHTML = `
   
      <td><input class="form-control" type="text" name="gtin[]" value="${data.gtin}"></td>
        <td><input  type="text" name="product_name[]" class="form-control product-name-input" autocomplete="off" >
        <div class="autocomplete-list"></div> </td>
       
      <td><input class="form-control price-input" type="text" name="price[]" value=""></td>
      <td><input class="form-control cost-input" type="text" name="cost[]" value=""></td>
      <td><select class="form-control" name="tax[]">
      <option value="1">No Tax</option>
      <option value="15"> @15 </option>
      </td>
      <td><button class="btn btn-sm btn-danger remove-btn">Remove</button></td>
    `;
        productsTable.appendChild(row);


        // Clear input for next scan
        qrInput.value = '';
        qrInput.focus();

        // Remove row
        row.querySelector('.remove-btn').addEventListener('click', () => {
            row.remove();
        });
    });

    $('#category').change(function () {
        var v = $(this).val();
        $('#modal-loading').show();
        if (v) {
            $.ajax({
                type: "get",
                async: false,
                url: "<?= admin_url('products/getSubCategories') ?>/" + v,
                dataType: "json",
                success: function (scdata) {
                    if (scdata != null) {
                        scdata.push({ id: '', text: '<?= lang('select_subcategory') ?>' });
                        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                            placeholder: "<?= lang('select_category_to_load') ?>",
                            minimumResultsForSearch: 7,
                            data: scdata
                        });
                    } else {
                        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                            placeholder: "<?= lang('no_subcategory') ?>",
                            minimumResultsForSearch: 7,
                            data: [{ id: '', text: '<?= lang('no_subcategory') ?>' }]
                        });
                    }
                },
                error: function () {
                    bootbox.alert('<?= lang('ajax_error') ?>');
                    $('#modal-loading').hide();
                }
            });
        } else {
            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                placeholder: "<?= lang('select_category_to_load') ?>",
                minimumResultsForSearch: 7,
                data: [{ id: '', text: '<?= lang('select_category_to_load') ?>' }]
            });
        }
        $('#modal-loading').hide();
    });

    document.addEventListener('input', function(e) {
  if (!e.target.classList.contains('product-name-input')) return;

  const input = e.target;
  const listContainer = input.nextElementSibling; // the div for showing suggestions
  const query = input.value.trim();

  // Clear previous list
  listContainer.innerHTML = '';

  if (query.length < 2) return;  // Minimum 2 chars before search

  fetch(`<?= admin_url('products/master_data_search') ?>?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
      if (data.length === 0) return;

      const ul = document.createElement('ul');
      ul.style.position = 'absolute';
      ul.style.background = 'white';
      ul.style.border = '1px solid #ccc';
      ul.style.width = input.offsetWidth + 'px';
      ul.style.maxHeight = '150px';
      ul.style.overflowY = 'auto';
      ul.style.zIndex = '1000';
      ul.style.padding = '0';
      ul.style.margin = '0';
      ul.style.listStyle = 'none';

      data.forEach(item => {
        const li = document.createElement('li');
        li.style.padding = '5px';
        li.style.cursor = 'pointer';
        li.textContent = item.trade_name;

        li.addEventListener('click', () => {
          input.value = item.trade_name;

          // Find the same row and fill price and cost inputs if present
          const row = input.closest('tr');
          if (row) {
            const priceInput = row.querySelector('input[name="price[]"]');
            const costInput = row.querySelector('input[name="cost[]"]');
            if (priceInput) priceInput.value = item.public_price || '';
            if (costInput) costInput.value = item.public_price || '';
          }

          // Clear autocomplete list after selection
          listContainer.innerHTML = '';
        });

        ul.appendChild(li);
      });

      listContainer.appendChild(ul);
    })
    .catch(err => {
      console.error('Autocomplete search error:', err);
    });
});

</script>