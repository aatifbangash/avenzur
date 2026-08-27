/**
 * Wasfaty Integration - JavaScript Controller
 * Two-column modal with prescription details on the right
 *
 * @package    Avenzur ERP
 * @subpackage Assets
 * @category   Wasfaty Integration
 * @author     Avenzur Development Team
 * @version    3.0
 */

console.log("=== WASFATY.JS FILE LOADED ===");

var WasfatyModule = (function ($) {
	"use strict";

	console.log("=== WasfatyModule function executing ===");

	var currentPrescription = null;
	var currentItems = null;
	var currentCartData = null;

	/**
	 * Initialize module
	 */
	function init() {
		console.log("Wasfaty module initializing...");
		bindEvents();
		console.log("Wasfaty module initialized successfully");
	}

	/**
	 * Bind event listeners
	 */
	function bindEvents() {
		console.log("=== bindEvents called ===");

		// Prevent modal from closing during operations
		$("#wasfatyModal").on("hide.bs.modal", function (e) {
			console.log("⚠️ Modal trying to close - checking if allowed");
			// Only allow closing if we're not processing
			if (window.wasfatyProcessing === true) {
				console.log("❌ BLOCKING modal close - operation in progress");
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
			console.log("✅ Allowing modal to close");
		});

		// Intercept Wasfaty button click to configure modal properly
		$(document).on("click", "#wasfaty-btn", function (e) {
			console.log("Wasfaty button clicked");
			e.preventDefault();
			e.stopPropagation();
			// Show modal with static backdrop
			$("#wasfatyModal").modal({
				backdrop: "static",
				keyboard: false,
			});
			return false;
		});

		// Form submission - PRIMARY handler
		$(document).on("submit", "#wasfaty-lookup-form", function (e) {
			console.log("🔵 FORM SUBMIT EVENT TRIGGERED");
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();

			console.log("Calling fetchPrescription from form submit");
			fetchPrescription();
			return false;
		});

		// Fetch button click - BACKUP handler
		$(document).on("click", "#fetch-prescription-btn", function (e) {
			console.log("🔵 BUTTON CLICK EVENT TRIGGERED");
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();

			console.log("Calling fetchPrescription from button click");
			fetchPrescription();
			return false;
		});

		// Convert to order button
		$(document).on("click", "#convert-to-order-btn", function (e) {
			e.preventDefault();
			e.stopPropagation();
			console.log("Convert to order clicked");
			convertToOrder();
			return false;
		});

		// Phone number formatting (digits only)
		$(document).on("input", "#patient-phone", function () {
			this.value = this.value.replace(/[^\d]/g, "");
		});

		// Prescription code formatting (digits only)
		$(document).on("input", "#prescription-code", function () {
			this.value = this.value.replace(/[^\d]/g, "");
		});

		// Reset on modal close - only when modal is actually hidden
		$(document).on("hidden.bs.modal", "#wasfatyModal", function () {
			console.log("Modal hidden - resetting form");
			resetForm();
		});
	}

	/**
	 * Fetch prescription from backend
	 */
	function fetchPrescription() {
		console.log("=== fetchPrescription called ===");

		// Set processing flag to prevent modal from closing
		window.wasfatyProcessing = true;

		// Check if site variable exists
		if (typeof site === "undefined" || !site.url) {
			console.error("❌ Site URL not defined!");
			showError("System error: Base URL not configured");
			window.wasfatyProcessing = false;
			return;
		}

		var patientPhone = $("#patient-phone").val().trim();
		var prescriptionCode = $("#prescription-code").val().trim();

		console.log("Phone:", patientPhone, "Code:", prescriptionCode);

		// Validation
		if (!validatePhone(patientPhone)) {
			console.log("Phone validation failed");
			showError("Invalid phone number. Must be 10 digits starting with 05");
			window.wasfatyProcessing = false;
			return;
		}

		if (!validatePrescriptionCode(prescriptionCode)) {
			console.log("Prescription code validation failed");
			showError("Invalid prescription code. Must be 6 digits");
			window.wasfatyProcessing = false;
			return;
		}

		console.log("Validation passed - showing loading");

		// Show loading in right column
		showLoading();
		hideError();

		console.log(
			"Sending AJAX request to:",
			site.url + "admin/wasfaty/fetch_prescription"
		);

		// Prepare data with CSRF token
		var requestData = {
			phone: patientPhone,
			prescription_code: prescriptionCode,
		};

		// Debug site object
		console.log("Site object:", site);
		console.log("site.csrf_token:", site.csrf_token);
		console.log("site.csrf_token_value:", site.csrf_token_value);

		// Add CSRF token
		if (typeof site !== "undefined" && site.csrf_token) {
			requestData[site.csrf_token] = site.csrf_token_value;
			console.log(
				"CSRF Token added:",
				site.csrf_token,
				"=",
				site.csrf_token_value
			);
		} else {
			console.warn(
				"⚠️ CSRF token NOT added - site.csrf_token is:",
				site.csrf_token
			);
		}

		console.log("Request data:", requestData);

		// AJAX request
		$.ajax({
			url: site.url + "admin/wasfaty/fetch_prescription",
			type: "POST",
			dataType: "json",
			data: requestData,
			success: function (response) {
				console.log("=== AJAX success ===", response);
				hideLoading();

				// Clear processing flag
				window.wasfatyProcessing = false;

				if (response.success) {
					console.log("Response successful - showing prescription details");
					currentPrescription = response.prescription;
					currentItems = response.items;
					showPrescriptionDetails(response);
					console.log(
						"Prescription details displayed - modal should stay open"
					);
				} else {
					console.log("Response failed:", response.message);
					showError(response.message || "Failed to fetch prescription");
					showEmptyState();
				}
			},
			error: function (xhr, status, error) {
				console.error("=== AJAX error ===", status, error);
				hideLoading();
				showError("Network error. Please try again.");
				showEmptyState();

				// Clear processing flag
				window.wasfatyProcessing = false;
			},
		});
	}

	/**
	 * Display prescription details in the right column
	 */
	function showPrescriptionDetails(data) {
		console.log("=== showPrescriptionDetails called ===");

		// Hide empty state, show details
		$("#prescription-empty-state").hide();

		console.log("Populating prescription info");

		// Populate prescription info
		$("#modal-phone").text(data.prescription.patient_phone);
		$("#modal-prescription-code").text(data.prescription.prescription_code);
		$("#modal-customer-type").text(data.prescription.customer_type);

		// Add discount text
		var discountPercent = getDiscountPercentage(
			data.prescription.customer_type
		);
		$("#modal-discount-text").html(
			'<i class="fa fa-tag"></i> ' + discountPercent + "% discount"
		);

		console.log(
			"Populating medications table with",
			data.items.length,
			"items"
		);

		// Populate medications table in modal
		var tbody = $("#modal-medications-list");
		tbody.empty();

		$.each(data.items, function (index, item) {
			var totalQty = item.quantity * item.duration_days;
			var row =
				"<tr>" +
				"<td><strong>" +
				item.medicine_name +
				"</strong></td>" +
				"<td class='text-center'>" +
				item.quantity +
				"</td>" +
				"<td>" +
				(item.dosage || "As directed") +
				"</td>" +
				"<td class='text-center'>" +
				item.duration_days +
				" days</td>" +
				"<td class='text-center'><strong style='color: #2196f3;'>" +
				totalQty +
				"</strong></td>" +
				"</tr>";
			tbody.append(row);
		});

		console.log("Populating POS item-list with prescription items");

		// Also populate the POS item-list (#item-list) on the left side
		var $itemList = $("#item-list");
		if ($itemList.length) {
			$itemList.empty(); // Clear existing items

			// Add header
			$itemList.append(
				'<div style="padding: 15px; background: #e3f2fd; border-bottom: 3px solid #2196F3;">' +
					'<h4 style="margin: 0; color: #2196F3;">' +
					'<i class="fa fa-heartbeat"></i> Wasfaty Prescription Items' +
					"</h4>" +
					'<p style="margin: 5px 0 0 0; color: #666;">Click any item to add to cart</p>' +
					"</div>"
			);

			// Create item cards for each medication
			$.each(data.items, function (index, item) {
				var totalQty = item.quantity * item.duration_days;
				var itemCard =
					'<div class="wasfaty-item-card" style="padding: 15px; border-bottom: 1px solid #e0e0e0; cursor: pointer; transition: background 0.2s;" ' +
					'data-medicine-id="' +
					item.medicine_id +
					'" ' +
					'data-medicine-name="' +
					item.medicine_name +
					'" ' +
					'data-quantity="' +
					totalQty +
					'">' +
					'<div style="margin-bottom: 8px;">' +
					'<strong style="font-size: 15px; color: #333;">' +
					item.medicine_name +
					"</strong>" +
					"</div>" +
					'<div style="color: #666; font-size: 13px;">' +
					'<span style="display: inline-block; margin-right: 15px;">' +
					'<i class="fa fa-prescription-bottle"></i> Qty: <strong style="color: #2196F3;">' +
					totalQty +
					"</strong>" +
					"</span>" +
					'<span style="display: inline-block; margin-right: 15px;">' +
					'<i class="fa fa-clock-o"></i> ' +
					item.duration_days +
					" days" +
					"</span>" +
					"</div>" +
					'<div style="margin-top: 8px; color: #888; font-size: 12px;">' +
					'<i class="fa fa-info-circle"></i> ' +
					(item.dosage || "As directed") +
					"</div>" +
					"</div>";

				$itemList.append(itemCard);
			});

			console.log("Added", data.items.length, "items to POS item-list");

			// Add hover effect
			$(document)
				.on("mouseenter", ".wasfaty-item-card", function () {
					$(this).css("background", "#f5f5f5");
				})
				.on("mouseleave", ".wasfaty-item-card", function () {
					$(this).css("background", "white");
				});

			// Add click handler to populate search input
			$(document).on("click", ".wasfaty-item-card", function () {
				var medicineName = $(this).data("medicine-name");
				var quantity = $(this).data("quantity");

				console.log("Wasfaty item clicked:", medicineName, "Qty:", quantity);

				// Populate the search input with medicine name
				var $addItemInput = $("#add_item");
				if ($addItemInput.length) {
					$addItemInput.val(medicineName).focus();
					console.log("Populated #add_item with:", medicineName);

					// Trigger search/autocomplete if available
					$addItemInput.trigger("input").trigger("keyup");
				} else {
					console.warn("Could not find #add_item input");
				}
			});
		} else {
			console.warn("Could not find #item-list element");
		}

		console.log("Showing prescription details");

		// Show prescription details with animation
		$("#prescription-details").fadeIn(400);

		// Show "Add to Cart" button
		$("#convert-to-order-btn").fadeIn(400);

		console.log(
			"=== showPrescriptionDetails complete - prescription should be visible ==="
		);
	}

	/**
	 * Convert prescription to order
	 * Uses existing POS functionality:
	 * 1. Select first pharmacy in dropdown
	 * 2. Search for each product by name
	 * 3. Auto-select earliest expiry batch
	 */
	function convertToOrder() {
		if (!currentPrescription || !currentItems) {
			showError("No prescription data available");
			return;
		}

		console.log("=== Converting prescription to order ===");
		console.log("Prescription:", currentPrescription);
		console.log("Items:", currentItems);

		// Disable button
		$("#convert-to-order-btn")
			.prop("disabled", true)
			.html('<i class="fa fa-spinner fa-spin"></i> Adding to Cart...');

		// Step 1: Select "Madina Pharmacy" in dropdown
		var $warehouseSelect = $("#poswarehouse");
		if ($warehouseSelect.length && $warehouseSelect.find("option").length > 1) {
			// Try to find "Madina Pharmacy" by text
			var madinaOption = $warehouseSelect
				.find("option")
				.filter(function () {
					return $(this).text().indexOf("Madina Pharmacy") !== -1;
				})
				.first();

			var selectedWarehouse;
			if (madinaOption.length && madinaOption.val()) {
				// Found Madina Pharmacy
				selectedWarehouse = madinaOption.val();
				console.log(
					'Selecting "Madina Pharmacy" (ID: ' + selectedWarehouse + ")"
				);
			} else {
				// Fallback to first option if Madina Pharmacy not found
				selectedWarehouse = $warehouseSelect
					.find("option:not(:first)")
					.first()
					.val();
				console.log(
					"Madina Pharmacy not found, selecting first warehouse (ID: " +
						selectedWarehouse +
						")"
				);
			}

			if (selectedWarehouse) {
				$warehouseSelect.val(selectedWarehouse).trigger("change");
			}
		}

		// Step 2: Add each item to cart sequentially
		var itemIndex = 0;

		function addNextItem() {
			if (itemIndex >= currentItems.length) {
				// All items added - show success and close modal
				console.log("=== All items added successfully ===");

				// Apply customer type discount
				applyCustomerDiscount(currentPrescription.customer_type);

				// Show success message
				bootbox.alert({
					message:
						'<div class="text-center">' +
						'<i class="fa fa-check-circle fa-3x text-success"></i><br><br>' +
						'<h4 style="margin-bottom: 15px;">Prescription Added Successfully!</h4>' +
						'<div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 15px 0;">' +
						'<p style="margin: 5px 0;"><strong>Customer Type:</strong> <span class="label label-warning label-lg">' +
						currentPrescription.customer_type +
						"</span></p>" +
						'<p style="margin: 5px 0; color: #28a745; font-size: 16px; font-weight: 600;"><i class="fa fa-tag"></i> Discount: ' +
						getDiscountPercentage(currentPrescription.customer_type) +
						"%</p>" +
						"</div>" +
						'<p style="color: #666;">' +
						currentItems.length +
						" items added to cart.</p>" +
						"</div>",
					className: "wasfaty-success-alert",
				});

				// Close modal
				setTimeout(function () {
					$("#wasfatyModal").modal("hide");
				}, 2000);

				return;
			}

			var item = currentItems[itemIndex];
			var searchTerm = item.medicine_name;
			var requiredQty = item.total_quantity;

			console.log(
				"Adding item " + (itemIndex + 1) + ":",
				searchTerm,
				"Qty:",
				requiredQty
			);

			// Prepare search data with CSRF token
			var searchData = {
				term: searchTerm,
				warehouse_id: $("#poswarehouse").val(),
				customer_id: $("#poscustomer").val() || null,
			};

			// Add CSRF token
			if (typeof site !== "undefined" && site.csrf_token) {
				searchData[site.csrf_token] = site.csrf_token_value;
			}

			// Use AJAX to search for product
			$.ajax({
				type: "GET",
				url: site.url + "admin/sales/suggestions/1",
				dataType: "json",
				data: searchData,
				success: function (data) {
					console.log("Search result for " + searchTerm + ":", data);

					if (data && data.length > 0 && data[0].id != 0) {
						var product = data[0];

						// Ensure all required fields exist with defaults
						if (!product.price || product.price === undefined) {
							product.price = 0;
						}
						if (
							!product.real_unit_price ||
							product.real_unit_price === undefined
						) {
							product.real_unit_price = product.price;
						}
						if (
							!product.base_unit_price ||
							product.base_unit_price === undefined
						) {
							product.base_unit_price = product.price;
						}
						if (!product.qty || product.qty === undefined) {
							product.qty = 1;
						}
						if (!product.discount || product.discount === undefined) {
							product.discount = "0";
						}
						if (!product.tax_rate || product.tax_rate === undefined) {
							product.tax_rate = 0;
						}

						// Check if add_invoice_item function exists (from POS)
						if (typeof add_invoice_item === "function") {
							console.log("Calling add_invoice_item for:", product.name);

							// Set the required quantity
							product.qty = requiredQty;
							product.base_quantity = requiredQty;

							// Add to cart using existing POS function
							add_invoice_item(product);

							// Move to next item after short delay
							setTimeout(function () {
								itemIndex++;
								addNextItem();
							}, 500);
						} else {
							console.error("add_invoice_item function not found");
							showError("POS cart function not available");
							$("#convert-to-order-btn")
								.prop("disabled", false)
								.html('<i class="fa fa-shopping-cart"></i> Add to Cart');
						}
					} else {
						console.error("Product not found:", searchTerm);
						showError("Product not found: " + searchTerm);
						$("#convert-to-order-btn")
							.prop("disabled", false)
							.html('<i class="fa fa-shopping-cart"></i> Add to Cart');
					}
				},
				error: function () {
					console.error("Error searching for product:", searchTerm);
					showError("Error searching for product: " + searchTerm);
					$("#convert-to-order-btn")
						.prop("disabled", false)
						.html('<i class="fa fa-shopping-cart"></i> Add to Cart');
				},
			});
		}

		// Start adding items
		addNextItem();
	}

	/**
	 * Apply customer discount
	 */
	function applyCustomerDiscount(customerType) {
		var percentage = getDiscountPercentage(customerType);

		console.log("Applying discount:", percentage + "%", "for", customerType);

		// Try to set discount in POS (this depends on your POS implementation)
		// You may need to adjust this based on how your POS handles discounts
		if ($("#order_discount").length) {
			$("#order_discount").val(percentage).trigger("change");
		} else if ($("#posdiscount").length) {
			$("#posdiscount").val(percentage).trigger("change");
		}

		// Show discount banner
		var discountBanner =
			'<div id="wasfaty-discount-banner" class="alert alert-success" style="margin: 10px 0; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; color: white;">' +
			'<i class="fa fa-tag"></i> <strong>' +
			customerType +
			" Customer Discount: " +
			percentage +
			"%</strong>" +
			"</div>";

		// Remove existing banner
		$("#wasfaty-discount-banner").remove();

		// Add to POS interface
		if ($("#leftdiv").length) {
			$("#leftdiv").prepend(discountBanner);
		}

		// Auto-remove after 10 seconds
		setTimeout(function () {
			$("#wasfaty-discount-banner").fadeOut(500, function () {
				$(this).remove();
			});
		}, 10000);
	}

	/**
	 * Get discount percentage by customer type
	 */
	function getDiscountPercentage(customerType) {
		var discounts = {
			REGULAR: 0,
			SILVER: 5,
			GOLD: 15,
			PLATINUM: 20,
		};

		return discounts[customerType] || 0;
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
		$("#prescription-empty-state").hide();
		$("#prescription-details").hide();
		$("#wasfaty-loading").fadeIn(200);
		$("#fetch-prescription-btn").prop("disabled", true);
	}

	function hideLoading() {
		$("#wasfaty-loading").fadeOut(200);
		$("#fetch-prescription-btn").prop("disabled", false);
	}

	function showError(message) {
		$("#wasfaty-error")
			.html('<i class="fa fa-exclamation-triangle"></i> ' + message)
			.fadeIn(300);

		// Auto-hide error after 5 seconds
		setTimeout(function () {
			$("#wasfaty-error").fadeOut(300);
		}, 5000);
	}

	function hideError() {
		$("#wasfaty-error").fadeOut(200);
	}

	function showEmptyState() {
		$("#prescription-details").hide();
		$("#prescription-empty-state").fadeIn(300);
	}

	function resetForm() {
		// Reset form
		$("#wasfaty-lookup-form")[0].reset();
		$("#prescription-details").hide();
		$("#wasfaty-error").hide();
		$("#wasfaty-loading").hide();
		$("#prescription-empty-state").fadeIn(200);
		$("#convert-to-order-btn").hide();

		// Reset data
		currentPrescription = null;
		currentItems = null;
		currentCartData = null;

		// Reset button
		$("#convert-to-order-btn")
			.prop("disabled", false)
			.html('<i class="fa fa-shopping-cart"></i> Add to Cart');
	}

	// Public API
	return {
		init: init,
	};
})(jQuery);

console.log("=== WasfatyModule defined, typeof:", typeof WasfatyModule, "===");

// Initialize on document ready
$(document).ready(function () {
	console.log("=== Document ready - checking for site variable ===");
	console.log("typeof site:", typeof site);

	if (typeof site !== "undefined") {
		console.log("Site defined, initializing Wasfaty module...");
		WasfatyModule.init();
	} else {
		console.error(
			"❌ Site base URL not defined. Wasfaty module not initialized."
		);
	}
});
