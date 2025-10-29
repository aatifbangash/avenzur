/**
 * TailAdmin Header Functions
 * Bootstrap-compatible JavaScript for header interactions
 */

class HeaderManager {
	constructor() {
		this.darkMode = localStorage.getItem("darkMode") === "true";
		this.init();
	}

	init() {
		this.initDarkMode();
		this.initDropdowns();
		this.initMobileMenu();
		this.initSearch();
		this.initClickOutside();
		this.initNotifications();
	}

	// ===== Dark Mode Toggle =====
	initDarkMode() {
		const darkModeToggle = document.getElementById("darkModeToggle");
		const html = document.documentElement;

		// Apply saved theme
		if (this.darkMode) {
			html.setAttribute("data-theme", "dark");
			this.updateDarkModeIcon(true);
		}

		// Toggle dark mode
		if (darkModeToggle) {
			darkModeToggle.addEventListener("click", () => {
				this.darkMode = !this.darkMode;
				localStorage.setItem("darkMode", this.darkMode);

				if (this.darkMode) {
					html.setAttribute("data-theme", "dark");
				} else {
					html.removeAttribute("data-theme");
				}

				this.updateDarkModeIcon(this.darkMode);
			});
		}
	}

	updateDarkModeIcon(isDark) {
		const moonIcon = document.getElementById("moonIcon");
		const sunIcon = document.getElementById("sunIcon");

		if (moonIcon && sunIcon) {
			if (isDark) {
				moonIcon.style.display = "none";
				sunIcon.style.display = "block";
			} else {
				moonIcon.style.display = "block";
				sunIcon.style.display = "none";
			}
		}
	}

	// ===== Dropdown Management =====
	initDropdowns() {
		const dropdownToggles = document.querySelectorAll("[data-dropdown-toggle]");

		dropdownToggles.forEach((toggle) => {
			toggle.addEventListener("click", (e) => {
				e.stopPropagation();
				const targetId = toggle.getAttribute("data-dropdown-toggle");
				const dropdown = document.getElementById(targetId);

				if (dropdown) {
					this.toggleDropdown(dropdown);
				}
			});
		});
	}

	toggleDropdown(dropdown) {
		const parentDropdown = dropdown.closest(".header-dropdown");
		const isOpen = parentDropdown.classList.contains("show");

		// Close all other dropdowns
		document.querySelectorAll(".header-dropdown.show").forEach((el) => {
			if (el !== parentDropdown) {
				el.classList.remove("show");
			}
		});

		// Toggle current dropdown
		if (isOpen) {
			parentDropdown.classList.remove("show");
		} else {
			parentDropdown.classList.add("show");
			dropdown.classList.add("animate-slide-down");
		}
	}

	// ===== Click Outside to Close =====
	initClickOutside() {
		document.addEventListener("click", (e) => {
			const isClickInside = e.target.closest(".header-dropdown");

			if (!isClickInside) {
				document.querySelectorAll(".header-dropdown.show").forEach((el) => {
					el.classList.remove("show");
				});
			}
		});
	}

	// ===== Mobile Menu Toggle =====
	initMobileMenu() {
		const mobileMenuToggle = document.getElementById("mobileMenuToggle");
		const sidebar = document.getElementById("sidebar");
		const body = document.body;

		if (mobileMenuToggle && sidebar) {
			mobileMenuToggle.addEventListener("click", () => {
				sidebar.classList.toggle("show");
				body.classList.toggle("sidebar-open");
			});
		}
	}

	// ===== Search Functionality =====
	initSearch() {
		const searchInput = document.getElementById("headerSearch");
		const mobileSearchToggle = document.getElementById("mobileSearchToggle");
		const mobileSearchContainer = document.getElementById(
			"mobileSearchContainer"
		);

		// Mobile search toggle
		if (mobileSearchToggle && mobileSearchContainer) {
			mobileSearchToggle.addEventListener("click", () => {
				mobileSearchContainer.classList.toggle("show");
				if (mobileSearchContainer.classList.contains("show")) {
					searchInput.focus();
				}
			});
		}

		// Search input handler
		if (searchInput) {
			let searchTimeout;
			searchInput.addEventListener("input", (e) => {
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(() => {
					this.performSearch(e.target.value);
				}, 300);
			});
		}
	}

	performSearch(query) {
		if (query.length < 2) return;

		// Add your search logic here
		console.log("Searching for:", query);

		// Example: Send AJAX request
		// fetch(`/api/search?q=${encodeURIComponent(query)}`)
		//   .then(response => response.json())
		//   .then(data => this.displaySearchResults(data));
	}

	// ===== Notifications =====
	initNotifications() {
		// Mark notification as read
		const notificationItems = document.querySelectorAll(
			"[data-notification-id]"
		);
		notificationItems.forEach((item) => {
			item.addEventListener("click", (e) => {
				const notificationId = item.getAttribute("data-notification-id");
				this.markNotificationAsRead(notificationId);
			});
		});
	}

	markNotificationAsRead(notificationId) {
		// Add your notification read logic here
		console.log("Marking notification as read:", notificationId);

		// Example: Send AJAX request
		// fetch(`/api/notifications/${notificationId}/read`, { method: 'POST' })
		//   .then(() => this.updateNotificationBadge());
	}

	updateNotificationBadge() {
		const badge = document.querySelector(".notification-badge");
		if (badge) {
			let count = parseInt(badge.textContent) - 1;
			if (count <= 0) {
				badge.style.display = "none";
			} else {
				badge.textContent = count;
			}
		}
	}
}

// Initialize on DOM ready
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", () => {
		new HeaderManager();
	});
} else {
	new HeaderManager();
}
