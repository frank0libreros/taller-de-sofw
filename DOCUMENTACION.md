# Sistema de Registro de Ventas - Arquitectura de Microservicios

## Arquitectura del Sistema

El sistema está diseñado con una arquitectura basada en microservicios que permite una escalabilidad y mantenibilidad óptima. La arquitectura se compone de los siguientes componentes:

### Componentes Principales

1. **API Gateway (Laravel)**
   - Punto de entrada único para todas las solicitudes del cliente
   - Gestión de autenticación mediante JWT
   - Enrutamiento de solicitudes a los microservicios correspondientes
   - Validación de tokens y autorización de usuarios

2. **Microservicio de Inventario (Flask + Firebase)**
   - Gestión de productos y control de stock
   - Verificación de disponibilidad de productos
   - Actualización del inventario después de las ventas
   - Base de datos: Firebase

3. **Microservicio de Ventas (Express + MongoDB)**
   - Registro y almacenamiento de transacciones de venta
   - Consulta de historial de ventas
   - Consulta de ventas por usuario o fecha
   - Base de datos: MongoDB

## Diagrama de Arquitectura

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│                 │    │                  │    │                 │
│   Cliente Web   │────│   API Gateway    │────│   Middleware    │
│                 │    │   (Laravel)      │    │   JWT           │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                │
                                │
                    ┌───────────┼───────────┐
                    │           │           │
                    ▼           ▼           ▼
          ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
          │                 │ │                 │ │                 │
          │ Microservicio   │ │ Microservicio   │ │   Firebase      │
          │ de Inventario   │ │ de Ventas       │ │   (Productos)   │
          │ (Flask)         │ │ (Express)       │ │                 │
          │                 │ │                 │ │                 │
          │ Puerto: 5000    │ │ Puerto: 3000    │ │                 │
          └─────────────────┘ └─────────────────┘ └─────────────────┘
                                │
                                │
                                ▼
                       ┌─────────────────┐
                       │                 │
                       │   MongoDB       │
                       │   (Ventas)      │
                       │                 │
                       └─────────────────┘
```

## Endpoints del API Gateway

### Autenticación

#### POST /api/auth/login
**Descripción:** Inicio de sesión de usuario y generación de JWT

**Body de la solicitud:**
```json
{
    "email": "admin@tienda.com",
    "password": "12345678"
}
```

**Respuesta exitosa:**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
        "id": 1,
        "name": "Administrador",
        "email": "admin@tienda.com"
    }
}
```

**Respuesta de error:**
```json
{
    "error": "Credenciales inválidas"
}
```

#### POST /api/auth/logout
**Descripción:** Cierre de sesión del usuario

**Headers requeridos:**
- Authorization: Bearer [token]

**Respuesta:**
```json
{
    "message": "Sesión cerrada exitosamente"
}
```

#### GET /api/auth/me
**Descripción:** Obtener información del usuario autenticado

**Headers requeridos:**
- Authorization: Bearer [token]

**Respuesta:**
```json
{
    "user": {
        "id": 1,
        "name": "Administrador",
        "email": "admin@tienda.com"
    }
}
```

### Gestión de Ventas

#### POST /api/ventas
**Descripción:** Registrar una nueva venta

**Headers requeridos:**
- Authorization: Bearer [token]
- Content-Type: application/json

**Body de la solicitud:**
```json
{
    "productos": [
        {
            "producto_id": "1",
            "cantidad": 2
        },
        {
            "producto_id": "2", 
            "cantidad": 1
        }
    ],
    "metodo_pago": "tarjeta"
}
```

**Flujo interno:**
1. Validación del JWT
2. Verificación de stock en el microservicio de inventario
3. Cálculo del total de la venta
4. Registro de la venta en el microservicio de ventas
5. Actualización del inventario

**Respuesta exitosa:**
```json
{
    "mensaje": "Venta registrada exitosamente",
    "venta": {
        "id": 2,
        "usuario_id": 1,
        "productos": [
            {
                "producto_id": "1",
                "nombre": "Laptop HP",
                "cantidad": 2,
                "precio": 899.99,
                "subtotal": 1799.98
            },
            {
                "producto_id": "2",
                "nombre": "Mouse Inalámbrico",
                "cantidad": 1,
                "precio": 25.99,
                "subtotal": 25.99
            }
        ],
        "total": 1825.97,
        "metodo_pago": "tarjeta",
        "fecha": "2026-03-12T19:30:00.000000Z"
    },
    "total": 1825.97
}
```

**Respuesta de error (stock insuficiente):**
```json
{
    "error": "Stock insuficiente para el producto 1. Disponible: 5"
}
```

#### GET /api/ventas
**Descripción:** Listar todas las ventas registradas

**Headers requeridos:**
- Authorization: Bearer [token]

**Respuesta:**
```json
[
    {
        "id": 1,
        "usuario_id": 1,
        "productos": [
            {
                "producto_id": "1",
                "nombre": "Laptop HP",
                "cantidad": 1,
                "precio": 899.99,
                "subtotal": 899.99
            }
        ],
        "total": 899.99,
        "metodo_pago": "tarjeta",
        "fecha": "2026-03-12T19:25:00.000000Z"
    }
]
```

#### GET /api/ventas/{id}
**Descripción:** Consultar una venta específica por su ID

**Headers requeridos:**
- Authorization: Bearer [token]

**Respuesta:**
```json
{
    "id": 1,
    "usuario_id": 1,
    "productos": [
        {
            "producto_id": "1",
            "nombre": "Laptop HP",
            "cantidad": 1,
            "precio": 899.99,
            "subtotal": 899.99
        }
    ],
    "total": 899.99,
    "metodo_pago": "tarjeta",
    "fecha": "2026-03-12T19:25:00.000000Z"
}
```

## Flujo de Registro de una Venta

El proceso de registro de una venta sigue estos pasos:

### 1. Autenticación del Cliente
- El cliente envía sus credenciales al endpoint `/api/auth/login`
- El API Gateway valida las credenciales contra la base de datos de usuarios
- Se genera un JWT firmado y se devuelve al cliente

### 2. Solicitud de Registro de Venta
- El cliente envía una solicitud POST a `/api/ventas` con el JWT en el header Authorization
- El API Gateway valida el JWT utilizando el middleware JWT

### 3. Validación de Stock
- El API Gateway consulta el stock de cada producto al microservicio de inventario
- Endpoint consultado: `GET /api/productos/{producto_id}/stock`
- Se verifica que haya suficiente stock para cada producto solicitado

### 4. Cálculo del Total
- Para cada producto, se obtiene su información (precio) del microservicio de inventario
- Se calcula el subtotal por producto y el total de la venta

### 5. Registro de la Venta
- Se envía la información de la venta al microservicio de ventas
- Endpoint utilizado: `POST /api/ventas`
- El microservicio de ventas almacena la transacción en MongoDB

### 6. Actualización del Inventario
- Una vez registrada la venta, se actualiza el stock de cada producto
- Se envía una solicitud PUT a `/api/productos/{producto_id}` con la nueva cantidad
- El microservicio de inventario actualiza Firebase con el nuevo stock

### 7. Respuesta al Cliente
- El API Gateway devuelve una confirmación de la venta exitosa
- Incluye detalles de la venta y el total cobrado

## Configuración de los Microservicios

### Microservicio de Inventario (Flask)

**Puerto:** 5000

**Endpoints principales:**
- `GET /health` - Estado del servicio
- `GET /api/productos` - Listar productos
- `GET /api/productos/{id}` - Obtener producto específico
- `GET /api/productos/{id}/stock` - Verificar stock
- `PUT /api/productos/{id}` - Actualizar producto (stock)

**Base de datos:** Firebase

### Microservicio de Ventas (Express)

**Puerto:** 3000

**Endpoints principales:**
- `GET /health` - Estado del servicio
- `POST /api/ventas` - Registrar venta
- `GET /api/ventas` - Listar ventas
- `GET /api/ventas/{id}` - Obtener venta específica

**Base de datos:** MongoDB

## Configuración del API Gateway (Laravel)

**Puerto:** 8000 (por defecto de Laravel)

**Dependencias principales:**
- firebase/php-jwt para manejo de tokens JWT
- Laravel HTTP Client para comunicarse con microservicios

**Configuración JWT:**
- Algoritmo: HS256
- Tiempo de vida: 60 minutos
- Clave secreta: Configurable en .env

**Middleware:**
- jwt: Valida tokens JWT en rutas protegidas

## Guía de Implementación

### Requisitos del Sistema

**Para el API Gateway (Laravel):**
- PHP 8.0 o superior
- Composer
- Laravel 9.x

**Para el Microservicio de Inventario (Flask):**
- Python 3.8 o superior
- Flask
- Firebase Admin SDK

**Para el Microservicio de Ventas (Express):**
- Node.js 14.x o superior
- Express
- MongoDB

### Pasos de Implementación

1. **Configurar el entorno**
   ```bash
   # Clonar el repositorio
   git clone [url-del-repositorio]
   
   # Instalar dependencias del API Gateway
   cd gateway-laravel
   composer install
   
   # Instalar dependencias del microservicio de inventario
   cd ../inventario-flask
   pip install -r requirements.txt
   
   # Instalar dependencias del microservicio de ventas
   cd ../ventas-express
   npm install
   ```

2. **Configurar variables de entorno**
   - Crear archivo `.env` en cada directorio de microservicio
   - Configurar claves de Firebase, MongoDB, y JWT secret

3. **Iniciar los servicios**
   ```bash
   # Iniciar API Gateway
   cd gateway-laravel
   php artisan serve --port=8000
   
   # Iniciar microservicio de inventario
   cd inventario-flask
   python app.py
   
   # Iniciar microservicio de ventas
   cd ventas-express
   node server.js
   ```

4. **Probar el sistema**
   - Realizar login: `POST http://localhost:8000/api/auth/login`
   - Registrar venta: `POST http://localhost:8000/api/ventas` (con token)
   - Consultar ventas: `GET http://localhost:8000/api/ventas` (con token)

## Consideraciones de Seguridad

1. **JWT Security:**
   - Uso de claves secretas robustas
   - Tiempo de vida limitado de tokens
   - Validación estricta de tokens

2. **Validación de Datos:**
   - Validación de entradas en todos los endpoints
   - Control de stock para evitar ventas negativas

3. **Comunicación entre Servicios:**
   - Uso de HTTPS en producción
   - Validación de respuestas entre microservicios

## Escalabilidad

El sistema está diseñado para ser altamente escalable:

1. **Independencia de Microservicios:** Cada microservicio puede escalar independientemente
2. **Base de Datos Distribuida:** Firebase y MongoDB permiten escalado horizontal
3. **Carga Balanceada:** El API Gateway puede distribuir la carga entre múltiples instancias
4. **Cache:** Implementación de cache para reducir la carga en las bases de datos

## Monitoreo y Logs

Cada componente del sistema debe implementar:
- Logs estructurados para seguimiento de operaciones
- Métricas de rendimiento
- Alertas para fallos en la comunicación entre servicios
- Monitoreo del estado de salud de cada microservicio