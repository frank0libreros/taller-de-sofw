const express = require('express');
const cors = require('cors');

const app = express();

app.use(cors());
app.use(express.json());

let ventas = [
    {
        id: 1,
        usuario_id: 1,
        productos: [
            { producto_id: "1", nombre: "Laptop HP", cantidad: 1, precio: 899.99 }
        ],
        total: 899.99,
        fecha: new Date(),
        metodo_pago: "tarjeta"
    }
];

app.get('/health', (req, res) => {
    res.json({ estado: "vivo", servicio: "ventas" });
});

app.post('/api/ventas', (req, res) => {
    const nuevaVenta = {
        id: ventas.length + 1,
        ...req.body,
        fecha: new Date()
    };
    
    ventas.push(nuevaVenta);
    res.status(201).json({
        mensaje: "Venta registrada",
        venta: nuevaVenta
    });
});

app.get('/api/ventas', (req, res) => {
    res.json(ventas);
});

app.get('/api/ventas/:id', (req, res) => {
    const venta = ventas.find(v => v.id == req.params.id);
    if (venta) {
        res.json(venta);
    } else {
        res.status(404).json({ error: "Venta no encontrada" });
    }
});

app.listen(3000, () => {
    console.log('Microservicio de ventas corriendo en puerto 3000');
});