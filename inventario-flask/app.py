from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Datos de prueba
inventario = [
    {
        "id": "1",
        "nombre": "Laptop HP",
        "precio": 899.99,
        "stock": 10,
        "categoria": "Electrónica"
    },
    {
        "id": "2",
        "nombre": "Mouse Inalámbrico",
        "precio": 25.99,
        "stock": 50,
        "categoria": "Accesorios"
    }
]

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"estado": "vivo", "servicio": "inventario"})

@app.route('/api/productos', methods=['GET'])
def get_productos():
    return jsonify(inventario)

@app.route('/api/productos/<producto_id>', methods=['GET'])
def get_producto(producto_id):
    for producto in inventario:
        if producto["id"] == producto_id:
            return jsonify(producto)
    return jsonify({"error": "Producto no encontrado"}), 404

@app.route('/api/productos/<producto_id>/stock', methods=['GET'])
def verificar_stock(producto_id):
    for producto in inventario:
        if producto["id"] == producto_id:
            return jsonify({
                "producto_id": producto_id,
                "stock": producto["stock"],
                "disponible": producto["stock"] > 0
            })
    return jsonify({"error": "Producto no encontrado"}), 404

if __name__ == '__main__':
    app.run(debug=True, port=5000)