# Pruebas y validaciones

| Prueba | Estado | Evidencia |
|---|---|---|
| Pedido con Pago con QR hasta la página de confirmación | Comprobado | `04` |
| Correo del vendedor con enlace al comprobante | Comprobado | `05` |
| Correo del comprador con enlace al comprobante | Comprobado | `07` |
| Campo obligatorio de comprobante en el checkout | Comprobado | `08`, `03` |
| Método QR visible en el checkout con logo y tarjeta | Comprobado | `03` |
| Configuración del método QR (imagen, subida) | Comprobado | `02` |
| Plantilla de producto en escritorio | Comprobado | `09` |
| Adjunto del archivo en el correo del vendedor | Observado en la bandeja (indicador de adjunto); no hay captura en el repositorio | — |
| Pedidos con otros métodos de pago | **Pendiente** | — |
| Plantilla de producto en móvil y con varios productos | **Pendiente** | — |
| Métodos de envío permitidos (plugin QR) | **Pendiente** (implementado en código) | — |
| Costos extra por ciudad (plugin QR) | **Pendiente** (implementado en código) | — |
| Comprobante con más de un archivo | **Pendiente** (limitación conocida) | — |

Los archivos PHP se publican tal como funcionan en producción; no se ejecutó un analizador estático sobre ellos para este repositorio.
