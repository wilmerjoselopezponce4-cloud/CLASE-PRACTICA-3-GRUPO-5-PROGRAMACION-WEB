/* Mercado Los Champisaca | Grupo 5 | UBE | Clase Práctica 3 */
'use strict';
(() => {
    const config = window.MERCADO5;
    if (!config) return;
    const $ = (sel, base = document) => base.querySelector(sel);
    const $$ = (sel, base = document) => [...base.querySelectorAll(sel)];
    const productMap = new Map(config.productos.map(p => [Number(p.id), p]));
    const money = n => '$' + Number(n).toFixed(2);
    const normalize = s => String(s).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    const html = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    let cart = config.carrito;
    let category = 'todos', query = '', favoriteMode = false, activeDialog = null, previousFocus = null;
    let toastTimeout;
    let favorites = new Set();
    try { favorites = new Set(JSON.parse(localStorage.getItem('m5-favoritos') || '[]').map(Number).filter(id => productMap.has(id))); } catch (_) { favorites = new Set(); }

    const catalog = $('#catalog-grid');
    const catalogCards = $$('.product-card', catalog);
    const empty = $('#empty-results');
    const search = $('#catalog-search');
    const headerSearch = $('#buscador-principal');
    const overlay = $('#overlay');
    const drawer = $('#cart-drawer');
    const quickview = $('#quickview');
    const orderModal = $('#order-modal');
    const originalOrder = new Map(catalogCards.map((el, i) => [el.dataset.id, i]));
    let requestQueue = Promise.resolve();

    function toast(message) {
        const el = $('#toast');
        clearTimeout(toastTimeout);
        el.textContent = message;
        el.classList.add('is-visible');
        toastTimeout = setTimeout(() => el.classList.remove('is-visible'), 3100);
    }
    function saveFavorites() {
        try { localStorage.setItem('m5-favoritos', JSON.stringify([...favorites])); } catch (_) { /* Browser storage may be disabled. */ }
        $$('[data-favorite]').forEach(button => {
            const selected = favorites.has(Number(button.dataset.favorite));
            button.classList.toggle('is-favorite', selected);
            button.setAttribute('aria-pressed', String(selected));
            button.title = selected ? 'Quitar de favoritos' : 'Guardar en favoritos';
        });
        $('#favorites-count').textContent = favorites.size;
        $('#favorites-count').hidden = favorites.size === 0;
        $('#favorites-toggle').setAttribute('aria-pressed', String(favoriteMode));
    }
    function filterProducts() {
        let count = 0;
        catalogCards.forEach(card => {
            const product = productMap.get(Number(card.dataset.id));
            const searchable = normalize(product.nombre + ' ' + product.descripcion + ' ' + product.presentacion);
            const visible = (category === 'todos' || product.categoria === category)
                && (!query || searchable.includes(normalize(query)))
                && (!favoriteMode || favorites.has(product.id));
            card.hidden = !visible;
            if (visible) count++;
        });
        empty.hidden = count > 0;
        $('#results-summary').textContent = `Mostrando ${count} de ${catalogCards.length} productos` + (favoriteMode ? ' · Mis favoritos' : '');
        $('#clear-filters').hidden = category === 'todos' && !query && !favoriteMode;
        $$('[data-filter]').forEach(btn => {
            const selected = btn.dataset.filter === category;
            btn.classList.toggle('is-active', selected);
            btn.setAttribute('aria-pressed', String(selected));
        });
    }
    function setCategory(next, scroll = true) {
        category = next;
        query = '';
        favoriteMode = false;
        search.value = '';
        headerSearch.value = '';
        filterProducts();
        saveFavorites();
        closeMenu();
        if (scroll) $('#catalogo').scrollIntoView({ behavior: 'smooth' });
    }
    function resetFilters() {
        setCategory('todos', false);
        $('#sort-products').value = 'original';
        sortProducts();
    }
    function sortProducts() {
        const sort = $('#sort-products').value;
        const cards = [...catalogCards].sort((a,b) => {
            const pa = productMap.get(Number(a.dataset.id));
            const pb = productMap.get(Number(b.dataset.id));
            if (sort === 'price-asc') return pa.precio - pb.precio || pa.id - pb.id;
            if (sort === 'price-desc') return pb.precio - pa.precio || pa.id - pb.id;
            if (sort === 'name') return pa.nombre.localeCompare(pb.nombre, 'es');
            return originalOrder.get(a.dataset.id) - originalOrder.get(b.dataset.id);
        });
        cards.forEach(card => catalog.appendChild(card));
    }

    function closeMenu() {
        $('#mega-menu').hidden = true;
        $('#category-trigger').setAttribute('aria-expanded', 'false');
    }
    $('#category-trigger').addEventListener('click', () => {
        const menu = $('#mega-menu');
        menu.hidden = !menu.hidden;
        $('#category-trigger').setAttribute('aria-expanded', String(!menu.hidden));
    });
    $('#close-menu').addEventListener('click', closeMenu);
    document.addEventListener('click', e => {
        if (!e.target.closest('.category-menu-wrap')) closeMenu();
    });
    $$('[data-go-category]').forEach(btn => btn.addEventListener('click', () => setCategory(btn.dataset.goCategory)));
    $$('[data-filter]').forEach(btn => btn.addEventListener('click', () => {
        category = btn.dataset.filter;
        favoriteMode = false;
        saveFavorites();
        filterProducts();
    }));
    search.addEventListener('input', () => {
        query = search.value;
        headerSearch.value = search.value;
        filterProducts();
    });
    headerSearch.addEventListener('input', () => {
        query = headerSearch.value;
        search.value = headerSearch.value;
        filterProducts();
    });
    $('#header-search').addEventListener('submit', e => {
        e.preventDefault();
        $('#catalogo').scrollIntoView({ behavior: 'smooth' });
    });
    $('#sort-products').addEventListener('change', sortProducts);
    $('#clear-filters').addEventListener('click', resetFilters);
    $('#empty-reset').addEventListener('click', resetFilters);
    function showFavorites() {
        favoriteMode = !favoriteMode;
        category = 'todos';
        query = '';
        search.value = '';
        headerSearch.value = '';
        saveFavorites();
        filterProducts();
        $('#catalogo').scrollIntoView({ behavior: 'smooth' });
        if (favoriteMode && favorites.size === 0) toast('Todavía no tienes favoritos. Usa el corazón de cada producto.');
    }
    $('#favorites-toggle').addEventListener('click', showFavorites);
    $('#footer-favorites').addEventListener('click', showFavorites);

    function openDialog(dialog) {
        if (activeDialog) closeDialog(false);
        previousFocus = document.activeElement;
        overlay.hidden = false;
        activeDialog = dialog;
        document.body.classList.add('modal-open');
        dialog.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => { overlay.classList.add('is-visible'); dialog.classList.add('is-visible'); });
        dialog.focus({ preventScroll: true });
    }
    function closeDialog(restoreFocus = true) {
        if (!activeDialog) return;
        activeDialog.classList.remove('is-visible');
        activeDialog.setAttribute('aria-hidden', 'true');
        overlay.classList.remove('is-visible');
        document.body.classList.remove('modal-open');
        activeDialog = null;
        setTimeout(() => { if (!activeDialog) overlay.hidden = true; }, 230);
        if (restoreFocus && previousFocus?.isConnected) previousFocus.focus({ preventScroll: true });
    }
    function openCart() { renderCart(); openDialog(drawer); }
    $$('[data-open-cart]').forEach(btn => btn.addEventListener('click', openCart));
    $$('[data-close-dialog]').forEach(btn => btn.addEventListener('click', () => closeDialog()));
    overlay.addEventListener('click', () => closeDialog());
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { if (activeDialog) closeDialog(); else closeMenu(); }
        if (e.key === 'Tab' && activeDialog) {
            const focusable = $$('button:not([disabled]),a[href],input:not([disabled]),select:not([disabled])',activeDialog).filter(el => el.getClientRects().length);
            if (!focusable.length) { e.preventDefault(); return; }
            if (e.shiftKey && document.activeElement === focusable[0]) { focusable.at(-1).focus(); e.preventDefault(); }
            if (!e.shiftKey && document.activeElement === focusable.at(-1)) { focusable[0].focus(); e.preventDefault(); }
        }
    });

    function updateCartBadges() {
        $$('[data-cart-count]').forEach(el => el.textContent = cart.cantidad);
        $('#header-cart-total').textContent = money(cart.subtotal);
    }
    function renderCart() {
        updateCartBadges();
        $('#cart-subtotal').textContent = money(cart.subtotal);
        const holder = $('#cart-items');
        $('#cart-footer').hidden = cart.cantidad === 0;
        if (!cart.cantidad) {
            holder.innerHTML = `<div class="cart-empty"><div class="cart-empty__icon"><svg class="icon"><use href="#icon-cart"></use></svg></div><h3>Tu carrito está vacío</h3><p>¡Hay muchas cosas buenas por descubrir! Añade tus productos favoritos para comenzar.</p><button class="btn btn--primary" type="button" data-cart-explore>Explorar el catálogo <svg class="icon"><use href="#icon-arrow"></use></svg></button></div>`;
            return;
        }
        holder.innerHTML = cart.articulos.map(item => `<div class="cart-item">
            <div class="cart-item__image"><img src="${html(item.imagen)}" alt="${html(item.nombre)}"></div>
            <div class="cart-item__info"><div class="cart-item__heading"><strong>${html(item.nombre)}</strong><button type="button" data-remove="${item.id}" aria-label="Quitar ${html(item.nombre)}"><svg class="icon"><use href="#icon-trash"></use></svg></button></div><div class="cart-item__meta">${html(item.presentacion)} · ${money(item.precio)} c/u</div><div class="cart-item__actions"><div class="quantity-stepper"><button type="button" data-update="${item.id}" data-qty="${item.cantidad - 1}" aria-label="Disminuir ${html(item.nombre)}"><svg class="icon"><use href="#icon-minus"></use></svg></button><span>${item.cantidad}</span><button type="button" data-update="${item.id}" data-qty="${item.cantidad + 1}" ${item.cantidad >= 30 ? 'disabled' : ''} aria-label="Aumentar ${html(item.nombre)}"><svg class="icon"><use href="#icon-plus"></use></svg></button></div><strong>${money(item.total)}</strong></div></div></div>`).join('');
    }
    async function requestCart(accion, payload = {}) {
        const response = await fetch('api/carrito.php', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ accion, ...payload, token: config.token })
        });
        let data;
        try { data = await response.json(); } catch (_) { throw new Error('No se recibió una respuesta válida del servidor PHP.'); }
        if (!response.ok || !data.ok) throw new Error(data.mensaje || 'No se pudo actualizar el carrito.');
        if (data.carrito) { cart = data.carrito; renderCart(); }
        return data;
    }
    function cartAction(accion, payload = {}) {
        const op = requestQueue.catch(() => {}).then(() => requestCart(accion, payload));
        requestQueue = op.catch(err => toast(err.message || 'Error de conexión con PHP.'));
        return op;
    }
    document.addEventListener('click', e => {
        const favorite = e.target.closest('[data-favorite]');
        if (favorite) {
            const id = Number(favorite.dataset.favorite);
            if (favorites.has(id)) { favorites.delete(id); toast('Producto quitado de favoritos'); }
            else { favorites.add(id); toast('Producto guardado en favoritos'); }
            saveFavorites();
            if (favoriteMode) filterProducts();
            return;
        }
        const add = e.target.closest('[data-add]');
        if (add) {
            cartAction('agregar', { id: Number(add.dataset.add) }).then(() => toast('¡Producto agregado a tu carrito!')).catch(() => {});
            return;
        }
        const view = e.target.closest('[data-quickview]');
        if (view) { showProduct(Number(view.dataset.quickview)); return; }
        const remove = e.target.closest('[data-remove]');
        if (remove) { cartAction('quitar', { id: Number(remove.dataset.remove) }).catch(() => {}); return; }
        const update = e.target.closest('[data-update]');
        if (update) { cartAction('actualizar', { id: Number(update.dataset.update), cantidad: Number(update.dataset.qty) }).catch(() => {}); return; }
        const explore = e.target.closest('[data-cart-explore]');
        if (explore) { closeDialog(); $('#catalogo').scrollIntoView({ behavior: 'smooth' }); }
    });
    $('#clear-cart').addEventListener('click', () => cartAction('vaciar').then(() => toast('Carrito vaciado')).catch(() => {}));
    $('#checkout-btn').addEventListener('click', () => {
        if (!cart.cantidad) return;
        const btn = $('#checkout-btn');
        btn.disabled = true;
        cartAction('confirmar').then(data => {
            closeDialog(false);
            $('#order-number').textContent = data.pedido;
            $('#order-amount').textContent = `Total simulado: ${money(data.total)}`;
            openDialog(orderModal);
        }).catch(() => {}).finally(() => { btn.disabled = false; });
    });
    $('#order-done').addEventListener('click', () => closeDialog());

    let currentViewId = null;
    function showProduct(id) {
        const p = productMap.get(id);
        if (!p) return;
        currentViewId = id;
        $('#quickview-title').textContent = p.nombre;
        $('#quickview-image').src = p.imagen;
        $('#quickview-image').alt = p.nombre;
        $('#quickview-unit').textContent = p.presentacion;
        $('#quickview-description').textContent = p.descripcion;
        $('#quickview-price').textContent = money(p.precio);
        openDialog(quickview);
    }
    $('#quickview-add').addEventListener('click', () => {
        if (!currentViewId) return;
        const id = currentViewId;
        cartAction('agregar', { id }).then(() => { closeDialog(); openCart(); toast('¡Producto agregado a tu carrito!'); }).catch(() => {});
    });

    // Carga el estado persistente del carrito desde la sesión PHP al abrir la página.
    renderCart();
    saveFavorites();
    filterProducts();
})();
