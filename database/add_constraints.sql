-- Agregar Constraints de Integridad Referencial Faltantes
-- Ejecutar después de verificar que los datos son consistentes

USE inventario_supermercado;

-- Verificar datos huérfanos antes de agregar constraints
SELECT 'Productos sin proveedor válido:' as info, COUNT(*) as cantidad
FROM productos p
LEFT JOIN proveedores prov ON p.proveedor_id = prov.id
WHERE p.proveedor_id IS NOT NULL AND prov.id IS NULL;

SELECT 'Productos sin unidad válida:' as info, COUNT(*) as cantidad
FROM productos p
LEFT JOIN unidades u ON p.unidad_id = u.id
WHERE p.unidad_id IS NOT NULL AND u.id IS NULL;

SELECT 'Compras sin proveedor válido:' as info, COUNT(*) as cantidad
FROM compras c
LEFT JOIN proveedores prov ON c.proveedor_id = prov.id
WHERE c.proveedor_id IS NOT NULL AND prov.id IS NULL;

-- Si hay datos huérfanos, corregirlos antes de continuar
-- UPDATE productos SET proveedor_id = NULL WHERE proveedor_id NOT IN (SELECT id FROM proveedores);
-- UPDATE productos SET unidad_id = NULL WHERE unidad_id NOT IN (SELECT id FROM unidades);
-- UPDATE compras SET proveedor_id = NULL WHERE proveedor_id NOT IN (SELECT id FROM proveedores);

-- Agregar constraints de integridad referencial
ALTER TABLE productos
ADD CONSTRAINT fk_productos_proveedor
FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE productos
ADD CONSTRAINT fk_productos_unidad
FOREIGN KEY (unidad_id) REFERENCES unidades(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE compras
ADD CONSTRAINT fk_compras_proveedor
FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE compras_productos
ADD CONSTRAINT fk_compras_productos_compra
FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE compras_productos
ADD CONSTRAINT fk_compras_productos_producto
FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ventas
ADD CONSTRAINT fk_ventas_producto
FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ventas
ADD CONSTRAINT fk_ventas_usuario
FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Agregar constraints de validación de datos
ALTER TABLE productos
ADD CONSTRAINT chk_precio_positivo CHECK (precio >= 0),
ADD CONSTRAINT chk_costo_positivo CHECK (costo >= 0),
ADD CONSTRAINT chk_stock_no_negativo CHECK (stock >= 0),
ADD CONSTRAINT chk_stock_minimo_no_negativo CHECK (stock_minimo >= 0);

ALTER TABLE compras
ADD CONSTRAINT chk_total_compra_positivo CHECK (total_compra >= 0);

ALTER TABLE compras_productos
ADD CONSTRAINT chk_cantidad_positiva CHECK (cantidad > 0),
ADD CONSTRAINT chk_costo_unitario_positivo CHECK (costo_unitario >= 0);

ALTER TABLE ventas
ADD CONSTRAINT chk_cantidad_vendida_positiva CHECK (cantidad_vendida > 0),
ADD CONSTRAINT chk_precio_unitario_positivo CHECK (precio_unitario >= 0),
ADD CONSTRAINT chk_precio_total_positivo CHECK (precio_total >= 0),
ADD CONSTRAINT chk_costo_total_no_negativo CHECK (costo_total >= 0);

-- Agregar índices únicos donde corresponda
ALTER TABLE usuarios ADD CONSTRAINT uk_username UNIQUE (username);
ALTER TABLE proveedores ADD CONSTRAINT uk_proveedor_email UNIQUE (email);
ALTER TABLE unidades ADD CONSTRAINT uk_unidad_abreviatura UNIQUE (abreviatura);

-- Agregar valores por defecto faltantes
ALTER TABLE productos
MODIFY COLUMN costo DECIMAL(10,2) DEFAULT 0.00,
MODIFY COLUMN stock_minimo INT DEFAULT 10,
MODIFY COLUMN deleted BOOLEAN DEFAULT FALSE;

ALTER TABLE ventas
MODIFY COLUMN costo_total DECIMAL(10,2) DEFAULT 0.00;

-- Verificar constraints agregados
SELECT
    TABLE_NAME,
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'inventario_supermercado'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

-- Verificar foreign keys
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'inventario_supermercado'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;