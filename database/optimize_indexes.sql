-- Optimización de Base de Datos - Índices Estratégicos
-- Ejecutar después de hacer backup de la base de datos

USE inventario_supermercado;

-- Índices para productos (consultas más frecuentes)
CREATE INDEX idx_productos_deleted_stock ON productos(deleted, stock);
CREATE INDEX idx_productos_nombre ON productos(nombre(50));
CREATE INDEX idx_productos_proveedor ON productos(proveedor_id);
CREATE INDEX idx_productos_unidad ON productos(unidad_id);
CREATE INDEX idx_productos_fecha_creacion ON productos(fecha_creacion);

-- Índices para ventas (consultas por fecha y producto)
CREATE INDEX idx_ventas_fecha ON ventas(fecha_venta);
CREATE INDEX idx_ventas_producto ON ventas(producto_id);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_ventas_transaccion ON ventas(transaccion_id);

-- Índices para compras
CREATE INDEX idx_compras_fecha ON compras(fecha_compra);
CREATE INDEX idx_compras_proveedor ON compras(proveedor_id);

-- Índices para compras_productos
CREATE INDEX idx_compras_productos_compra ON compras_productos(compra_id);
CREATE INDEX idx_compras_productos_producto ON compras_productos(producto_id);

-- Índices para usuarios
CREATE INDEX idx_usuarios_rol ON usuarios(rol);

-- Índices compuestos para consultas complejas
CREATE INDEX idx_ventas_fecha_producto ON ventas(fecha_venta, producto_id);
CREATE INDEX idx_ventas_fecha_usuario ON ventas(fecha_venta, usuario_id);
CREATE INDEX idx_productos_stock_minimo ON productos(stock, stock_minimo);

-- Índice para búsqueda de texto completo en productos
ALTER TABLE productos ADD FULLTEXT idx_productos_texto(nombre, descripcion);

-- Verificar índices creados
SHOW INDEX FROM productos;
SHOW INDEX FROM ventas;
SHOW INDEX FROM compras;
SHOW INDEX FROM compras_productos;