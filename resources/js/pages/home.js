// ── Dark mode ──
(function () {
    const root = document.documentElement;
    const btn = document.getElementById('theme-toggle');

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

// ── Search ──
(function () {
    const overlay   = document.getElementById('search-overlay');
    const modal     = document.getElementById('search-modal');
    const input     = document.getElementById('search-input');
    const results   = document.getElementById('search-results');
    const openBtn   = document.getElementById('search-open-btn');
    let activeIdx   = -1;

    function tintClass(idx) {
        return 'card-icon-tint-' + (idx % 5);
    }

    function globeIcon() {
        return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>`;
    }

    function currentItems() {
        const json = document.getElementById('websites-json')?.textContent;

        try {
            const websites = json ? JSON.parse(json) : [];

            if (websites.length) {
                return websites;
            }
        } catch (error) {
            // Fall through to cards fallback.
        }

        return Array.from(document.querySelectorAll('.app-card')).map(card => {
            const name = card.querySelector('.card-title')?.textContent?.trim() || 'Layanan';
            const url = card.getAttribute('href') || '#';
            const host = card.querySelector('.card-tag')?.textContent?.trim() || url;
            const category = card.querySelector('.card-desc span')?.textContent?.trim() || '';

            return { name, url, category, host };
        });
    }

    function highlight(text, query) {
        if (!query) { return text; }
        const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return text.replace(new RegExp('(' + escaped + ')', 'gi'), '<mark style="background:transparent;color:var(--ink);font-weight:600;">$1</mark>');
    }

    function render(query) {
        const q = query.trim().toLowerCase();
        const items = currentItems();
        const filtered = q
            ? items.filter(w =>
                w.name.toLowerCase().includes(q) ||
                (w.category || '').toLowerCase().includes(q) ||
                w.host.toLowerCase().includes(q)
            )
            : items;

        activeIdx = filtered.length ? 0 : -1;

        if (!filtered.length) {
            results.innerHTML = '<div class="search-empty">Tidak ada hasil untuk "<strong>' + query + '</strong>"</div>';
            return;
        }

        results.innerHTML = filtered.map((w, i) => `
            <a href="${w.url}" class="search-result-item${i === 0 ? ' active' : ''}" data-idx="${i}" target="${w.url.startsWith('http') ? '_blank' : '_self'}" rel="noopener">
                <div class="search-result-icon ${tintClass(i)}">${globeIcon()}</div>
                <div class="search-result-body">
                    <div class="search-result-name">${highlight(w.name, query)}</div>
                    <div class="search-result-meta">${w.host}</div>
                </div>
                ${w.category ? `<span class="search-result-badge">${w.category}</span>` : ''}
            </a>
        `).join('');

        results.querySelectorAll('.search-result-item').forEach(el => {
            el.addEventListener('mouseenter', function () {
                setActive(parseInt(this.dataset.idx));
            });
        });
    }

    function setActive(idx) {
        activeIdx = idx;
        results.querySelectorAll('.search-result-item').forEach((el, i) => {
            el.classList.toggle('active', i === idx);
            if (i === idx) { el.scrollIntoView({ block: 'nearest' }); }
        });
    }

    function open() {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        input.value = '';
        render('');
        setTimeout(() => input.focus(), 50);
    }

    function close() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', open);

    overlay.addEventListener('click', function (e) {
        if (!modal.contains(e.target)) { close(); }
    });

    input.addEventListener('input', function () {
        render(this.value);
    });

    document.addEventListener('keydown', function (e) {
        // Cmd/Ctrl+K to open
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            overlay.classList.contains('open') ? close() : open();
            return;
        }

        if (!overlay.classList.contains('open')) { return; }

        const rows = results.querySelectorAll('.search-result-item');

        if (e.key === 'Escape') { close(); }
        else if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(Math.min(activeIdx + 1, rows.length - 1));
        }
        else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(Math.max(activeIdx - 1, 0));
        }
        else if (e.key === 'Enter' && activeIdx >= 0) {
            rows[activeIdx]?.click();
        }
    });
})();

// ── Unit filter partial navigation ──
(function () {
    const parser = new DOMParser();
    let requestController = null;

    function cardsGrid() {
        return document.querySelector('.cards-grid');
    }

    function updateSearchData(nextDocument) {
        const currentJson = document.getElementById('websites-json');
        const nextJson = nextDocument.getElementById('websites-json');

        if (currentJson && nextJson) {
            currentJson.textContent = nextJson.textContent;
        }
    }

    function updateActiveUnit(url) {
        const selectedUrl = new URL(url, window.location.origin);

        document.querySelectorAll('.unit-filter-link').forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);

            link.classList.toggle('active', linkUrl.search === selectedUrl.search);
        });
    }

    async function loadUnit(url, shouldPushState = true) {
        const currentGrid = cardsGrid();

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
            const nextGrid = nextDocument.querySelector('.cards-grid');

            if (!nextGrid) {
                throw new Error('Missing cards grid.');
            }

            currentGrid.replaceWith(nextGrid);
            updateSearchData(nextDocument);
            updateActiveUnit(url);

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
