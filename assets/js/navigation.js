document.addEventListener('DOMContentLoaded', function () {
	const menuToggle = document.querySelector('.proof-menu-toggle');
	const nav = document.querySelector('.proof-primary-nav');
	const navSearchToggle = document.querySelector('.proof-nav-search-link');
	const searchPanel = document.querySelector('.proof-search-panel');
	let searchFocusTimeout;

	if (!menuToggle || !nav || !searchPanel) {
		return;
	}

	const setMenuState = function (isOpen) {
		nav.classList.toggle('is-open', isOpen);
		menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		menuToggle.textContent = isOpen ? proofLabels.menuClose : proofLabels.menuOpen;
	};

	const setSearchState = function (isOpen) {
		window.clearTimeout(searchFocusTimeout);
		searchPanel.hidden = !isOpen;
		searchPanel.classList.toggle('is-open', isOpen);
		if (navSearchToggle) {
			navSearchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		}
	};

	const closeMenu = function () {
		setMenuState(false);
	};

	const closeSearch = function () {
		setSearchState(false);
	};

	menuToggle.addEventListener('click', function () {
		const isOpening = !nav.classList.contains('is-open');
		if (isOpening) {
			closeSearch();
		}
		setMenuState(isOpening);
	});

	const toggleSearch = function () {
		const isOpening = searchPanel.hidden;
		if (isOpening) {
			closeMenu();
		}
		setSearchState(isOpening);
		if (isOpening) {
			const input = searchPanel.querySelector('.search-field');
			if (input) {
				searchFocusTimeout = window.setTimeout(function () {
					input.focus();
				}, 30);
			}
		}
	};

	if (navSearchToggle) {
		navSearchToggle.addEventListener('click', toggleSearch);
	}

	document.addEventListener('click', function (event) {
		if (nav.classList.contains('is-open')) {
			if (!nav.contains(event.target) && !menuToggle.contains(event.target)) {
				closeMenu();
			}
		}

		if (!searchPanel.hidden) {
			const clickedSearchToggle = navSearchToggle && navSearchToggle.contains(event.target);
			if (!searchPanel.contains(event.target) && !clickedSearchToggle) {
				closeSearch();
			}
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && (nav.classList.contains('is-open') || !searchPanel.hidden)) {
			const wasSearchOpen = !searchPanel.hidden;
			closeMenu();
			closeSearch();
			// The search trigger lives inside the closed menu. Prefer it only
			// when it remains visible; otherwise return to the menu control.
			const control = wasSearchOpen && navSearchToggle && navSearchToggle.getClientRects().length && window.getComputedStyle(navSearchToggle).visibility !== 'hidden' ? navSearchToggle : menuToggle;
			if (control.getClientRects().length && window.getComputedStyle(control).visibility !== 'hidden') {
				control.focus();
			}
			event.preventDefault();
		}
	});
});
