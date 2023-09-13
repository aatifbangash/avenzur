<?php 




SELECT prd.id, prd.code, prd.name, data.*

FROM sma_products as prd

LEFT JOIN ( 

SELECT entry_id, entry_date, type, document_no, name_of, batch_no, expiry_date, quantity, unit_cost, system_serial, sale_price, purchase_price, product_id

FROM (

    -- Sales
    SELECT sale.id as entry_id, sale.date as entry_date, 'Sale' as type, sale.invoice_number as document_no, sale.customer as name_of, saleItem.batch_no as batch_no,
    saleItem.expiry as expiry_date, saleItem.quantity as quantity, saleItem.net_unit_price as unit_cost,
    NULL as system_serial, NULL as sale_price, NULL as purchase_price, saleItem.product_id

    FROM sma_sales as sale

    LEFT JOIN sma_sale_items as saleItem ON saleItem.sale_id = sale.id

    WHERE saleItem.product_id = 10585 AND DATE(sale.date) >= '2023-08-01' AND DATE(sale.date) <= '2023-09-11'

    UNION ALL

    -- Purchases
    SELECT purchase.id as entry_id, purchase.date as entry_date, 'Purchase' as type, purchase.invoice_number as document_no, purchase.supplier as name_of, pitem.batchno as batch_no, 
    pitem.expiry as expiry_date, pitem.quantity as quantity, pitem.net_unit_cost as unit_cost,
    NULL as system_serial, pitem.sale_price as sale_price, NULL as purchase_price, pitem.product_id

    FROM sma_purchases as purchase

    LEFT JOIN sma_purchase_items as pitem ON pitem.purchase_id = purchase.id

    WHERE pitem.product_id = 10585 AND DATE(purchase.date) >= '2023-08-01' AND DATE(purchase.date) <= '2023-09-11'  AND purchase.grand_total > 0 


    UNION ALL

    -- Return
    SELECT rtn.id as entry_id, rtn.date as entry_date, 'Return Customer' as type, rtn.invoice_number as document_no, rtn.customer as name_of, ritem.batch_no as batch_no, 
    ritem.expiry as expiry_date, ritem.quantity as quantity, ritem.net_unit_price as unit_cost,
    NULL as system_serial, NULL as sale_price, NULL as return_price, ritem.product_id

    FROM sma_returns as rtn

    LEFT JOIN sma_return_items as ritem ON ritem.return_id = rtn.id

    WHERE ritem.product_id = 43 AND DATE(rtn.date) >= '2023-08-01' AND DATE(rtn.date) <= '2023-09-11' 

    UNION ALL 

    -- Return Supplier
    SELECT purchase.id as entry_id, purchase.date as entry_date, 'Return Supplier' as type, purchase.invoice_number as document_no, purchase.supplier as name_of, pitem.batchno as batch_no, 
    pitem.expiry as expiry_date, pitem.quantity as quantity, pitem.net_unit_cost as unit_cost,
    NULL as system_serial, pitem.sale_price as sale_price, NULL as purchase_price, pitem.product_id

    FROM sma_purchases as purchase

    LEFT JOIN sma_purchase_items as pitem ON pitem.purchase_id = purchase.id

    WHERE pitem.product_id = 10585 AND DATE(purchase.date) >= '2023-08-01' AND DATE(purchase.date) <= '2023-09-11'  AND purchase.grand_total < 0 


    UNION ALL

    -- Transfer In
    SELECT trnf.id as entry_id, trnf.date as entry_date, 'Transfer In' as type,  trnf.invoice_number as document_no, CONCAT(trnf.from_warehouse_name,' - ',trnf.to_warehouse_name) as name_of, titm.batchno as batch_no, 
    titm.expiry as expiry_date, titm.quantity as quantity, titm.net_unit_cost as unit_cost,
    NULL as system_serial, NULL as sale_price, NULL as purchase_price, titm.product_id

    FROM sma_transfers as trnf


    LEFT JOIN (

         SELECT transfer_id, 
                  batchno, expiry, quantity, net_unit_cost, product_id
        
         FROM (

            SELECT transfer_id, 
                  batchno, expiry, quantity, net_unit_cost, product_id
           FROM sma_transfer_items 
           WHERE  `product_id` = '10823' 
           AND warehouse_id = 39
           AND DATE(`date`) >= '2023-08-01' AND DATE(`date`) <= '2023-09-11'
           GROUP BY transfer_id

           UNION ALL


        SELECT transfer_id, 
                      batchno, expiry, quantity, net_unit_cost, product_id
               FROM sma_purchase_items 
               WHERE  `product_id` = '10823' 
               AND warehouse_id = 39
               AND DATE(`date`) >= '2023-08-01' AND DATE(`date`) <= '2023-09-11'
               AND transfer_id IS NOT NULL
               GROUP BY transfer_id


                  ) AS combined_transfer_in

        ) AS titm 
        ON titm.transfer_id = trnf.id 

        WHERE  DATE(trnf.date) >= '2023-08-01' AND DATE(trnf.date) <= '2023-09-11' AND titm.product_id = 10823


    UNION ALL

    -- Transfer Out
    SELECT trnf.id as entry_id, trnf.date as entry_date, 'Transfer Out' as type,  trnf.invoice_number as document_no, CONCAT(trnf.from_warehouse_name,' - ',trnf.to_warehouse_name) as name_of, titm.batchno as batch_no, 
    titm.expiry as expiry_date, titm.quantity as quantity, titm.net_unit_cost as unit_cost,
    NULL as system_serial, NULL as sale_price, NULL as purchase_price, titm.product_id

    FROM sma_transfers as trnf

    LEFT JOIN (

         SELECT transfer_id, 
                  batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id
        
         FROM (

            SELECT transfer_id, 
                  batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id
           FROM sma_transfer_items 
           WHERE  `product_id` = '9778' 
           AND DATE(`date`) >= '2023-08-01' AND DATE(`date`) <= '2023-09-11'
           GROUP BY transfer_id

           UNION ALL

           SELECT transfer_id, 
                          batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id
                   FROM sma_purchase_items 
                   WHERE  `product_id` = '9778' 
                   AND DATE(`date`) >= '2023-08-01' AND DATE(`date`) <= '2023-09-11'
                   AND transfer_id IS NOT NULL
                   GROUP BY transfer_id


              ) AS combained

    ) AS titm 
    ON titm.transfer_id = trnf.id
    WHERE  DATE(trnf.date) >= '2023-08-01' AND DATE(trnf.date) <= '2023-09-11'  AND trnf.from_warehouse_id = 32 AND titm.product_id = 9778


 ) AS combined_by_types)

 as data ON data.product_id = prd.id 

 WHERE prd.id IN (10585, 9778, 10823);