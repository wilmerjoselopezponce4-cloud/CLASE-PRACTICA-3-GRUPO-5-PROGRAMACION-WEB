<?php
declare(strict_types=1);

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function moneda(float $monto): string {
    return '$' . number_format($monto, 2, '.', ',');
}

function datosCarrito(array $carrito, array $productos): array {
    $articulos = [];
    $cantidad = 0;
    $subtotal = 0.0;
    foreach ($carrito as $id => $unidades) {
        $id = (int)$id;
        $unidades = (int)$unidades;
        if (!isset($productos[$id]) || $unidades < 1) {
            continue;
        }
        $producto = $productos[$id];
        $linea = round($producto['precio'] * $unidades, 2);
        $cantidad += $unidades;
        $subtotal += $linea;
        $articulos[] = [
            'id' => $id,
            'nombre' => $producto['nombre'],
            'presentacion' => $producto['presentacion'],
            'precio' => $producto['precio'],
            'imagen' => $producto['imagen'],
            'cantidad' => $unidades,
            'total' => $linea,
        ];
    }
    return ['articulos' => $articulos, 'cantidad' => $cantidad, 'subtotal' => round($subtotal, 2)];
}
