# Implementación

Probar primero en un entorno de staging o copia.

## 1. Tema hijo
1. Subir la carpeta `wordpress/astra-child/` a `wp-content/themes/`.
2. Apariencia → Temas → activar **Astra Child**. Astra debe estar instalado.

## 2. Plantilla de producto
El archivo `astra-child/woocommerce/content-single-product.php` sobrescribe la plantilla del núcleo. Si se usó una plantilla de producto en Elementor Theme Builder, desactivar su condición de visualización para evitar conflicto. Purgar la caché y abrir varios productos.

## 3. Snippets (Code Snippets)
1. Fragmentos de código → Añadir nuevo.
2. Pegar el contenido de `comprobante-de-pago.php` (sin la etiqueta `<?php` inicial, que el plugin añade) y activarlo en todo el sitio.
3. Repetir con `cambios-en-checkout.php`, sustituyendo los marcadores `example.com` por las imágenes reales.
4. Nota: el nombre de campo del comprobante se detecta por las subcadenas `wooccm` o `file`.

## 4. Campo de comprobante
En WooCommerce Checkout Manager, un campo de tipo archivo, obligatorio, en el checkout. La captura `08` muestra el resultado.

## 5. Plugin QR
1. Comprimir `wc-custom-qr-gateway.php` en ZIP.
2. Plugins → Añadir nuevo → Subir plugin → Activar (requiere WooCommerce).
3. WooCommerce → Ajustes → Pagos → **Pago con QR**: habilitar, título, descripción, imagen del QR, logo y, opcionalmente, métodos de envío permitidos y costos por ciudad (`Ciudad:Costo`, una por línea).
4. Hacer un pedido de prueba.

## Historial de iteraciones del plugin
Según la conversación de desarrollo: el botón de subir imagen no respondía y se corrigió cargando `wp_enqueue_media()` y usando eventos delegados; se añadió el campo de logo; el cargo por ciudad se movió a `woocommerce_cart_calculate_fees` para que se vea en el resumen del pedido. La versión publicada es la final.

## Caché
Tras instalar: LiteSpeed Cache → *Purge All*. Ver `mantenimiento.md`.
