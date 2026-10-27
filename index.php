<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
}
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
if (!isset($_SESSION['token_csrf'])) {
    $_SESSION['token_csrf'] = bin2hex(random_bytes(24));
}
require_once __DIR__ . '/includes/productos.php';
require_once __DIR__ . '/includes/funciones.php';
$estadoInicial = datosCarrito($_SESSION['carrito'], $productos);
$destacados = array_filter($productos, static fn(array $producto): bool => $producto['etiqueta'] !== '');
$destacados = array_slice($destacados, 0, 8, true);
function tarjetaProducto(array $producto): void {
    $id = (int)$producto['id'];
    $oferta = $producto['etiqueta'] === 'oferta';
    $popular = $producto['etiqueta'] === 'popular';
    ?>
    <article class="product-card" data-id="<?= $id ?>" data-name="<?= escapar($producto['nombre']) ?>" data-category="<?= escapar($producto['categoria']) ?>" data-price="<?= escapar((string)$producto['precio']) ?>">
        <div class="product-card__visual">
            <?php if ($oferta): ?><span class="product-badge product-badge--offer">Precio especial</span><?php elseif ($popular): ?><span class="product-badge">Favorito</span><?php endif; ?>
            <button class="favorite-btn" type="button" data-favorite="<?= $id ?>" title="Guardar en favoritos" aria-label="Guardar <?= escapar($producto['nombre']) ?> en favoritos" aria-pressed="false">
                <svg class="icon" aria-hidden="true"><use href="#icon-heart"></use></svg>
            </button>
            <img src="<?= escapar($producto['imagen']) ?>" alt="<?= escapar($producto['nombre']) ?>" loading="lazy" width="260" height="225">
            <button class="quickview-btn" type="button" data-quickview="<?= $id ?>">Vista rápida <svg class="icon icon--sm" aria-hidden="true"><use href="#icon-eye"></use></svg></button>
        </div>
        <div class="product-card__body">
            <span class="product-card__unit"><?= escapar($producto['presentacion']) ?></span>
            <h3><?= escapar($producto['nombre']) ?></h3>
            <p><?= escapar($producto['descripcion']) ?></p>
            <div class="product-card__bottom">
                <strong class="product-price"><?= moneda((float)$producto['precio']) ?></strong>
                <button class="add-btn" type="button" data-add="<?= $id ?>" aria-label="Agregar <?= escapar($producto['nombre']) ?> al carrito">
                    <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg><span>Agregar</span>
                </button>
            </div>
        </div>
    </article>
    <?php
}
?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#cc1734">
    <meta name="description" content="Mercado Los Champisaca: tienda de alimentos desarrollada por el Grupo 5 de Programación Web Avanzada de la UBE. Catálogo demostrativo con 40 productos.">
    <title>Mercado Los Champisaca | Tu supermercado en línea · Grupo 5 UBE</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="stylesheet" href="assets/css/estilos.css?v=1.1">
    <script>window.MERCADO5 = <?= json_encode(['token' => $_SESSION['token_csrf'], 'carrito' => $estadoInicial, 'productos' => array_values($productos)], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
    <script src="assets/js/app.js?v=1.0" defer></script>
</head>
<body>
<svg xmlns="http://www.w3.org/2000/svg" class="svg-sprite" aria-hidden="true"><symbol id="icon-cart" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></symbol><symbol id="icon-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></symbol><symbol id="icon-heart" viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0l-1 1-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></symbol><symbol id="icon-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol><symbol id="icon-minus" viewBox="0 0 24 24"><path d="M5 12h14"/></symbol><symbol id="icon-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol><symbol id="icon-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol><symbol id="icon-close" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></symbol><symbol id="icon-truck" viewBox="0 0 24 24"><path d="M1 4h14v12H1zM15 8h4l4 4v4h-8"/><circle cx="5.5" cy="18" r="2"/><circle cx="19" cy="18" r="2"/></symbol><symbol id="icon-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol><symbol id="icon-shield" viewBox="0 0 24 24"><path d="m12 22 7-4V6l-7-4-7 4v12z"/><path d="m9 12 2 2 4-4"/></symbol><symbol id="icon-eye" viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></symbol><symbol id="icon-arrow" viewBox="0 0 24 24"><path d="M4 12h16m-6-6 6 6-6 6"/></symbol><symbol id="icon-trash" viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m5 4v7m4-7v7"/></symbol><symbol id="icon-sliders" viewBox="0 0 24 24"><path d="M4 7h16M4 17h16"/><circle cx="9" cy="7" r="2"/><circle cx="16" cy="17" r="2"/></symbol><symbol id="icon-star" viewBox="0 0 24 24"><path d="m12 2 3 6 6.6 1-4.8 4.7 1.2 6.6L12 17l-6 3.3 1.2-6.6L2.4 9 9 8z"/></symbol></svg>

<div class="announcement"><div class="container announcement__inner"><span><span class="announcement__sparkle">✦</span> ¡Bienvenido a Mercado Los Champisaca! Todo con más sabor para la familia.</span><span class="announcement__tag">Proyecto universitario · Grupo 5 · UBE</span></div></div>
<header class="site-header" id="inicio">
    <div class="container header-main">
        <a class="brand" href="#inicio" aria-label="Mercado Los Champisaca, ir al inicio"><span class="brand__mark"><span>5</span><svg class="icon" aria-hidden="true"><use href="#icon-cart"></use></svg></span><span class="brand__name">mercado<span>Los Champisaca</span><small>TODO CON MÁS SABOR PARA LA FAMILIA</small></span></a>
        <form id="header-search" class="search-form" role="search"><svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg><label class="sr-only" for="buscador-principal">Buscar productos</label><input id="buscador-principal" type="search" placeholder="¿Qué necesitas comprar hoy?" autocomplete="off"><button type="submit">Buscar</button></form>
        <div class="header-actions"><button class="header-action favorites-toggle" id="favorites-toggle" type="button" title="Ver favoritos"><span class="header-action__icon"><svg class="icon"><use href="#icon-heart"></use></svg><span class="counter counter--small" id="favorites-count" hidden>0</span></span><span>Favoritos</span></button><button class="header-action header-action--cart" type="button" data-open-cart aria-label="Abrir carrito de compras"><span class="header-action__icon"><svg class="icon"><use href="#icon-cart"></use></svg><span class="counter" data-cart-count><?= $estadoInicial['cantidad'] ?></span></span><span>Mi carrito <small id="header-cart-total"><?= moneda($estadoInicial['subtotal']) ?></small></span></button></div>
    </div>
    <nav class="main-nav" aria-label="Navegación principal"><div class="container nav-inner">
        <div class="category-menu-wrap"><button id="category-trigger" class="category-trigger" type="button" aria-expanded="false" aria-controls="mega-menu"><svg class="icon"><use href="#icon-menu"></use></svg> Todas las categorías <svg class="icon icon--sm"><use href="#icon-chevron"></use></svg></button>
            <div class="mega-menu" id="mega-menu" hidden><div class="mega-menu__heading">Explora nuestro supermercado <button class="icon-only" type="button" id="close-menu" aria-label="Cerrar categorías"><svg class="icon"><use href="#icon-close"></use></svg></button></div><div class="mega-menu__items"><button type="button" data-go-category="todos"><span>🛍️</span>Todos los productos<svg class="icon icon--sm"><use href="#icon-arrow"></use></svg></button><?php foreach ($categorias as $clave => $categoria): ?><button type="button" data-go-category="<?= escapar($clave) ?>"><span><?= escapar($categoria['emoji']) ?></span><?= escapar($categoria['nombre']) ?><svg class="icon icon--sm"><use href="#icon-arrow"></use></svg></button><?php endforeach; ?></div><div class="mega-menu__foot">40 productos para tu compra diaria <svg class="icon icon--sm"><use href="#icon-check"></use></svg></div></div>
        </div>
        <a href="#categorias">Categorías</a><a href="#destacados">Destacados</a><a href="#catalogo">Todos los productos</a><a href="#equipo">Nuestro equipo</a><span class="nav-educational"><svg class="icon icon--sm"><use href="#icon-shield"></use></svg> Compra simulada y segura</span>
    </div></nav>
</header>
<main>
<section class="hero container" aria-labelledby="hero-title"><div class="hero__copy"><div class="hero__eyebrow"><span class="eyebrow-dot"></span> ALIMENTOS PARA COMPARTIR EN FAMILIA</div><h1 id="hero-title">Todo con más sabor <em>para la familia.</em></h1><p>En Mercado Los Champisaca encuentras alimentos frescos, abarrotes, bebidas y todo lo que tu familia necesita, en un solo lugar.</p><div class="hero__buttons"><a class="btn btn--primary" href="#catalogo">Explorar productos <svg class="icon"><use href="#icon-arrow"></use></svg></a><a class="btn btn--outline" href="#categorias">Ver categorías</a></div><div class="hero__trust"><div class="avatar-stack"><span>JT</span><span>JD</span><span>WA</span><span>JC</span><span>WL</span></div><div><strong>Hecho por el Grupo 5</strong><small>Programación Web Avanzada · UBE</small></div></div></div>
<div class="hero__art" aria-label="Selección de frutas y productos frescos"><div class="hero__glow"></div><div class="hero__photo hero__photo--big"><img src="assets/img/producto-25.webp" alt="Manzanas frescas" fetchpriority="high"></div><div class="hero__photo hero__photo--orange"><img src="assets/img/producto-27.webp" alt="Naranjas frescas"></div><div class="hero__photo hero__photo--banana"><img src="assets/img/producto-26.webp" alt="Bananos frescos"></div><div class="hero__sticker"><span>✦</span><strong>40</strong><small>PRODUCTOS</small></div><div class="hero__float-label"><span>✓</span> Calidad para tu mesa</div><span class="hero__sparkle hero__sparkle--one">✳</span><span class="hero__sparkle hero__sparkle--two">✳</span></div></section>

<section class="benefits container" aria-label="Ventajas de Mercado Los Champisaca"><div class="benefit"><span class="benefit__icon"><svg class="icon"><use href="#icon-check"></use></svg></span><div><strong>Productos variados</strong><small>40 opciones para elegir</small></div></div><div class="benefit"><span class="benefit__icon"><svg class="icon"><use href="#icon-truck"></use></svg></span><div><strong>Compra fácil</strong><small>Carrito en tiempo real</small></div></div><div class="benefit"><span class="benefit__icon"><svg class="icon"><use href="#icon-star"></use></svg></span><div><strong>7 categorías</strong><small>Todo bien organizado</small></div></div><div class="benefit"><span class="benefit__icon"><svg class="icon"><use href="#icon-shield"></use></svg></span><div><strong>Proyecto académico</strong><small>Sin pagos reales</small></div></div></section>

<section id="categorias" class="section container"><div class="section-heading"><div><span class="section-kicker">PARA CADA NECESIDAD</span><h2>Compra por categoría<span class="heading-period">.</span></h2><p>Encuentra lo que buscas de forma rápida y sencilla.</p></div><a class="text-link" href="#catalogo">Ver todos los productos <svg class="icon icon--sm"><use href="#icon-arrow"></use></svg></a></div><div class="categories-grid">
<?php foreach ($categorias as $clave => $categoria): ?>
<button class="category-card" type="button" data-go-category="<?= escapar($clave) ?>"><span class="category-card__media"><img src="<?= escapar($productos[$categoria['destacado']]['imagen']) ?>" loading="lazy" alt=""><span class="category-card__emoji"><?= escapar($categoria['emoji']) ?></span></span><strong><?= escapar($categoria['nombre']) ?></strong><small><?= count(array_filter($productos, static fn(array $p):bool => $p['categoria'] === $clave)) ?> productos <svg class="icon icon--sm"><use href="#icon-arrow"></use></svg></small></button>
<?php endforeach; ?></div></section>

<section class="promo-section container" aria-label="Colecciones recomendadas"><button type="button" class="promo-card promo-card--fruit" data-go-category="frutas"><div><span>DE LA HUERTA A TU MESA</span><h3>Elige lo fresco<br>cada día.</h3><span class="promo-card__link">Explorar frutas <svg class="icon icon--sm"><use href="#icon-arrow"></use></svg></span></div><img src="assets/img/producto-25.webp" alt="" loading="lazy"></button><button type="button" class="promo-card promo-card--pantry" data-go-category="despensa"><div><span>BÁSICOS QUE NO PUEDEN FALTAR</span><h3>Todo para<br>tu despensa.</h3><span class="promo-card__link">Comprar abarrotes <svg class="icon icon--sm"><use href="#icon-arrow"></use></svg></span></div><img src="assets/img/producto-31.webp" alt="" loading="lazy"></button></section>

<section id="destacados" class="section section--featured"><div class="container"><div class="section-heading"><div><span class="section-kicker">SELECCIÓN DEL SÚPER</span><h2>Favoritos de nuestra tienda<span class="heading-period">.</span></h2><p>Una selección para empezar tu compra con buen pie.</p></div><a class="text-link" href="#catalogo">Descubrir el catálogo <svg class="icon icon--sm"><use href="#icon-arrow"></use></svg></a></div><div class="products-grid products-grid--featured"><?php foreach ($destacados as $producto) { tarjetaProducto($producto); } ?></div></div></section>

<section id="catalogo" class="section container catalog-section"><div class="section-heading"><div><span class="section-kicker">EL MERCADO COMPLETO</span><h2>Nuestros productos<span class="heading-period">.</span></h2><p>Busca, filtra y agrega tus favoritos al carrito.</p></div><div class="catalog-total"><strong>40</strong><span>productos<br>disponibles</span></div></div>
<div class="catalog-toolbar"><div class="filter-tabs" role="group" aria-label="Filtrar productos por categoría"><button class="filter-tab is-active" type="button" data-filter="todos" aria-pressed="true">Todos</button><?php foreach ($categorias as $clave => $categoria): ?><button class="filter-tab" type="button" data-filter="<?= escapar($clave) ?>" aria-pressed="false"><?= escapar($categoria['nombre']) ?></button><?php endforeach; ?></div><div class="catalog-controls"><label class="search-small"><svg class="icon icon--sm"><use href="#icon-search"></use></svg><span class="sr-only">Buscar en el catálogo</span><input id="catalog-search" type="search" placeholder="Buscar producto..."></label><label class="sort-select"><svg class="icon icon--sm"><use href="#icon-sliders"></use></svg><span class="sr-only">Ordenar productos</span><select id="sort-products"><option value="original">Recomendados</option><option value="price-asc">Menor precio</option><option value="price-desc">Mayor precio</option><option value="name">Nombre: A a Z</option></select></label></div></div>
<div class="catalog-feedback"><span id="results-summary" aria-live="polite">Mostrando 40 productos</span><button id="clear-filters" type="button" hidden>Limpiar filtros ×</button></div>
<div class="products-grid" id="catalog-grid"><?php foreach ($productos as $producto) { tarjetaProducto($producto); } ?></div><div class="empty-results" id="empty-results" hidden><span>🔍</span><h3>No encontramos productos</h3><p>Prueba con otra palabra o selecciona una categoría distinta.</p><button class="btn btn--primary" id="empty-reset" type="button">Ver todos los productos</button></div>
</section>
<section class="team-section" id="equipo"><div class="container team-section__inner"><div class="team-section__title"><span class="section-kicker">PROYECTO UNIVERSITARIO</span><h2>Hecho en equipo, <span>hecho con dedicación.</span></h2><p>Clase Práctica 3 · Programación Web Avanzada · Universidad Bolivariana del Ecuador</p></div><div class="team-list"><span>Jorge Toapanta</span><span>Jhoely Dávila</span><span>Wilmer Adriano</span><span>Jhonatan Chávez</span><span>Wilmer López</span></div></div></section>
</main>
<footer class="site-footer"><div class="container footer-main"><div class="footer-brand"><a class="brand brand--footer" href="#inicio"><span class="brand__mark"><span>5</span><svg class="icon"><use href="#icon-cart"></use></svg></span><span class="brand__name">mercado<span>Los Champisaca</span><small>TODO CON MÁS SABOR PARA LA FAMILIA</small></span></a><p>Todo con más sabor para la familia. Tienda virtual académica desarrollada con HTML, CSS, JavaScript y PHP.</p></div><div class="footer-links"><strong>Explorar</strong><a href="#inicio">Inicio</a><a href="#categorias">Categorías</a><a href="#catalogo">Productos</a><a href="#equipo">Grupo 5</a></div><div class="footer-links"><strong>Nuestra tienda</strong><button type="button" data-open-cart>Mi carrito</button><button type="button" id="footer-favorites">Mis favoritos</button><a href="#destacados">Recomendados</a></div><div class="footer-note"><strong>Información importante</strong><p>Los precios y las compras son simulados para fines académicos. Esta página no vende productos ni procesa pagos.</p><span class="footer-note__badge"><svg class="icon icon--sm"><use href="#icon-shield"></use></svg> Demostración educativa</span></div></div><div class="container footer-bottom"><span>© <?= date('Y') ?> Mercado Los Champisaca · Grupo 5 · UBE</span><span>Diseñado con ♥ en Ecuador <span aria-hidden="true">🇪🇨</span></span></div></footer>

<div id="overlay" class="overlay" hidden></div>
<aside id="cart-drawer" class="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cart-title" aria-hidden="true" tabindex="-1"><div class="drawer-header"><div><span class="drawer-kicker">TU COMPRA</span><h2 id="cart-title">Mi carrito <span class="cart-title-count" data-cart-count><?= $estadoInicial['cantidad'] ?></span></h2></div><button class="icon-only" type="button" data-close-dialog aria-label="Cerrar carrito"><svg class="icon"><use href="#icon-close"></use></svg></button></div><div id="cart-items" class="cart-items"></div><div id="cart-footer" class="cart-footer"><div class="cart-total-row"><span>Subtotal</span><strong id="cart-subtotal">$0.00</strong></div><p class="cart-disclaimer">Precios de demostración. No se cobran pagos ni se realizan entregas.</p><button type="button" id="checkout-btn" class="btn btn--primary btn--full">Simular pedido <svg class="icon"><use href="#icon-arrow"></use></svg></button><button type="button" id="clear-cart" class="cart-clear">Vaciar carrito</button></div></aside>
<div id="quickview" class="quickview-modal" role="dialog" aria-modal="true" aria-labelledby="quickview-title" aria-hidden="true" tabindex="-1"><button class="icon-only quickview-close" type="button" data-close-dialog aria-label="Cerrar detalle"><svg class="icon"><use href="#icon-close"></use></svg></button><div class="quickview__visual"><img id="quickview-image" src="assets/img/producto-01.webp" alt=""></div><div class="quickview__content"><span class="section-kicker">PRODUCTO DE MERCADO LOS CHAMPISACA</span><h2 id="quickview-title"></h2><span id="quickview-unit" class="quickview__unit"></span><p id="quickview-description"></p><strong id="quickview-price" class="quickview__price"></strong><button class="btn btn--primary btn--full" type="button" id="quickview-add"><svg class="icon"><use href="#icon-cart"></use></svg> Agregar al carrito</button><small>Compra simulada · Sin transacciones reales</small></div></div>
<div class="order-modal" id="order-modal" role="dialog" aria-modal="true" aria-labelledby="order-title" aria-hidden="true" tabindex="-1"><div class="order-modal__icon"><svg class="icon"><use href="#icon-check"></use></svg></div><h2 id="order-title">¡Pedido simulado!</h2><p>Tu recorrido de compra se completó correctamente. No se ha realizado ningún pago ni solicitud real.</p><div class="order-code" id="order-number"></div><span id="order-amount"></span><button id="order-done" class="btn btn--primary btn--full" type="button">Seguir comprando</button></div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
</body>
</html>
