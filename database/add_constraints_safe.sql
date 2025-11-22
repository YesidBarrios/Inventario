-- Agregar Constraints de Integridad Referencial de Forma Segura
-- Este script verifica si las constraints ya existen antes de crearlas

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

-- Solo agregar constraints si no existen
SET @constraint_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = 'inventario_supermercado'
    AND TABLE_NAME = 'productos'
    AND CONSTRAINT_NAME = 'fk_productos_proveedor'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE productos ADD CONSTRAINT fk_productos_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "fk_productos_proveedor ya existe" as status;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Repetir para otras constraints
SET @constraint_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = 'inventario_supermercado'
    AND TABLE_NAME = 'productos'
    AND CONSTRAINT_NAME = 'fk_productos_unidad'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE productos ADD CONSTRAINT fk_productos_unidad FOREIGN KEY (unidad_id) REFERENCES unidades(id) ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "fk_productos_unidad ya existe" as status;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Continuar con las demás constraints...
SET @constraint_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = 'inventario_supermercado'
    AND TABLE_NAME = 'compras'
    AND CONSTRAINT_NAME = 'fk_compras_proveedor'
);

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE compras ADD CONSTRAINT fk_compras_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "fk_compras_proveedor ya existe" as status;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar constraints existentes
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