# Evidencias visuales

## Incluidas (anonimizadas)

| Archivo | Sección del dossier | Qué demuestra | Pie sugerido | Anonimización aplicada | Tipo |
|---|---|---|---|---|---|
| `arquitectura/01-estructura-astra-child.png` | 03 | Tema hijo con sus tres elementos | Tema hijo en el servidor | Usuario de cuenta y carpetas de plugins ocultos | Obligatoria |
| `checkout/02-config-pasarela-qr.png` | 06 | Ajustes del método QR y selector de imagen | Ajustes del método de pago | QR y número de contacto ocultos; sin barra ni menú | Obligatoria |
| `checkout/03-checkout-pago-qr-comprobante.png` | 06 | Método QR con logo y tarjeta en el checkout | Método en el checkout | QR ocultado | Obligatoria |
| `resultados/04-pedido-recibido.png` | 09 | Pedido de prueba procesado | Pedido de prueba recibido | Número de pedido ocultado | Obligatoria |
| `comprobantes/05-correo-vendedor-enlace.png` | 05 | Enlace al comprobante en el correo del vendedor | Correo del vendedor | Recorte al cuerpo; nombre y pedido ocultos | Obligatoria |
| `resultados/06-error-checkout.png` | 05 | Error original | Mensaje que veía el cliente | Recorte al aviso | Obligatoria |
| `comprobantes/07-correo-comprador-enlace.png` | 05 | Enlace en el correo del comprador | Correo del comprador | Recorte; nombre y pedido ocultos; sin direcciones | Obligatoria |
| `checkout/08-checkout-transferencia-comprobante.png` | 05 | Campo obligatorio de comprobante | Campo obligatorio en el checkout | Cuenta y titular ocultos | Obligatoria |
| `productos/09-producto-plantilla-escritorio.png` | 04 | Plantilla de producto | Plantilla en escritorio | Teléfonos de la etiqueta ocultos | Obligatoria |

## Pendientes (no se inventan)

| Evidencia | Sección | Anonimizar | Tipo |
|---|---|---|---|
| Producto en móvil | 04 | Sí, si aparecen datos de contacto | Obligatoria |
| Varios productos con datos distintos | 04 | No | Complementaria |
| Archivos del tema (`functions.php`, `style.css`, plantilla) en el editor | 03 | Rutas | Complementaria (el código ya está en el repositorio) |
| Extracto del `debug.log` | 05 | Sí: rutas, correos, IDs | Complementaria |
| Snippet original con el error | 05 | No | Complementaria |
| Configuración del campo de archivo en WOOCCM | 05 | No | Obligatoria |
| Listado de metadatos del correo de diagnóstico | 05 | Sí: dirección, teléfono y correo del comprador de prueba | Complementaria |
| Archivo en la biblioteca multimedia / `wooccm_uploads` | 05 | Sí: nombre del archivo | Complementaria |
| Pagos → lista con la columna ID | 07 | Sí, si muestra datos sensibles | Complementaria |
| Pruebas con otros métodos de pago | 09 | Sí | Obligatoria |
| Envíos permitidos y costos por ciudad | 06 | No | Obligatoria si se quiere afirmar su funcionamiento |
| Configuración de LiteSpeed Cache (solo si se aplicó) | 08 | No | Complementaria |

## Regla de publicación
No publicar comprobantes reales, QR escaneables, cuentas bancarias, números de contacto, nombres o correos de compradores, números de pedido ni nombres de archivos.
