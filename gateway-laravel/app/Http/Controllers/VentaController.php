<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VentaController extends Controller
{
    protected $inventarioUrl = 'http://localhost:5000';
    protected $ventasUrl = 'http://localhost:3000';

    public function registrarVenta(Request $request)
    {
        $request->validate([
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',
            'metodo_pago' => 'required|string',
        ]);

        $productos = $request->productos;
        $metodoPago = $request->metodo_pago;
        $userId = $request->user_id;

        // Verificar stock de cada producto
        $productosConStock = [];
        $totalVenta = 0;

        foreach ($productos as $producto) {
            $productoId = $producto['producto_id'];
            $cantidad = $producto['cantidad'];

            // Consultar stock al microservicio de inventario
            $response = Http::get("{$this->inventarioUrl}/api/productos/{$productoId}/stock");
            
            if ($response->failed()) {
                return response()->json([
                    'error' => 'Error al consultar el inventario'
                ], 500);
            }

            $stockData = $response->json();

            if (!$stockData['disponible'] || $stockData['stock'] < $cantidad) {
                return response()->json([
                    'error' => "Stock insuficiente para el producto {$productoId}. Disponible: {$stockData['stock']}"
                ], 400);
            }

            // Obtener información del producto para calcular el total
            $productoResponse = Http::get("{$this->inventarioUrl}/api/productos/{$productoId}");
            $productoInfo = $productoResponse->json();
            
            $subtotal = $productoInfo['precio'] * $cantidad;
            $totalVenta += $subtotal;

            $productosConStock[] = [
                'producto_id' => $productoId,
                'nombre' => $productoInfo['nombre'],
                'cantidad' => $cantidad,
                'precio' => $productoInfo['precio'],
                'subtotal' => $subtotal
            ];
        }

        // Registrar la venta en el microservicio de ventas
        $ventaData = [
            'usuario_id' => $userId,
            'productos' => $productosConStock,
            'total' => $totalVenta,
            'metodo_pago' => $metodoPago
        ];

        $ventaResponse = Http::post("{$this->ventasUrl}/api/ventas", $ventaData);

        if ($ventaResponse->failed()) {
            return response()->json([
                'error' => 'Error al registrar la venta'
            ], 500);
        }

        $venta = $ventaResponse->json();

        // Actualizar el inventario
        foreach ($productosConStock as $producto) {
            $nuevaCantidad = $stockData['stock'] - $producto['cantidad'];
            
            Http::put("{$this->inventarioUrl}/api/productos/{$producto['producto_id']}", [
                'stock' => $nuevaCantidad
            ]);
        }

        return response()->json([
            'mensaje' => 'Venta registrada exitosamente',
            'venta' => $venta['venta'],
            'total' => $totalVenta
        ]);
    }

    public function obtenerVentas()
    {
        $response = Http::get("{$this->ventasUrl}/api/ventas");
        
        if ($response->failed()) {
            return response()->json([
                'error' => 'Error al obtener las ventas'
            ], 500);
        }

        return response()->json($response->json());
    }

    public function obtenerVenta($id)
    {
        $response = Http::get("{$this->ventasUrl}/api/ventas/{$id}");
        
        if ($response->failed()) {
            return response()->json([
                'error' => 'Error al obtener la venta'
            ], 500);
        }

        return response()->json($response->json());
    }
}