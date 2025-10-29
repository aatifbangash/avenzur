/**
 * Clean Sidebar Menu Toggle
 */

$(document).ready(function () {
	console.log("✅ Sidebar Menu Initialized");

	// Handle dropdown toggle
	$(".nav.main-menu").on("click", "a.dropmenu", function (e) {
		e.preventDefault();
		e.stopPropagation();

		const $link = $(this);
		const $li = $link.closest("li");
		const $submenu = $li.find("> ul").first();

		if ($submenu.length === 0) {
			return true; // Allow navigation if no submenu
		}

		// Close other menus at same level
		$li.siblings().each(function () {
			$(this).find("> ul").removeClass("show");
			$(this).find("> a").removeClass("menu-open");
		});

		// Toggle current menu
		$submenu.toggleClass("show");
		$link.toggleClass("menu-open");

		return false;
	});

	// Mobile menu toggle
	$("#mobileMenuToggle").on("click", function () {
		$(".sidebar-con").toggleClass("active");
		$("body").toggleClass("sidebar-open");
	});

	// Close mobile menu when clicking outside
	$(document).on("click", function (e) {
		if ($(window).width() <= 991) {
			if (!$(e.target).closest(".sidebar-con, #mobileMenuToggle").length) {
				$(".sidebar-con").removeClass("active");
				$("body").removeClass("sidebar-open");
			}
		}
	});

	console.log("🎯 Sidebar Ready!");
});
