
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL, -- Usar hash (ej. bcrypt) para almacenar de forma segura
    nombre_completo VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagen_url VARCHAR(255),
    categoria_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
);
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(255) NOT NULL,
    telefono_cliente VARCHAR(20) NOT NULL,
    direccion_envio TEXT NOT NULL,
    departamento_envio VARCHAR(100) NOT NULL,
    monto_total DECIMAL(10, 2) NOT NULL,
    estado ENUM('Pendiente', 'Pagado', 'Pendiente de Envío', 'Enviado', 'Completado', 'Cancelado') NOT NULL DEFAULT 'Pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pedidos_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL, -- Precio al momento de la compra
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT -- Evita borrar productos si están en un pedido
);

CREATE TABLE transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    libelula_deuda_id VARCHAR(255) NOT NULL, -- ID de la "deuda" generado por Libélula
    estado_pago ENUM('Pendiente', 'Confirmado', 'Fallido', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    monto DECIMAL(10, 2) NOT NULL,
    metodo_pago VARCHAR(50), -- Ej. "QR", "Tarjeta", "Tigo Money" (si Libélula lo retorna)
    fecha_transaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    datos_callback JSON, -- Almacena la respuesta completa del callback de Libélula para auditoría
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);