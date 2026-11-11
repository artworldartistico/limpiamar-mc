# Mantenimiento, rendimiento y caché

No se midió rendimiento antes ni después; este documento define el procedimiento.

## Antes de actualizar
- Copia de seguridad de archivos y base de datos.
- Idealmente, probar en staging.

## Orden sugerido
1. WordPress.
2. WooCommerce.
3. Astra (tema padre).
4. Plugins.
5. Revisar WooCommerce → Estado → Sistema por plantillas sobrescritas desactualizadas.

## Después de actualizar
- LiteSpeed Cache: **Purge All** (barra superior de administración).
- Abrir varios productos: título, precio, galería, descripciones y botones.
- Pedido de prueba con comprobante; revisar los dos correos.
- Revisar `wp-content/debug.log` si se activó el registro, y desactivarlo al terminar.

## LiteSpeed Cache
- No existe una opción literal para regenerar `/producto/*` con frecuencia propia.
- Control disponible: TTL de la caché pública, purgas y exclusiones.
- En *Cache → Exclude*: parámetros de compra (`add-to-cart`, `wc-quick-buy-now`) y páginas transaccionales (carrito, finalizar compra, mi cuenta).
- La integración con WooCommerce purga la página de un producto al editarlo; verificarlo en cada instalación.
- Los nombres exactos de las pestañas pueden variar según la versión del plugin.

## Limpieza
Eliminar temas y plugins sin uso reduce la superficie de actualización.
