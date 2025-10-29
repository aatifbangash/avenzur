/**
 * TailAdmin Sidebar Functions
 * Sidebar management, menu toggling, and navigation
 */

class SidebarManager {
	constructor() {
		this.sidebar = document.getElementById("sidebar-left");
		this.mobileMenuToggle = document.getElementById("mobileMenuToggle");
		this.init();
	}

	init() {
		this.initMobileToggle();
		this.initMenuToggle();
		this.initActiveMenu();
		this.handleWindowResize();
	}

	// ===== Mobile Menu Toggle =====
	initMobileToggle() {
		if (!this.mobileMenuToggle) return;

		this.mobileMenuToggle.addEventListener("click", (e) => {
			e.preventDefault();
			this.toggleMobileSidebar();
		});

		// Close sidebar when clicking outside
		document.addEventListener("click", (e) => {
			if (
				!this.sidebar.contains(e.target) &&
				!this.mobileMenuToggle.contains(e.target)
			) {
				this.closeMobileSidebar();
			}
		});
	}

	toggleMobileSidebar() {
		this.sidebar.classList.toggle("show");
		document.body.classList.toggle("sidebar-open");
	}

	closeMobileSidebar() {
		if (window.innerWidth < 992) {
			this.sidebar.classList.remove("show");
			document.body.classList.remove("sidebar-open");
		}
	}

	// ===== Menu Toggle (Expand/Collapse) =====
	initMenuToggle() {
		const dropmenuLinks = document.querySelectorAll(".sidebar-nav a.dropmenu");

		dropmenuLinks.forEach((link) => {
			link.addEventListener("click", (e) => {
				e.preventDefault();

				const submenu = link.nextElementSibling;
				if (submenu && submenu.tagName === "UL") {
					const isOpen = submenu.classList.contains("show");

					// Close all other submenus
					document.querySelectorAll(".sidebar-nav ul.show").forEach((menu) => {
						if (menu !== submenu) {
							menu.classList.remove("show");
							const arrow =
								menu.previousElementSibling.querySelector(".menu-arrow");
							if (arrow) arrow.classList.remove("menu-arrow-open");
						}
					});

					// Toggle current submenu
					submenu.classList.toggle("show");
					const arrow = link.querySelector(".menu-arrow");
					if (arrow) {
						arrow.classList.toggle("menu-arrow-open");
					}
				}
			});
		});
	}

	// ===== Active Menu Highlight =====
	initActiveMenu() {
		const currentUrl = window.location.href;
		const menuLinks = document.querySelectorAll(
			".sidebar-nav a:not(.dropmenu)"
		);

		menuLinks.forEach((link) => {
			if (link.href === currentUrl || link.href === currentUrl + "/") {
				// Mark as active
				link.classList.add("active");

				// Open parent menu if submenu is active
				const parentUl = link.closest("ul");
				if (parentUl && parentUl !== document.querySelector(".main-menu")) {
					parentUl.classList.add("show");
					const parentLink = parentUl.previousElementSibling;
					if (parentLink) {
						parentLink.classList.add("active");
						const arrow = parentLink.querySelector(".menu-arrow");
						if (arrow) arrow.classList.add("menu-arrow-open");
					}
				}
			}
		});
	}

	// ===== Window Resize Handler =====
	handleWindowResize() {
		let resizeTimer;
		window.addEventListener("resize", () => {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(() => {
				if (window.innerWidth >= 992) {
					this.closeMobileSidebar();
				}
			}, 250);
		});
	}

	// ===== Public Methods =====
	closeSidebar() {
		this.closeMobileSidebar();
	}

	openSidebar() {
		if (window.innerWidth < 992) {
			this.sidebar.classList.add("show");
			document.body.classList.add("sidebar-open");
		}
	}

	toggleMenu(menuElement) {
		const submenu = menuElement.nextElementSibling;
		if (submenu && submenu.tagName === "UL") {
			submenu.classList.toggle("show");
			const arrow = menuElement.querySelector(".menu-arrow");
			if (arrow) arrow.classList.toggle("menu-arrow-open");
		}
	}

	expandMenu(menuElement) {
		const submenu = menuElement.nextElementSibling;
		if (submenu && submenu.tagName === "UL") {
			submenu.classList.add("show");
			const arrow = menuElement.querySelector(".menu-arrow");
			if (arrow) arrow.classList.add("menu-arrow-open");
		}
	}

	collapseMenu(menuElement) {
		const submenu = menuElement.nextElementSibling;
		if (submenu && submenu.tagName === "UL") {
			submenu.classList.remove("show");
			const arrow = menuElement.querySelector(".menu-arrow");
			if (arrow) arrow.classList.remove("menu-arrow-open");
		}
	}
}

// Initialize on DOM ready
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", () => {
		window.sidebarManager = new SidebarManager();
	});
} else {
	window.sidebarManager = new SidebarManager();
}
