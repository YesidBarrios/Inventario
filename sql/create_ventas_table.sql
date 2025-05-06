-- Script SQL para crear la tabla `ventas`

CREATE TABLE `ventas` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `producto_id` INT(11) NOT NULL,
    `cantidad_vendida` INT(11) NOT NULL,
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    `precio_total` DECIMAL(10,2) NOT NULL,
    `fecha_venta` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_id` INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;