markdown# Wasfaty Mock Integration - CodeIgniter 3 Implementation Guide

## Project Overview

Implement a mock Wasfaty (government-backed prescription service) integration flow in the ERP POS system using CodeIgniter 3 framework. The system allows pharmacists to retrieve prescriptions via phone number and prescription code, then convert them to orders with automatic batch selection and GOLD customer discount application.

## Architecture Overview

### MVC Structure

```
application/
├── controllers/
│   ├── Pos.php (existing - to be modified)
│   └── Wasfaty.php (new)
├── models/
│   ├── Wasfaty_model.php (new)
│   ├── Batch_model.php (new/existing - to be modified)
│   └── Order_model.php (existing - to be modified)
├── views/
│   ├── pos/
│   │   ├── index.php (existing - to be modified)
│   │   └── wasfaty_tab.php (new)
│   └── wasfaty/
│       └── prescription_modal.php (new)
└── libraries/
    └── Wasfaty_service.php (new)

assets/
├── js/
│   └── wasfaty.js (new)
└── css/
    └── wasfaty.css (new)
```

## Implementation Steps

### Phase 1: Database Setup

#### 1.1 Create/Modify Tables

```sql
-- Add Wasfaty prescription tracking table
CREATE TABLE IF NOT EXISTS `wasfaty_prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_code` varchar(20) NOT NULL,
  `patient_phone` varchar(15) NOT NULL,
  `customer_type` enum('REGULAR','SILVER','GOLD','PLATINUM') DEFAULT 'REGULAR',
  `fetched_at` datetime DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('PENDING','DISPENSED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prescription_code` (`prescription_code`),
  KEY `patient_phone` (`patient_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add Wasfaty prescription items table
CREATE TABLE IF NOT EXISTS `wasfaty_prescription_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `duration_days` int(11) NOT NULL,
  `total_quantity` int(11) GENERATED ALWAYS AS (`quantity` * `duration_days`) STORED,
  PRIMARY KEY (`id`),
  KEY `prescription_id` (`prescription_id`),
  KEY `medicine_id` (`medicine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Modify orders table to track Wasfaty source
ALTER TABLE `orders`
ADD COLUMN `source` enum('MANUAL','WASFATY','ONLINE','MOBILE') DEFAULT 'MANUAL' AFTER `id`,
ADD COLUMN `prescription_code` varchar(20) DEFAULT NULL AFTER `source`,
ADD COLUMN `customer_type` enum('REGULAR','SILVER','GOLD','PLATINUM') DEFAULT 'REGULAR' AFTER `customer_id`;

-- Ensure batch tracking exists
CREATE TABLE IF NOT EXISTS `inventory_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medicine_id` int(11) NOT NULL,
  `batch_number` varchar(50) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `expiry_date` date NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `medicine_id` (`medicine_id`),
  KEY `expiry_date` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert mock data for testing
INSERT INTO `wasfaty_prescriptions`
(`prescription_code`, `patient_phone`, `customer_type`, `status`)
VALUES
('190583', '0554712260', 'GOLD', 'PENDING');

-- Get the last inserted ID and use it for prescription items
SET @prescription_id = LAST_INSERT_ID();

INSERT INTO `wasfaty_prescription_items`
(`prescription_id`, `medicine_id`, `medicine_name`, `quantity`, `dosage`, `duration_days`)
VALUES
(@prescription_id, 1, 'EXYLIN 100ML SYRUP', 3, '5ml twice daily', 5),
(@prescription_id, 2, 'Panadol Cold Flu 24Cap (Green)', 3, '1 tablet three times daily', 5);

-- Insert sample batch data for testing
INSERT INTO `inventory_batches`
(`medicine_id`, `batch_number`, `quantity`, `expiry_date`, `selling_price`)
VALUES
(1, 'BATCH-EXY-001', 50, '2026-03-15', 25.50),
(1, 'BATCH-EXY-002', 30, '2025-12-20', 25.50),
(2, 'BATCH-PAN-001', 100, '2026-06-30', 18.75),
(2, 'BATCH-PAN-002', 75, '2025-11-10', 18.75);
```

### Phase 2: Backend Implementation

#### 2.1 Create Wasfaty Controller

**File:** `application/controllers/Wasfaty.php`

```php
load->model('Wasfaty_model');
        $this->load->model('Batch_model');
        $this->load->library('Wasfaty_service');

        // Check if user is logged in and has permission
        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
    }

    /**
     * Fetch prescription by phone and code
     * AJAX endpoint
     */
    public function fetch_prescription() {
        // Simulate 1 second delay
        sleep(1);

        $phone = $this->input->post('phone');
        $code = $this->input->post('prescription_code');

        // Validation
        if (!$this->_validate_phone($phone)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid phone number format. Must be Saudi format (05XXXXXXXX)'
            ]);
            return;
        }

        if (!$this->_validate_prescription_code($code)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid prescription code format'
            ]);
            return;
        }

        // Fetch prescription
        $prescription = $this->Wasfaty_model->get_prescription($phone, $code);

        if (!$prescription) {
            echo json_encode([
                'success' => false,
                'message' => 'Prescription not found or already dispensed'
            ]);
            return;
        }

        // Get prescription items
        $items = $this->Wasfaty_model->get_prescription_items($prescription->id);

        // Check stock availability for each item
        $stock_check = $this->_check_stock_availability($items);

        if (!$stock_check['available']) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient stock for: ' . implode(', ', $stock_check['unavailable_items'])
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'prescription' => $prescription,
            'items' => $items,
            'customer_type' => $prescription->customer_type
        ]);
    }

    /**
     * Convert prescription to order
     * AJAX endpoint
     */
    public function convert_to_order() {
        $prescription_code = $this->input->post('prescription_code');

        if (!$prescription_code) {
            echo json_encode(['success' => false, 'message' => 'Prescription code required']);
            return;
        }

        $prescription = $this->Wasfaty_model->get_prescription_by_code($prescription_code);

        if (!$prescription) {
            echo json_encode(['success' => false, 'message' => 'Prescription not found']);
            return;
        }

        $items = $this->Wasfaty_model->get_prescription_items($prescription->id);

        // Prepare cart items with batch selection
        $cart_items = [];
        foreach ($items as $item) {
            $total_quantity = $item->quantity * $item->duration_days;

            // Select batch with earliest expiry
            $batch = $this->Batch_model->get_earliest_expiry_batch(
                $item->medicine_id,
                $total_quantity
            );

            if (!$batch) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Insufficient stock for ' . $item->medicine_name
                ]);
                return;
            }

            $cart_items[] = [
                'medicine_id' => $item->medicine_id,
                'medicine_name' => $item->medicine_name,
                'quantity' => $total_quantity,
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'price' => $batch->selling_price,
                'expiry_date' => $batch->expiry_date
            ];
        }

        // Calculate discount based on customer type
        $discount_percentage = $this->_get_discount_percentage($prescription->customer_type);

        echo json_encode([
            'success' => true,
            'cart_items' => $cart_items,
            'customer_type' => $prescription->customer_type,
            'discount_percentage' => $discount_percentage,
            'prescription_id' => $prescription->id,
            'prescription_code' => $prescription_code
        ]);
    }

    /**
     * Validate Saudi phone number format
     */
    private function _validate_phone($phone) {
        return preg_match('/^05\d{8}$/', $phone);
    }

    /**
     * Validate prescription code format
     */
    private function _validate_prescription_code($code) {
        return preg_match('/^\d{6}$/', $code);
    }

    /**
     * Check stock availability for all items
     */
    private function _check_stock_availability($items) {
        $unavailable = [];

        foreach ($items as $item) {
            $total_quantity = $item->quantity * $item->duration_days;
            $batch = $this->Batch_model->get_earliest_expiry_batch(
                $item->medicine_id,
                $total_quantity
            );

            if (!$batch) {
                $unavailable[] = $item->medicine_name;
            }
        }

        return [
            'available' => empty($unavailable),
            'unavailable_items' => $unavailable
        ];
    }

    /**
     * Get discount percentage based on customer type
     */
    private function _get_discount_percentage($customer_type) {
        $discounts = [
            'REGULAR' => 0,
            'SILVER' => 5,
            'GOLD' => 15,
            'PLATINUM' => 20
        ];

        return isset($discounts[$customer_type]) ? $discounts[$customer_type] : 0;
    }
}
```

#### 2.2 Create Wasfaty Model

**File:** `application/models/Wasfaty_model.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wasfaty_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get prescription by phone and code
     */
    public function get_prescription($phone, $code) {
        return $this->db
            ->where('patient_phone', $phone)
            ->where('prescription_code', $code)
            ->where('status', 'PENDING')
            ->get('wasfaty_prescriptions')
            ->row();
    }

    /**
     * Get prescription by code only
     */
    public function get_prescription_by_code($code) {
        return $this->db
            ->where('prescription_code', $code)
            ->where('status', 'PENDING')
            ->get('wasfaty_prescriptions')
            ->row();
    }

    /**
     * Get prescription items
     */
    public function get_prescription_items($prescription_id) {
        return $this->db
            ->where('prescription_id', $prescription_id)
            ->get('wasfaty_prescription_items')
            ->result();
    }

    /**
     * Update prescription status
     */
    public function update_prescription_status($prescription_id, $status, $order_id = null) {
        $data = [
            'status' => $status,
            'fetched_at' => date('Y-m-d H:i:s')
        ];

        if ($order_id) {
            $data['order_id'] = $order_id;
        }

        return $this->db
            ->where('id', $prescription_id)
            ->update('wasfaty_prescriptions', $data);
    }
}
```

#### 2.3 Create/Update Batch Model

**File:** `application/models/Batch_model.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Batch_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get batch with earliest expiry date that has sufficient quantity
     *
     * @param int $medicine_id
     * @param int $required_quantity
     * @return object|null
     */
    public function get_earliest_expiry_batch($medicine_id, $required_quantity) {
        $batch = $this->db
            ->where('medicine_id', $medicine_id)
            ->where('quantity >=', $required_quantity)
            ->where('expiry_date >', date('Y-m-d')) // Not expired
            ->order_by('expiry_date', 'ASC')
            ->limit(1)
            ->get('inventory_batches')
            ->row();

        return $batch;
    }

    /**
     * Get all available batches for a medicine (for multi-batch selection)
     *
     * @param int $medicine_id
     * @return array
     */
    public function get_available_batches($medicine_id) {
        return $this->db
            ->where('medicine_id', $medicine_id)
            ->where('quantity >', 0)
            ->where('expiry_date >', date('Y-m-d'))
            ->order_by('expiry_date', 'ASC')
            ->get('inventory_batches')
            ->result();
    }

    /**
     * Reduce batch quantity after order
     *
     * @param int $batch_id
     * @param float $quantity
     * @return bool
     */
    public function reduce_batch_quantity($batch_id, $quantity) {
        $this->db->set('quantity', 'quantity - ' . $quantity, FALSE);
        $this->db->where('id', $batch_id);
        return $this->db->update('inventory_batches');
    }

    /**
     * Get batch by ID
     */
    public function get_batch($batch_id) {
        return $this->db
            ->where('id', $batch_id)
            ->get('inventory_batches')
            ->row();
    }
}
```

#### 2.4 Update Order Model

**File:** `application/models/Order_model.php` (add these methods)

```php
<?php
// Add these methods to existing Order_model

/**
 * Create order from Wasfaty prescription
 *
 * @param array $order_data
 * @param array $items
 * @param int $prescription_id
 * @return int|bool Order ID or false
 */
public function create_wasfaty_order($order_data, $items, $prescription_id) {
    $this->db->trans_start();

    // Create order
    $order_insert = [
        'source' => 'WASFATY',
        'prescription_code' => $order_data['prescription_code'],
        'customer_id' => $order_data['customer_id'] ?? null,
        'customer_type' => $order_data['customer_type'],
        'subtotal' => $order_data['subtotal'],
        'discount' => $order_data['discount'],
        'discount_percentage' => $order_data['discount_percentage'],
        'total' => $order_data['total'],
        'payment_method' => $order_data['payment_method'] ?? 'CASH',
        'status' => 'COMPLETED',
        'created_by' => $this->session->userdata('user_id'),
        'created_at' => date('Y-m-d H:i:s')
    ];

    $this->db->insert('orders', $order_insert);
    $order_id = $this->db->insert_id();

    // Insert order items
    foreach ($items as $item) {
        $order_item = [
            'order_id' => $order_id,
            'medicine_id' => $item['medicine_id'],
            'medicine_name' => $item['medicine_name'],
            'batch_id' => $item['batch_id'],
            'batch_number' => $item['batch_number'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'subtotal' => $item['quantity'] * $item['price']
        ];

        $this->db->insert('order_items', $order_item);

        // Reduce batch quantity
        $this->load->model('Batch_model');
        $this->Batch_model->reduce_batch_quantity($item['batch_id'], $item['quantity']);
    }

    // Update prescription status
    $this->load->model('Wasfaty_model');
    $this->Wasfaty_model->update_prescription_status($prescription_id, 'DISPENSED', $order_id);

    $this->db->trans_complete();

    return $this->db->trans_status() ? $order_id : false;
}
```

### Phase 3: Frontend Implementation

#### 3.1 Modify POS View

**File:** `application/views/pos/index.php`

Add Wasfaty tab to existing POS tabs:

```php




            Manual Sale



                 Wasfaty













            load->view('pos/wasfaty_tab'); ?>










```

#### 3.2 Create Wasfaty Tab View

**File:** `application/views/pos/wasfaty_tab.php`

```php


         Wasfaty Prescription Lookup
        Enter patient phone number and prescription code to retrieve prescription details







                        Patient Phone Number *

                        Saudi mobile format (10 digits starting with 05)





                        Prescription Code *

                        6-digit prescription code




                    &nbsp;

                         Fetch







                Loading...

            Fetching prescription from Wasfaty...





```

#### 3.3 Create Prescription Modal View

**File:** `application/views/wasfaty/prescription_modal.php`

```php





                     Wasfaty Prescription Details


                    &times;








                            Phone Number:



                            Prescription Code:






                Prescribed Medications




                                Medicine Name
                                Quantity
                                Dosage
                                Duration
                                Total Qty










                    Note: Total quantity will be calculated as (Quantity × Duration Days)





                     Cancel


                     Convert to Order





```

#### 3.4 Create Wasfaty JavaScript

**File:** `assets/js/wasfaty.js`

```javascript
/**
 * Wasfaty Integration - JavaScript Controller
 * Handles prescription lookup, modal display, and order conversion
 */

var WasfatyModule = (function () {
	"use strict";

	var currentPrescription = null;
	var currentItems = null;

	/**
	 * Initialize module
	 */
	function init() {
		bindEvents();
	}

	/**
	 * Bind event listeners
	 */
	function bindEvents() {
		// Form submission
		$("#wasfaty-lookup-form").on("submit", function (e) {
			e.preventDefault();
			fetchPrescription();
		});

		// Convert to order button
		$(document).on("click", "#convert-to-order-btn", function () {
			convertToOrder();
		});

		// Phone number formatting
		$("#patient-phone").on("input", function () {
			this.value = this.value.replace(/[^\d]/g, "");
		});

		// Prescription code formatting
		$("#prescription-code").on("input", function () {
			this.value = this.value.replace(/[^\d]/g, "");
		});
	}

	/**
	 * Fetch prescription from backend
	 */
	function fetchPrescription() {
		var phone = $("#patient-phone").val().trim();
		var code = $("#prescription-code").val().trim();

		// Validation
		if (!validatePhone(phone)) {
			showError("Invalid phone number. Must be 10 digits starting with 05");
			return;
		}

		if (!validatePrescriptionCode(code)) {
			showError("Invalid prescription code. Must be 6 digits");
			return;
		}

		// Show loading
		showLoading();
		hideError();

		// AJAX request
		$.ajax({
			url: BASE_URL + "wasfaty/fetch_prescription",
			type: "POST",
			dataType: "json",
			data: {
				phone: phone,
				prescription_code: code,
			},
			success: function (response) {
				hideLoading();

				if (response.success) {
					currentPrescription = response.prescription;
					currentItems = response.items;
					showPrescriptionModal(response);
				} else {
					showError(response.message || "Failed to fetch prescription");
				}
			},
			error: function (xhr, status, error) {
				hideLoading();
				showError("Network error. Please try again.");
				console.error("Error:", error);
			},
		});
	}

	/**
	 * Display prescription modal
	 */
	function showPrescriptionModal(data) {
		// Populate modal data
		$("#modal-phone").text(data.prescription.patient_phone);
		$("#modal-prescription-code").text(data.prescription.prescription_code);

		// Populate medications table
		var tbody = $("#modal-medications-list");
		tbody.empty();

		$.each(data.items, function (index, item) {
			var totalQty = item.quantity * item.duration_days;
			var row =
				"" +
				"" +
				item.medicine_name +
				"" +
				"" +
				item.quantity +
				"" +
				"" +
				(item.dosage || "As directed") +
				"" +
				"" +
				item.duration_days +
				" days" +
				"" +
				totalQty +
				"" +
				"";
			tbody.append(row);
		});

		// Show modal
		$("#prescription-modal").modal("show");
	}

	/**
	 * Convert prescription to order
	 */
	function convertToOrder() {
		if (!currentPrescription) {
			showError("No prescription data available");
			return;
		}

		// Disable button
		$("#convert-to-order-btn").prop("disabled", true).html(" Processing...");

		$.ajax({
			url: BASE_URL + "wasfaty/convert_to_order",
			type: "POST",
			dataType: "json",
			data: {
				prescription_code: currentPrescription.prescription_code,
			},
			success: function (response) {
				if (response.success) {
					// Close modal
					$("#prescription-modal").modal("hide");

					// Add items to POS cart
					addItemsToCart(response);

					// Show success message
					showSuccessToast("Prescription items added to cart");

					// Reset form
					$("#wasfaty-lookup-form")[0].reset();
					currentPrescription = null;
					currentItems = null;
				} else {
					showError(response.message || "Failed to convert prescription");
				}

				$("#convert-to-order-btn")
					.prop("disabled", false)
					.html(" Convert to Order");
			},
			error: function () {
				showError("Network error. Please try again.");
				$("#convert-to-order-btn")
					.prop("disabled", false)
					.html(" Convert to Order");
			},
		});
	}

	/**
	 * Add items to POS cart
	 */
	function addItemsToCart(data) {
		// Clear existing cart or add to existing based on your POS logic
		var cartContainer = $("#pos-cart-items"); // Adjust selector based on your POS structure

		$.each(data.cart_items, function (index, item) {
			var cartItem = {
				medicine_id: item.medicine_id,
				medicine_name: item.medicine_name,
				quantity: item.quantity,
				price: item.price,
				batch_id: item.batch_id,
				batch_number: item.batch_number,
				expiry_date: item.expiry_date,
			};

			// Call your existing POS function to add item
			// This depends on your existing POS implementation
			if (typeof addToCart === "function") {
				addToCart(cartItem);
			}
		});

		// Set customer type
		setCustomerType(data.customer_type);

		// Apply discount
		applyDiscount(data.discount_percentage, data.customer_type);

		// Store prescription metadata for order submission
		$("#pos-form").data("prescription-code", data.prescription_code);
		$("#pos-form").data("source", "WASFATY");
	}

	/**
	 * Set customer type and display badge
	 */
	function setCustomerType(customerType) {
		var badgeClass = "badge-secondary";

		switch (customerType) {
			case "GOLD":
				badgeClass = "badge-warning";
				break;
			case "PLATINUM":
				badgeClass = "badge-dark";
				break;
			case "SILVER":
				badgeClass = "badge-secondary";
				break;
		}

		var badge = "" + customerType + "";

		// Add badge to customer info section
		$("#customer-info-container").append(badge);

		// Or update existing customer type display
		$("#customer-type").html(badge);
	}

	/**
	 * Apply discount to cart
	 */
	function applyDiscount(percentage, customerType) {
		// Update discount field in your POS
		$("#discount-percentage").val(percentage);

		// Trigger calculation
		if (typeof calculateTotal === "function") {
			calculateTotal();
		}

		// Show discount info
		var discountInfo =
			"" +
			" " +
			"" +
			customerType +
			" Customer Discount Applied: " +
			percentage +
			"%" +
			"";

		$("#discount-info-container").html(discountInfo);
	}

	/**
	 * Validation functions
	 */
	function validatePhone(phone) {
		return /^05\d{8}$/.test(phone);
	}

	function validatePrescriptionCode(code) {
		return /^\d{6}$/.test(code);
	}

	/**
	 * UI Helper functions
	 */
	function showLoading() {
		$("#wasfaty-loading").show();
		$("#fetch-prescription-btn").prop("disabled", true);
	}

	function hideLoading() {
		$("#wasfaty-loading").hide();
		$("#fetch-prescription-btn").prop("disabled", false);
	}

	function showError(message) {
		$("#wasfaty-error").text(message).show();
	}

	function hideError() {
		$("#wasfaty-error").hide();
	}

	function showSuccessToast(message) {
		// Use your existing toast/notification system
		if (typeof toastr !== "undefined") {
			toastr.success(message);
		} else {
			alert(message);
		}
	}

	// Public API
	return {
		init: init,
	};
})();

// Initialize on document ready
$(document).ready(function () {
	WasfatyModule.init();
});
```

#### 3.5 Create Wasfaty CSS

**File:** `assets/css/wasfaty.css`

```css
/**
 * Wasfaty Module Styles - Blue Theme
 */

/* Container */
.wasfaty-container {
	padding: 30px;
	background: #ffffff;
	border-radius: 8px;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Header */
.wasfaty-header {
	margin-bottom: 30px;
	padding-bottom: 20px;
	border-bottom: 2px solid #007bff;
}

.wasfaty-header h4 {
	color: #007bff;
	font-weight: 600;
	margin-bottom: 10px;
}

.wasfaty-header i {
	margin-right: 10px;
}

/* Form Container */
.wasfaty-form-container {
	background: #f8f9fa;
	padding: 25px;
	border-radius: 6px;
}

.wasfaty-form-container .form-control-lg {
	height: 50px;
	font-size: 16px;
}

.wasfaty-form-container label {
	font-weight: 600;
	color: #495057;
	margin-bottom: 8px;
}

.wasfaty-form-container .btn-primary {
	background: #007bff;
	border-color: #007bff;
	height: 50px;
	font-weight: 600;
}

.wasfaty-form-container .btn-primary:hover {
	background: #0056b3;
	border-color: #0056b3;
}

/* Loading State */
#wasfaty-loading {
	padding: 40px;
}

#wasfaty-loading .spinner-border {
	width: 3rem;
	height: 3rem;
}

/* Modal Styles */
#prescription-modal .modal-header {
	background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

#prescription-modal .modal-title {
	font-weight: 600;
}

#prescription-modal .prescription-info {
	background: #e7f3ff;
	padding: 15px;
	border-radius: 6px;
	border-left: 4px solid #007bff;
}

#prescription-modal .prescription-info p {
	margin-bottom: 5px;
}

#prescription-modal .prescription-info strong {
	color: #007bff;
}

#prescription-modal .table thead {
	background: #007bff;
	color: white;
}

#prescription-modal .table thead th {
	border: none;
	font-weight: 600;
	padding: 12px;
}

#prescription-modal .table tbody td {
	vertical-align: middle;
	padding: 12px;
}

#prescription-modal .table tbody tr:hover {
	background-color: #f1f8ff;
}

#prescription-modal .alert-info {
	background-color: #e7f3ff;
	border-color: #007bff;
	color: #004085;
}

#prescription-modal #convert-to-order-btn {
	min-width: 180px;
}

/* Customer Type Badge */
.badge-lg {
	padding: 8px 16px;
	font-size: 14px;
	font-weight: 600;
	border-radius: 20px;
}

.badge-warning {
	background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
	color: #000;
	box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
}

/* Cart Discount Info */
#discount-info-container .alert-success {
	background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
	border: none;
	color: white;
	font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
	.wasfaty-container {
		padding: 15px;
	}

	.wasfaty-form-container {
		padding: 15px;
	}

	#prescription-modal .modal-dialog {
		margin: 10px;
	}
}

/* Animation */
@keyframes fadeIn {
	from {
		opacity: 0;
		transform: translateY(-10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.wasfaty-container {
	animation: fadeIn 0.3s ease-in-out;
}

/* Error Alert */
#wasfaty-error {
	margin-top: 20px;
	animation: fadeIn 0.3s ease-in-out;
}
```

### Phase 4: POS Integration

#### 4.1 Modify POS Order Submission

Update your existing POS order submission to handle Wasfaty orders:

```javascript
// In your existing POS JavaScript file

function submitOrder() {
	var orderData = {
		// ... existing order data
	};

	// Check if this is a Wasfaty order
	var prescriptionCode = $("#pos-form").data("prescription-code");
	var source = $("#pos-form").data("source");

	if (source === "WASFATY" && prescriptionCode) {
		orderData.source = "WASFATY";
		orderData.prescription_code = prescriptionCode;
	}

	// Submit order via AJAX
	$.ajax({
		url: BASE_URL + "pos/create_order",
		type: "POST",
		data: orderData,
		success: function (response) {
			// Handle success
		},
	});
}
```

### Phase 5: Testing Checklist

```markdown
## Testing Steps

### 1. Database Setup

- [ ] All tables created successfully
- [ ] Mock data inserted
- [ ] Foreign keys working
- [ ] Indexes created

### 2. Backend Validation

- [ ] Phone number validation (05XXXXXXXX)
- [ ] Prescription code validation (6 digits)
- [ ] Prescription fetch returns correct data
- [ ] Batch selection picks earliest expiry
- [ ] Stock availability check works
- [ ] Order creation with Wasfaty source
- [ ] Prescription status updates to DISPENSED

### 3. Frontend Functionality

- [ ] Wasfaty tab displays correctly
- [ ] Form validation working
- [ ] 1-second delay implemented
- [ ] Loading spinner shows during fetch
- [ ] Modal displays prescription correctly
- [ ] Modal shows both medications
- [ ] Quantity calculation (3 × 5 = 15) correct
- [ ] Convert to Order button works
- [ ] Modal closes automatically

### 4. Cart Integration

- [ ] Items added to cart with correct quantity
- [ ] GOLD badge displayed prominently
- [ ] Discount (15%) applied automatically
- [ ] Subtotal, discount, and total calculated
- [ ] Batch information visible in cart
- [ ] Prescription code stored with order

### 5. Order Submission

- [ ] Order created successfully
- [ ] Order source set to WASFATY
- [ ] Prescription code stored
- [ ] Customer type stored
- [ ] Batch quantities reduced
- [ ] Prescription status updated
- [ ] Order receipt shows Wasfaty info

### 6. Error Handling

- [ ] Invalid phone format error
- [ ] Invalid prescription code error
- [ ] Prescription not found error
- [ ] Insufficient stock error
- [ ] Network error handling
- [ ] Duplicate order prevention

### 7. UI/UX

- [ ] Blue theme consistency
- [ ] Responsive design
- [ ] Smooth animations
- [ ] Loading states clear
- [ ] Success messages shown
- [ ] Error messages user-friendly

### 8. Edge Cases

- [ ] Multiple batches scenario
- [ ] No batch available scenario
- [ ] Already dispensed prescription
- [ ] Expired batch exclusion
- [ ] Concurrent order prevention
```

## Configuration

### Required Constants

Add to `application/config/constants.php`:

```php
// Wasfaty Configuration
defined('WASFATY_ENABLED') OR define('WASFATY_ENABLED', TRUE);
defined('WASFATY_MOCK_MODE') OR define('WASFATY_MOCK_MODE', TRUE);

// Customer Type Discounts
defined('CUSTOMER_DISCOUNT_REGULAR') OR define('CUSTOMER_DISCOUNT_REGULAR', 0);
defined('CUSTOMER_DISCOUNT_SILVER') OR define('CUSTOMER_DISCOUNT_SILVER', 5);
defined('CUSTOMER_DISCOUNT_GOLD') OR define('CUSTOMER_DISCOUNT_GOLD', 15);
defined('CUSTOMER_DISCOUNT_PLATINUM') OR define('CUSTOMER_DISCOUNT_PLATINUM', 20);
```

## Deployment Steps

1. **Backup Database**

```bash
   mysqldump -u username -p database_name > backup.sql
```

2. **Run Database Migrations**

```bash
   mysql -u username -p database_name < wasfaty_schema.sql
```

3. **Upload Files**

   - Controllers
   - Models
   - Views
   - Assets (JS/CSS)

4. **Clear Cache**

```bash
   rm -rf application/cache/*
```

5. **Test in Staging**

   - Run all test cases
   - Verify data integrity
   - Check permissions

6. **Deploy to Production**
   - Schedule maintenance window
   - Deploy code
   - Monitor logs
   - Verify functionality

## Future Enhancements

1. **Real API Integration**

   - Replace mock service with actual Wasfaty API
   - Implement authentication
   - Handle API rate limiting

2. **Advanced Features**

   - Prescription history
   - Patient management
   - Refill requests
   - Insurance integration

3. **Reporting**

   - Wasfaty sales reports
   - Customer type analytics
   - Prescription tracking

4. **Security**
   - Encryption for sensitive data
   - Audit logging
   - User permissions

## Support & Documentation

- Mock data provided for testing
- All validations implemented
- Error handling comprehensive
- Code comments included
- Blue theme maintained
