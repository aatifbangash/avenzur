ALTER TABLE sma_purchase_items ADD COLUMN avz_item_code INT(6) ZEROFILL UNIQUE;
UPDATE purchase_items SET avz_item_code = LPAD(id, 6, '0');

ALTER TABLE sma_inventory_movements ADD COLUMN avz_item_code INT(11);

UPDATE sma_inventory_movements im 
JOIN sma_purchase_items pi ON im.reference_id = pi.purchase_id 
AND im.location_id = pi.warehouse_id 
AND im.batch_number = pi.batchno 
SET im.avz_item_code = pi.avz_item_code 
WHERE im.type = 'purchase';
