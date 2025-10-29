/**
 * Modern Admin UI - JavaScript
 * Handles sidebar toggle, submenu expand/collapse, and mobile drawer
 * Light Theme with smooth animations
 */

(function ($) {
	"use strict";

	// Configuration
	const CONFIG = {
		sidebarSelector: ".sidebar-wrapper",
		toggleBtnSelector: ".sidebar-toggle-btn",
		submenuToggleSelector: ".sidebar-nav-toggle",
		backdropSelector: ".sidebar-backdrop",
		navItemSelector: ".sidebar-nav-item",
		navLinkSelector: ".sidebar-nav-link, .sidebar-nav-toggle",
		storageKey: "sidebarCollapsed",
		mobileBreakpoint: 768,
		debounceDelay: 150,
	};

	// State
	let isMobile = $(window).width() < CONFIG.mobileBreakpoint;
	let debounceTimer;

	/**
	 * Initialize the sidebar and event listeners
	 */
	function init() {
		// Convert old menu structure to new sidebar classes
		convertMenuStructure();

		// Restore sidebar state from localStorage
		restoreSidebarState();

		// Bind events
		bindEvents();

		// Highlight active menu item
		highlightActiveMenuItem();
	}

	/**
	 * Convert Bootstrap menu to modern sidebar structure
	 */
	function convertMenuStructure() {
		// Main menu list
		const $mainMenu = $("ul.main-menu");
		if ($mainMenu.length) {
			$mainMenu.addClass("sidebar-nav");

			// Convert menu items
			$mainMenu.find("> li").each(function () {
				const $li = $(this);
				$li.addClass("sidebar-nav-item");

				// Convert main links
				const $link = $li.find("> a");
				if ($link.length) {
					// Check if it has a submenu
					const $submenu = $li.find("> ul");
					if ($submenu.length) {
						// This is a parent with submenu
						$link.addClass("sidebar-nav-toggle");
						$link.attr("href", "#");
						$link.attr("data-toggle", "submenu");
						$link.attr("aria-expanded", "false");

						$submenu.addClass("sidebar-submenu");
					} else {
						// Regular link
						$link.addClass("sidebar-nav-link");
					}

					// Add classes to icon and text
					$link.find("i").addClass("sidebar-icon");
					$link.find("span.text").addClass("sidebar-label");

					// Handle chevron
					let $chevron = $link.find(".chevron");
					if (!$chevron.length) {
						$chevron = $('<i class="fa fa-chevron-right sidebar-chevron"></i>');
						$link.append($chevron);
					} else {
						$chevron.addClass("sidebar-chevron");
					}
				}

				// Convert submenu items
				$li.find("ul > li").each(function () {
					const $subli = $(this);
					$subli.addClass("sidebar-nav-item");

					const $sublink = $subli.find("> a");
					if ($sublink.length) {
						$sublink.addClass("sidebar-nav-link");
						$sublink.find("i").addClass("sidebar-icon");
						$sublink.find("span.text").addClass("sidebar-label");
					}
				});
			});
		}

		// Wrap menu in sidebar wrapper if not already wrapped
		if (!$mainMenu.parent().hasClass("sidebar-wrapper")) {
			$mainMenu.wrap('<aside class="sidebar-wrapper"></aside>');
		}

		// Create backdrop for mobile
		if (!$(CONFIG.backdropSelector).length) {
			$('<div class="sidebar-backdrop"></div>').insertAfter(
				CONFIG.sidebarSelector
			);
		}
	}

	/**
	 * Restore sidebar collapsed state from localStorage
	 * DEPRECATED: Sidebar collapse feature has been removed
	 */
	function restoreSidebarState() {
		// This function is kept for reference but is no longer called
		if (!isMobile) {
			const isCollapsed = localStorage.getItem(CONFIG.storageKey);
			if (isCollapsed === "true") {
				$(CONFIG.sidebarSelector).addClass("collapsed");
			}
		}
	}

	/**
	 * Remove sidebar collapsed state to ensure menu items are visible
	 * Called on init to clear any previously saved collapsed state
	 */
	function removeSidebarCollapsedState() {
		const $sidebar = $(CONFIG.sidebarSelector);
		$sidebar.removeClass("collapsed");
		localStorage.removeItem(CONFIG.storageKey);
	}

	/**
	 * Save sidebar state to localStorage
	 */
	function saveSidebarState(isCollapsed) {
		localStorage.setItem(CONFIG.storageKey, isCollapsed);
	}

	/**
	 * Toggle sidebar between expanded and minimized (desktop only)
	 */
	function toggleSidebar() {
		if (isMobile) {
			// Mobile: show/hide drawer
			toggleMobileDrawer();
		} else {
			// Desktop: collapse/expand
			const $sidebar = $(CONFIG.sidebarSelector);
			$sidebar.toggleClass("collapsed");
			saveSidebarState($sidebar.hasClass("collapsed"));
		}
	}

	/**
	 * Toggle mobile drawer
	 */
	function toggleMobileDrawer() {
		const $sidebar = $(CONFIG.sidebarSelector);
		const $backdrop = $(CONFIG.backdropSelector);

		$sidebar.toggleClass("show");
		$backdrop.toggleClass("show");
	}

	/**
	 * Close mobile drawer
	 */
	function closeMobileDrawer() {
		$(CONFIG.sidebarSelector).removeClass("show");
		$(CONFIG.backdropSelector).removeClass("show");
	}

	/**
	 * Toggle submenu visibility
	 */
	function toggleSubmenu(event) {
		event.preventDefault();

		const $toggle = $(event.currentTarget);
		const isExpanded = $toggle.attr("aria-expanded") === "true";

		$toggle.attr("aria-expanded", !isExpanded);

		// Close other submenus at same level
		$toggle
			.closest(".sidebar-nav-item")
			.siblings()
			.each(function () {
				const $sibling = $(this);
				const $siblingToggle = $sibling.find("> .sidebar-nav-toggle");
				if ($siblingToggle.length) {
					$siblingToggle.attr("aria-expanded", "false");
				}
			});
	}

	/**
	 * Highlight active menu item based on current URL
	 */
	function highlightActiveMenuItem() {
		const currentPath = window.location.pathname;

		$(CONFIG.navLinkSelector).each(function () {
			const $link = $(this);
			const href = $link.attr("href");

			// Check if link matches current path
			if (href && href !== "#" && currentPath.indexOf(href) !== -1) {
				// Mark this link as active
				$link.closest(CONFIG.navItemSelector).addClass("active");

				// Also mark parent items as active
				$link
					.closest(".sidebar-submenu")
					.prev(".sidebar-nav-toggle")
					.closest(CONFIG.navItemSelector)
					.addClass("active");

				// Expand parent submenu
				const $parentToggle = $link
					.closest(".sidebar-submenu")
					.prev(".sidebar-nav-toggle");
				if ($parentToggle.length) {
					$parentToggle.attr("aria-expanded", "true");
				}
			}
		});
	}

	/**
	 * Handle window resize
	 */
	function handleResize() {
		clearTimeout(debounceTimer);
		debounceTimer = setTimeout(function () {
			const newIsMobile = $(window).width() < CONFIG.mobileBreakpoint;

			if (newIsMobile !== isMobile) {
				isMobile = newIsMobile;

				if (isMobile) {
					// Switch to mobile mode
					$(CONFIG.sidebarSelector).removeClass("collapsed");
					closeMobileDrawer();
				} else {
					// Switch to desktop mode
					closeMobileDrawer();
					// Restore sidebar state when switching back to desktop
					restoreSidebarState();
				}
			}
		}, CONFIG.debounceDelay);
	}

	/**
	 * Bind event listeners
	 */
	function bindEvents() {
		// Sidebar toggle button
		$(document).on("click", CONFIG.toggleBtnSelector, function (e) {
			e.preventDefault();
			toggleSidebar();
		});

		// Submenu toggle
		$(document).on("click", CONFIG.submenuToggleSelector, toggleSubmenu);

		// Close drawer when clicking menu item (mobile only)
		$(document).on("click", CONFIG.navLinkSelector, function () {
			if (isMobile && !$(this).hasClass("sidebar-nav-toggle")) {
				closeMobileDrawer();
			}
		});

		// Close drawer when clicking backdrop
		$(document).on("click", CONFIG.backdropSelector, closeMobileDrawer);

		// Close drawer on ESC key
		$(document).on("keydown", function (e) {
			if (e.keyCode === 27) {
				// ESC
				if (isMobile) {
					closeMobileDrawer();
				}
			}
		});

		// Handle window resize
		$(window).on("resize", handleResize);
	}

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function () {
		init();
	});
})(jQuery);
