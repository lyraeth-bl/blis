// ── Dark mode ──
(function () {
    const root = document.documentElement;
    const btn  = document.getElementById('theme-toggle');

    btn.addEventListener('click', function () {
        const isDark = root.getAttribute('data-theme') === 'dark';
        if (isDark) {
            root.removeAttribute('data-theme');
            localStorage.setItem('blis-theme', 'light');
        } else {
            root.setAttribute('data-theme', 'dark');
            localStorage.setItem('blis-theme', 'dark');
        }
    });
})();

// ── Password toggle ──
function togglePassword(id, btn) {
    const el = document.getElementById('pwd-' + id);
    if (el.classList.contains('hidden')) {
        el.textContent = el.dataset.password;
        el.classList.remove('hidden');
        btn.textContent = 'Sembunyikan';
    } else {
        el.textContent = '••••••••••';
        el.classList.add('hidden');
        btn.textContent = 'Tampilkan';
    }
}

// ── Copy password ──
function copyPassword(id, btn) {
    const el = document.getElementById('pwd-' + id);
    navigator.clipboard.writeText(el.dataset.password).then(() => {
        btn.textContent = 'Tersalin!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.textContent = 'Salin';
            btn.classList.remove('copied');
        }, 2000);
    });
}

// ── Filter cards ──
function filterCards(query) {
    const q     = query.toLowerCase().trim();
    const cards = document.querySelectorAll('.wifi-card');
    let visible = 0;

    cards.forEach(card => {
        const match = !q || card.dataset.ssid.includes(q) || card.dataset.location.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) { visible++; }
    });

    let emptyEl = document.getElementById('empty-filter');
    if (visible === 0 && q) {
        if (!emptyEl) {
            emptyEl = document.createElement('div');
            emptyEl.id        = 'empty-filter';
            emptyEl.className = 'empty-state';
            emptyEl.style.gridColumn = '1 / -1';
            emptyEl.innerHTML = '<span>Tidak ada hasil untuk "<strong>' + query + '</strong>".</span>';
            document.getElementById('wifi-grid').appendChild(emptyEl);
        }
    } else if (emptyEl) {
        emptyEl.remove();
    }
}

document.getElementById('search')?.addEventListener('input', function () {
    filterCards(this.value);
});

document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-password-action]');

    if (!button) {
        return;
    }

    const wifiId = button.dataset.wifiId;

    if (button.dataset.passwordAction === 'toggle') {
        togglePassword(wifiId, button);
        return;
    }

    if (button.dataset.passwordAction === 'copy') {
        copyPassword(wifiId, button);
    }
});

// ── Unit filter partial navigation ──
(function () {
    const parser = new DOMParser();
    let requestController = null;

    function wifiGrid() {
        return document.getElementById('wifi-grid');
    }

    function updateActiveUnit(url) {
        const selectedUrl = new URL(url, window.location.origin);

        document.querySelectorAll('.unit-filter-link').forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);

            link.classList.toggle('active', linkUrl.search === selectedUrl.search);
        });
    }

    async function loadUnit(url, shouldPushState = true) {
        const currentGrid = wifiGrid();

        if (!currentGrid) {
            window.location.href = url;
            return;
        }

        requestController?.abort();
        requestController = new AbortController();
        currentGrid.classList.add('is-loading');

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
            });

            if (!response.ok) {
                throw new Error('Failed to load unit filter.');
            }

            const html = await response.text();
            const nextDocument = parser.parseFromString(html, 'text/html');
            const nextGrid = nextDocument.getElementById('wifi-grid');

            if (!nextGrid) {
                throw new Error('Missing WiFi grid.');
            }

            currentGrid.replaceWith(nextGrid);
            updateActiveUnit(url);
            filterCards(document.getElementById('search')?.value ?? '');

            if (shouldPushState) {
                history.pushState({}, '', url);
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            window.location.href = url;
        }
    }

    function bindUnitFilters() {
        document.querySelectorAll('.unit-filter-link').forEach(link => {
            link.addEventListener('click', function (event) {
                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
                    return;
                }

                event.preventDefault();
                loadUnit(this.href);
            });
        });
    }

    window.addEventListener('popstate', function () {
        loadUnit(window.location.href, false);
    });

    bindUnitFilters();
})();
