(function () {
	const root = document.documentElement;
	let visitorHasControl = false;
	let wasReady = false;
	let nativeSearch;

	// A late reload can also cancel an in-flight native link or a GET search.
	function claim(event) {
		if (!event.isTrusted || !event.target.closest || event.button > 0 || event.metaKey || event.ctrlKey || event.altKey || event.shiftKey) {
			return;
		}
		const control = event.target.closest('#masthead button, .proof-search-form, a[href]');
		if (!control || (control.matches('a') && (control.hasAttribute('download') || (control.target && control.target !== '_self')))) {
			return;
		}
		if (event.type !== 'focusin' || control.closest('#masthead, .proof-search-form')) {
			visitorHasControl = true;
		}
	}
	for (const type of ['pointerdown', 'focusin', 'click', 'submit']) {
		document.addEventListener(type, claim, true);
	}

	document.addEventListener('proof:guest-reload', function (event) {
		if (visitorHasControl || root.classList.contains('proof-guest-reloading')) {
			event.preventDefault();
			return;
		}
		// While an idle reload commits, only native navigation is actionable.
		wasReady = root.classList.contains('proof-nav-ready');
		root.classList.add('proof-guest-reloading');
		root.classList.remove('proof-nav-ready');
		const trigger = document.querySelector('.proof-nav-search-link');
		const form = document.querySelector('#proof-search-panel form');
		if (trigger && form) {
			const url = new URL(form.action);
			url.searchParams.set('s', '');
			nativeSearch = document.createElement('a');
			nativeSearch.href = url;
			nativeSearch.textContent = trigger.textContent.trim();
			trigger.before(nativeSearch);
		}
	});

	function resume() {
		if (root.classList.contains('proof-guest-reloading')) {
			root.classList.remove('proof-guest-reloading');
			root.classList.toggle('proof-nav-ready', wasReady);
			if (nativeSearch) {
				nativeSearch.remove();
			}
		}
	}
	window.addEventListener('pageshow', function (event) {
		if (event.persisted) {
			resume();
		}
	});
	if (window.navigation) {
		window.navigation.addEventListener('navigate', function (event) {
			if (event.navigationType === 'reload' && root.classList.contains('proof-guest-reloading')) {
				event.signal.addEventListener('abort', resume, { once: true });
			}
		});
	}
}());
