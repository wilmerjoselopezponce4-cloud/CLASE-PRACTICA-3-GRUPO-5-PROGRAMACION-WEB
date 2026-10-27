<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
require_once __DIR__ . '/../includes/productos.php';
require_once __DIR__ . '/../includes/funciones.php';

function responder(array $datos, int $estado = 200): never {
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
if (!isset($_SESSION['token_csrf'])) {
    $_SESSION['token_csrf'] = bin2hex(random_bytes(24));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    responder(['ok' => true, 'carrito' => datosCarrito($_SESSION['carrito'], $productos)]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    responder(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}
$entrada = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($entrada)) {
    responder(['ok' => false, 'mensaje' => 'Datos no válidos.'], 400);
}
if (!isset($entrada['token']) || !is_string($entrada['token']) || !hash_equals($_SESSION['token_csrf'], $entrada['token'])) {
    responder(['ok' => false, 'mensaje' => 'Sesión inválida. Actualiza la página.'], 403);
}
$accion = $entrada['accion'] ?? '';
if (!is_string($accion)) {
    responder(['ok' => false, 'mensaje' => 'Acción inválida.'], 400);
}
if (in_array($accion, ['agregar', 'actualizar', 'quitar'], true)) {
    $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id === false || !isset($productos[$id])) {
        responder(['ok' => false, 'mensaje' => 'Producto no encontrado.'], 400);
    }
    $actual = (int)($_SESSION['carrito'][$id] ?? 0);
    if ($accion === 'agregar') {
        $_SESSION['carrito'][$id] = min(30, $actual + 1);
    } elseif ($accion === 'quitar') {
        unset($_SESSION['carrito'][$id]);
    } else {
        $cantidad = filter_var($entrada['cantidad'] ?? null, FILTER_VALIDATE_INT);
        if ($cantidad === false || $cantidad < 0 || $cantidad > 30) {
            responder(['ok' => false, 'mensaje' => 'La cantidad debe estar entre 0 y 30.'], 400);
        }
        if ($cantidad === 0) {
            unset($_SESSION['carrito'][$id]);
        } else {
            $_SESSION['carrito'][$id] = $cantidad;
        }
    }
} elseif ($accion === 'vaciar') {
    $_SESSION['carrito'] = [];
} elseif ($accion === 'confirmar') {
    $previo = datosCarrito($_SESSION['carrito'], $productos);
    if ($previo['cantidad'] === 0) {
        responder(['ok' => false, 'mensaje' => 'Agrega productos antes de continuar.'], 400);
    }
    // Pedido simulado para la clase práctica: no se almacena ni transmite a comercios.
    $numero = 'G5-' . date('ymd') . '-' . random_int(1000, 9999);
    $_SESSION['carrito'] = [];
    responder(['ok' => true, 'pedido' => $numero, 'total' => $previo['subtotal'], 'carrito' => datosCarrito([], $productos)]);
} else {
    responder(['ok' => false, 'mensaje' => 'Acción desconocida.'], 400);
}
responder(['ok' => true, 'carrito' => datosCarrito($_SESSION['carrito'], $productos)]);
