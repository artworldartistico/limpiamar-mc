# Diagnóstico por incidente

Convención: **Confirmado** = demostrado en un archivo o captura. **Hipótesis** = sospecha sin evidencia. **Reportado** = dicho por el cliente o por el registro de errores, sin evidencia adjunta.

## Incidente 1 · Datos del producto que no coincidían

- **Contexto:** la plantilla de producto se construía con Elementor Theme Builder.
- **Síntomas:** precio, imágenes, galería y descripción de otro producto. *Reportado.*
- **Impacto:** quejas de clientes y devoluciones. *Reportado.*
- **Diagnóstico:** revisión de la exportación de la plantilla de Elementor.
- **Causa raíz:** *Confirmado:* una pestaña de descripción alimentada por una plantilla incrustada fija, no por el producto actual (descripciones repetidas entre productos). *Hipótesis:* caché de página sirviendo el contenido de un producto en otra URL, sin evidencia (no hay cabeceras ni registros).
- **Solución:** plantilla en el tema hijo que lee `$product`; carrito nativo.
- **Procedimiento:** ver `implementacion.md`, pasos 1 y 2.
- **Componentes:** Astra Child, WooCommerce, Quick Buy Now.
- **Evidencia:** captura de la plantilla funcionando (`09`). Sin capturas del estado anterior.
- **Resultado:** la plantilla muestra los datos del producto cargado. Falta validar en móvil y con varios productos.
- **Prevención:** no incrustar plantillas fijas para datos de producto; revisar productos tras cada actualización; purgar caché.

## Incidente 2 · Comprobantes de pago que no llegaban

- **Contexto:** los clientes suben el comprobante en el checkout con WooCommerce Checkout Manager.
- **Síntomas:** el vendedor no recibía el comprobante en el correo.
- **Impacto:** no podía verificar el pago desde el pedido.
- **Diagnóstico:** un snippet temporal listó los metadatos del pedido en un correo de prueba.
- **Causa raíz (confirmada):** el campo guarda **IDs de adjuntos** (un valor como `ID1,ID2`), no una URL. Las versiones previas del snippet buscaban texto o URLs.
- **Solución:** resolver la URL con `wp_get_attachment_url()`, mostrar el enlace en ambos correos y adjuntar el archivo al correo del vendedor si existe en disco.
- **Componentes:** WOOCCM, Code Snippets, `woocommerce_email_order_meta`, `woocommerce_email_attachments`.
- **Evidencia:** correos de vendedor (`05`) y comprador (`07`); campo obligatorio (`08`). El aviso de licencia del plugin es *reportado*, sin captura.
- **Resultado:** el enlace llega a ambos correos.
- **Limitaciones/prevención:** solo se usa el primer ID; revisar el acceso público a `/wp-content/uploads/wooccm_uploads/`.

## Incidente 3 · Error crítico al procesar pedidos

- **Contexto:** mensaje "Se produjo un error al procesar tu pedido…" al finalizar la compra.
- **Síntomas:** el error aparecía con cualquier método de pago (`06`).
- **Impacto:** pedidos no completados.
- **Diagnóstico:** `WP_DEBUG` y `WP_DEBUG_LOG` activados; revisión de `wp-content/debug.log`.
- **Hallazgo (reportado):** `PHP Fatal error: Uncaught TypeError: strpos(): Argument #1 ($haystack) must be of type string, array given`, originado en el snippet *Comprobante de pago*, función `agregar_comprobante_wooccm`, al notificar el nuevo pedido.
- **Causa raíz:** el snippet aplicaba `strpos()` a todos los metadatos, algunos de los cuales son arrays. No se atribuye a la pasarela.
- **Solución:** validar tipos y luego rediseñar la extracción (incidente 2).
- **Evidencia:** captura del error (`06`) y pedido de prueba posterior (`04`). El extracto del `debug.log` no se publica.
- **Resultado:** el pedido de prueba con Pago con QR se procesó.
- **Prevención:** validar tipos en código que lee metadatos; probar los snippets en staging; mantener el registro de errores disponible durante el diagnóstico.

## Incidente 4 · Mantenimiento, rendimiento y caché

- **Contexto:** lentitud y elementos que no cargaban. *Reportado.*
- **Causa:** falta de mantenimiento continuo. *Reportado.*
- **Solución:** procedimiento en `mantenimiento.md`.
- **Evidencia:** no hay métricas ni capturas de configuración de caché.
- **Nota sobre LiteSpeed Cache:** no hay opción literal para regenerar `/producto/*` con frecuencia propia; el control es TTL, purgas y exclusiones.
