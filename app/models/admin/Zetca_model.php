<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Zetca_model extends CI_Model{

    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('Settings_model');
    }

    public function get_zetca_settings(){
        $settings = $this->Settings_model ->getSettings();
        $zetca_settings = array(
            "zatca_enabled" => isset($settings->zatca_enabled) ? $settings->zatca_enabled : null,
            "zatca_url" => isset($settings->zatca_url) ? $settings->zatca_url : null,
            "zatca_appkey" => isset($settings->zatca_appkey) ? $settings->zatca_appkey : null,
            "zatca_secretKey" => isset($settings->zatca_secretkey) ? $settings->zatca_secretkey : null,
        );
        return $zetca_settings;
    }
     
    public function create_simple_invoice_without_discount($sale, $items){
        $payload = array();
        $date = new DateTime($sale->issueDate);
        $formatedDate = $date->format("Y-m-d\TH:i:s.v\Z");
        $payload['kind'] = "Simplified-Invoice";
        $payload['invoiceNo'] = 'INV'.$sale->invoiceNo;
        $payload['issueDate'] = $formatedDate;
        $payload['currency'] = 'SAR';
        $payload['items']  = [];
        $totalDiscount = 0;
        $totalAmountBeforeVatAndDiscount = 0;
        $totalNetBeforeTax = 0;
        $totalTaxAmount = 0;
        $totalGrossAmount = 0;
        $totalAmount = 0;
        foreach($items as $item){
            $discount = 0;
            /*if($sale->order_discount_id){
                $discount = (float) rtrim($sale->order_discount_id,"%");
            }*/
            $row = [];
            $row['product'] = $item ['product_name'];
            $row['quantity'] = (int) $item['quantity'];
            $row['unit']= 'EA';
            $row['unitPrice']= round(((float) $item['unit_price']),2);
          
            $row['tax'] = (float) $item['rate'];
            $row['taxFormat'] = '%';
            $row['taxCategory'] = "Standard";
            $row['taxExemptionReasonCode']  = "";
            $row['taxExemptionReason'] = "";
            if(!$row['tax']){
                $row['taxCategory'] = "Zero Rated Goods";
                $row['taxExemptionReasonCode']  = "VATEX-SA-35";
                $row['taxExemptionReason'] = "Medicines and medical Equipment";
            }            
          
            $row['amount'] = round(((float) $item['unit_price'] * (int) $item['quantity']),2);
            if($sale->documentDiscount){
                //$row['discount'] = $discount;
                //$row['discountFormat'] = "%";
                //$row['discountAmount'] = round(($row['amount']  * $discount/100),2);
                $row['discountAmount'] = round(($item['item_discount']),2);
                $row['netAmount'] =  round(((float) $item['totalbeforevat']),2);
                $totalDiscount = round(($totalDiscount + $row['discountAmount']),2);
            }else{
                $row['netAmount'] = round(( (float) $item['totalbeforevat']),2);
            }
             
            $row['taxAmount'] = round(($row['netAmount'] * $row['tax']/100),2);
            $row['grossAmount'] = round(($row['netAmount'] + $row['taxAmount']),2);
            $totalAmount = round(($totalAmount + $row['amount']),2);
            $totalNetBeforeTax = round(($totalNetBeforeTax + $row['netAmount']),2);
            $totalAmountBeforeVatAndDiscount = round(($totalAmountBeforeVatAndDiscount + $item['totalbeforevat']),2);
            $totalTaxAmount = round(($totalTaxAmount + $row['taxAmount']),2);
            $totalGrossAmount = round(($totalGrossAmount + $row['grossAmount']),2);
            $payload['items'][] = $row;
        }
        $payload['amount'] = $totalAmount;
        if($sale->documentDiscount){
            $payload['discountAmount'] = round(( (float) $sale->documentDiscount),2);
        }
        $payload['netAmount'] = $totalNetBeforeTax;
        $payload['taxAmount'] = $totalTaxAmount;
        $payload['grossAmount']  = round($totalGrossAmount,3);
        return $payload;
    }

    public function create_return_b2c($return, $items){
        /**
         * Fetch the customer details
         */
        $this->db->select("id,name, vat_no,address,city,state,postal_code,country, phone, cr");
        $this->db->from("sma_companies");
        $this->db->where("id", $return->customer_id);
        $company = $this->db->get()->row();
        

        $payload = array();
        $date = new DateTime($return->issueDate);
        $formatedDate = $date->format("Y-m-d\TH:i:s.v\Z");
        $payload['kind'] = "Simplified-Credit-Note";
        $payload['invoiceNo'] = 'RS'.$return->invoiceNo;
        $payload['returnInvoiceNo'] = 'RS'.$return->invoiceNo;
        $payload['instructionNote'] = 'Cancellation';
        $payload['issueDate'] = $formatedDate;
        $payload['returnInvoiceDate'] = $formatedDate;
        $payload['currency'] = 'SAR';
        $payload['clientData'] = [
            "clientNo"=> $company->cr,
            "name" => $company->name,
            "buildingNo"  => $company->address,
            "taxNo" => $company->vat_no,
            "street" => "",
            "city"=> $company->city,
            "district"=> $company->state,
            "postalCode"=> $company->postal_code,
            "countryCode"=> $company->country
        ];
        $payload['items']  = [];
        $totalDiscount = 0;
        $totalAmountBeforeVatAndDiscount = 0;
        $totalNetBeforeTax = 0;
        $totalTaxAmount = 0;
        $totalGrossAmount = 0;
        $totalAmount = 0;
        foreach($items as $item){
            $discount = 0;
            /*if($sale->order_discount_id){
                $discount = (float) rtrim($sale->order_discount_id,"%");
            }*/
            $row = [];
            $row['product'] = $item ['product_name'];
            $row['quantity'] = (int) $item['quantity'];
            $row['unit']= 'EA';
            $row['unitPrice']= round(((float) $item['unit_price']),2);
          
            $row['tax'] = (float) $item['rate'];
            $row['taxFormat'] = '%';
            $row['taxCategory'] = "Standard";
            $row['taxExemptionReasonCode']  = "";
            $row['taxExemptionReason'] = "";
            if(!$row['tax']){
                $row['taxCategory'] = "Zero Rated Goods";
                $row['taxExemptionReasonCode']  = "VATEX-SA-35";
                $row['taxExemptionReason'] = "Medicines and medical Equipment";
            }            
          
            $row['amount'] = round(((float) $item['unit_price'] * (int) $item['quantity']),2);
            if($return->documentDiscount){
                //$row['discount'] = $discount;
                //$row['discountFormat'] = "%";
                //$row['discountAmount'] = round(($row['amount']  * $discount/100),2);
                $row['discountAmount'] = round(($item['item_discount']),2);
                $row['netAmount'] =  round(((float) $item['totalbeforevat']),2);
                $totalDiscount = round(($totalDiscount + $row['discountAmount']),2);
            }else{
                $row['netAmount'] = round(( (float) $item['totalbeforevat']),2);
            }
             
            $row['taxAmount'] = round(($row['netAmount'] * $row['tax']/100),2);
            $row['grossAmount'] = round(($row['netAmount'] + $row['taxAmount']),2);
            $totalAmount = round(($totalAmount + $row['amount']),2);
            $totalNetBeforeTax = round(($totalNetBeforeTax + $row['netAmount']),2);
            $totalAmountBeforeVatAndDiscount = round(($totalAmountBeforeVatAndDiscount + $item['totalbeforevat']),2);
            $totalTaxAmount = round(($totalTaxAmount + $row['taxAmount']),2);
            $totalGrossAmount = round(($totalGrossAmount + $row['grossAmount']),2);
            $payload['items'][] = $row;
        }
        $payload['amount'] = $totalAmount;
        if($return->documentDiscount){
            $payload['discountAmount'] = round(( (float) $return->documentDiscount),2);
        }
        $payload['netAmount'] = $totalNetBeforeTax;
        $payload['taxAmount'] = $totalTaxAmount;
        $payload['grossAmount']  = round($totalGrossAmount,3);
        return $payload;
    }

    public function create_return_b2b($return, $items){
        /**
         * Fetch the customer details
         */
        $this->db->select("id,name, vat_no,address,city,state,postal_code,country, phone, cr");
        $this->db->from("sma_companies");
        $this->db->where("id", $return->customer_id);
        $company = $this->db->get()->row();
        

        $payload = array();
        $date = new DateTime($return->issueDate);
        $formatedDate = $date->format("Y-m-d\TH:i:s.v\Z");
        $payload['kind'] = "Credit-Note";
        $payload['invoiceNo'] = 'RC'.$return->invoiceNo;
        $payload['returnInvoiceNo'] = 'RC'.$return->invoiceNo;
        $payload['instructionNote'] = 'Cancellation';
        $payload['issueDate'] = $formatedDate;
        $payload['returnInvoiceDate'] = $formatedDate;
        $payload['currency'] = 'SAR';
        $payload['clientData'] = [
            "clientId" => $return->customer_id,
            "clientNo"=> $company->cr,
            "name" => $company->name,
            "buildingNo"  => $company->address,
            "taxNo" => $company->vat_no,
            "street" => "",
            "city"=> $company->city,
            "district"=> $company->state,
            "postalCode"=> $company->postal_code,
            "countryCode"=> $company->country
        ];
        $payload['items']  = [];
        $totalDiscount = 0;
        $totalAmountBeforeVatAndDiscount = 0;
        $totalNetBeforeTax = 0;
        $totalTaxAmount = 0;
        $totalGrossAmount = 0;
        $totalAmount = 0;
        foreach($items as $item){
            $discount = 0;
            /*if($sale->order_discount_id){
                $discount = (float) rtrim($sale->order_discount_id,"%");
            }*/
            $row = [];
            $row['product'] = $item ['product_name'];
            $row['quantity'] = (int) $item['quantity'];
            $row['unit']= 'EA';
            $row['unitPrice']= round(((float) $item['unit_price']),2);
          
            $row['tax'] = (float) $item['rate'];
            $row['taxFormat'] = '%';
            $row['taxCategory'] = "Standard";
            $row['taxExemptionReasonCode']  = "";
            $row['taxExemptionReason'] = "";
            if(!$row['tax']){
                $row['taxCategory'] = "Zero Rated Goods";
                $row['taxExemptionReasonCode']  = "VATEX-SA-35";
                $row['taxExemptionReason'] = "Medicines and medical Equipment";
            }            
          
            $row['amount'] = round(((float) $item['unit_price'] * (int) $item['quantity']),2);
            if($return->documentDiscount){
                //$row['discount'] = $discount;
                //$row['discountFormat'] = "%";
                //$row['discountAmount'] = round(($row['amount']  * $discount/100),2);
                $row['discountAmount'] = round(($item['item_discount']),2);
                $row['netAmount'] =  round(((float) $item['totalbeforevat']),2);
                $totalDiscount = round(($totalDiscount + $row['discountAmount']),2);
            }else{
                $row['netAmount'] = round(( (float) $item['totalbeforevat']),2);
            }
             
            $row['taxAmount'] = round(($row['netAmount'] * $row['tax']/100),2);
            $row['grossAmount'] = round(($row['netAmount'] + $row['taxAmount']),2);
            $totalAmount = round(($totalAmount + $row['amount']),2);
            $totalNetBeforeTax = round(($totalNetBeforeTax + $row['netAmount']),2);
            $totalAmountBeforeVatAndDiscount = round(($totalAmountBeforeVatAndDiscount + $item['totalbeforevat']),2);
            $totalTaxAmount = round(($totalTaxAmount + $row['taxAmount']),2);
            $totalGrossAmount = round(($totalGrossAmount + $row['grossAmount']),2);
            $payload['items'][] = $row;
        }
        $payload['amount'] = $totalAmount;
        if($return->documentDiscount){
            $payload['discountAmount'] = round(( (float) $return->documentDiscount),2);
        }
        $payload['netAmount'] = $totalNetBeforeTax;
        $payload['taxAmount'] = $totalTaxAmount;
        $payload['grossAmount']  = round($totalGrossAmount,3);
        return $payload;
    }

    public function create_invoice_b2b($sale, $items){

        /**
         * Fetch the customer details
         */
        $this->db->select("id,name, vat_no,address,city,state,postal_code,country, phone, cr");
        $this->db->from("sma_companies");
        $this->db->where("id", $sale->customer_id);
        $company = $this->db->get()->row();
        

        $payload = array();
        $date = new DateTime($sale->issueDate);
        $formatedDate = $date->format("Y-m-d\TH:i:s.v\Z");
        $payload['kind'] = "Invoice";
        $payload['invoiceNo'] = 'SL'.$sale->invoiceNo;
        $payload['issueDate'] = $formatedDate;
        $payload['currency'] = 'SAR';
        $payload['clientData'] = [
            "clientNo"=> $company->cr,
            "name" => $company->name,
            "buildingNo"  => $company->address,
            "taxNo" => $company->vat_no,
            "street" => "",
            "city"=> $company->city,
            "district"=> $company->state,
            "postalCode"=> $company->postal_code,
            "countryCode"=> $company->country
        ];
        $payload['items']  = [];
        $totalDiscount = 0;
        $totalAmountBeforeVatAndDiscount = 0;
        $totalNetBeforeTax = 0;
        $totalTaxAmount = 0;
        $totalGrossAmount = 0;
        $totalAmount = 0;
        foreach($items as $item){
            $discount = 0;
            /*if($sale->order_discount_id){
                $discount = (float) rtrim($sale->order_discount_id,"%");
            }*/
            $row = [];
            $row['product'] = $item ['product_name'];
            $row['quantity'] = (int) $item['quantity'];
            $row['unit']= 'EA';
            $row['unitPrice']= round(((float) $item['unit_price']),2);
          
            $row['tax'] = (float) $item['rate'];
            $row['taxFormat'] = '%';
            $row['taxCategory'] = "Standard";
            $row['taxExemptionReasonCode']  = "";
            $row['taxExemptionReason'] = "";
            if(!$row['tax']){
                $row['taxCategory'] = "Zero Rated Goods";
                $row['taxExemptionReasonCode']  = "VATEX-SA-35";
                $row['taxExemptionReason'] = "Medicines and medical Equipment";
            }            
          
            $row['amount'] = round(((float) $item['unit_price'] * (int) $item['quantity']),2);
            if($sale->documentDiscount){
                //$row['discount'] = $discount;
                //$row['discountFormat'] = "%";
                //$row['discountAmount'] = round(($row['amount']  * $discount/100),2);
                $row['discountAmount'] = round(($item['item_discount']),2);
                $row['netAmount'] =  round(((float) $item['totalbeforevat']),2);
                $totalDiscount = round(($totalDiscount + $row['discountAmount']),2);
            }else{
                $row['netAmount'] = round(( (float) $item['totalbeforevat']),2);
            }
             
            $row['taxAmount'] = round(($row['netAmount'] * $row['tax']/100),2);
            $row['grossAmount'] = round(($row['netAmount'] + $row['taxAmount']),2);
            $totalAmount = round(($totalAmount + $row['amount']),2);
            $totalNetBeforeTax = round(($totalNetBeforeTax + $row['netAmount']),2);
            $totalAmountBeforeVatAndDiscount = round(($totalAmountBeforeVatAndDiscount + $item['totalbeforevat']),2);
            $totalTaxAmount = round(($totalTaxAmount + $row['taxAmount']),2);
            $totalGrossAmount = round(($totalGrossAmount + $row['grossAmount']),2);
            $payload['items'][] = $row;
        }
        $payload['amount'] = $totalAmount;
        if($sale->documentDiscount){
            $payload['discountAmount'] = round(( (float) $sale->documentDiscount),2);
        }
        $payload['netAmount'] = $totalNetBeforeTax;
        $payload['taxAmount'] = $totalTaxAmount;
        $payload['grossAmount']  = round($totalGrossAmount,3);
        return $payload;
    }

    public function get_zetca_data_b2b($saleId){
        
        $this->db->select("id as invoiceNo, customer_id, date as issueDate,total_discount as documentDiscount,grand_total as grossAmount,order_discount_id, total_tax as taxAmount");
        $this->db->from("sma_sales");
        $this->db->where("id", $saleId);
        $sale = $this->db->get()->row();

        $this->db->select('product_name, quantity, unit_price, totalbeforevat, tax, item_discount,main_net, tr.rate');
        $this->db->from('sma_sale_items');
        $this->db->join('sma_tax_rates as tr', 'tr.id = tax_rate_id');
        $this->db->where('sale_id', $saleId);
        $query = $this->db->get();
        $result  = $query->result_array();
        $payload = $this->create_invoice_b2b($sale, $result);
        return $payload;
    }

    public function get_zetca_return_b2b($retrunId){
        
        $this->db->select("id as invoiceNo, customer_id, date as issueDate,total_discount as documentDiscount,grand_total as grossAmount,order_discount_id, total_tax as taxAmount");
        $this->db->from("sma_returns");
        $this->db->where("id", $retrunId);
        $return = $this->db->get()->row();

        $this->db->select('product_name, quantity, unit_price, totalbeforevat, tax, item_discount,main_net, tr.rate');
        $this->db->from('sma_return_items');
        $this->db->join('sma_tax_rates as tr', 'tr.id = tax_rate_id');
        $this->db->where('return_id', $retrunId);
        $query = $this->db->get();
        $result  = $query->result_array();
        $payload = $this->create_return_b2b($return, $result);
        return $payload;
    }

    public function get_zetca_return_b2c($retrunId){
        $this->db->select("id as invoiceNo, customer_id, date as issueDate,total_discount as documentDiscount,grand_total as grossAmount,order_discount_id, total_tax as taxAmount");
        $this->db->from("sma_returns");
        $this->db->where("id", $retrunId);
        $return = $this->db->get()->row();

        $this->db->select('product_name, quantity, unit_price, totalbeforevat, tax, item_discount,main_net, tr.rate');
        $this->db->from('sma_return_items');
        $this->db->join('sma_tax_rates as tr', 'tr.id = tax_rate_id');
        $this->db->where('return_id', $retrunId);
        $query = $this->db->get();
        $result  = $query->result_array();
        $payload = $this->create_return_b2c($return, $result);
        return $payload;
    }

    public function get_zatca_data($saleId){

        $this->db->select("id as invoiceNo,  date as issueDate,total_discount as documentDiscount,grand_total as grossAmount,order_discount_id, total_tax as taxAmount");
        $this->db->from("sma_sales");
        $this->db->where("id", $saleId);
        $sale = $this->db->get()->row();
        $this->db->select('product_name, quantity, unit_price, totalbeforevat, tax, item_discount,main_net, tr.rate');
        $this->db->from('sma_sale_items');
        $this->db->join('sma_tax_rates as tr', 'tr.id = tax_rate_id');
        $this->db->where('sale_id', $saleId);
        $query = $this->db->get();
        $result  = $query->result_array();
        $payload = $this->create_simple_invoice_without_discount($sale, $result);
        return $payload;
    }

    public function report_failure($sale_id, $reason, $date, $payload){
        $json = json_encode($payload,true);
        $data = array(
            "sale_id" => $sale_id,
            "date" =>$date,
            "reason"  => $reason,
            "payload"  =>$json
        );
        $this->db->insert("zatca_failures", $data);

    }
    public function report_zatca_status($data){
        return $this->db->insert("sma_zatca_reporting_status", $data);
    }

}