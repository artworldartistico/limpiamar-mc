<?php
/**
 * Snippet (plugin Code Snippets): "Comprobante de pago"
 *
 * Qué hace:
 *  1. Muestra un enlace al comprobante de pago en los correos de WooCommerce
 *     (vendedor y comprador).
 *  2. Adjunta el archivo físico al correo "Nuevo pedido" del vendedor.
 *  3. Resuelve la URL del archivo a partir de los IDs de adjunto que guarda
 *     WooCommerce Checkout Manager (WOOCCM) en los metadatos del pedido
 *     (por ejemplo, _billing_wooccmNN con un valor como "ID1,ID2").
 *
 * Limitaciones conocidas (ver docs/diagnostico.md):
 *  - Solo se recupera el PRIMER ID válido; si el cliente sube varios archivos,
 *    los demás no se enlazan ni se adjuntan.
 *  - La coincidencia por clave usa las subcadenas "wooccm" o "file".
 *  - El adjunto solo se agrega en el correo con id 'new_order'.
 */

/**
 * 1. Mostrar enlace del comprobante en los correos (Vendedor y Comprador)
 */
add_action( 'woocommerce_email_order_meta', 'mostrar_comprobante_por_id_wooccm', 20, 3 );
function mostrar_comprobante_por_id_wooccm( $order, $sent_to_admin, $plain_text ) {
    $url_comprobante = obtener_url_desde_wooccm_meta( $order );

    if ( ! empty( $url_comprobante ) ) {
        $titulo = $sent_to_admin ? 'Comprobante de pago cargado por el cliente:' : 'Tu comprobante de pago enviado:';

        if ( $plain_text ) {
            echo "\n" . $titulo . " " . $url_comprobante . "\n";
        } else {
            echo '<div style="margin-top: 20px; padding: 12px 15px; background-color: #f8f9fa; border-left: 4px solid #7eb338; border-radius: 4px;">';
            echo '<p style="margin: 0; font-size: 14px; color: #333;"><strong>' . esc_html( $titulo ) . '</strong></p>';
            echo '<p style="margin: 8px 0 0 0;"><a href="' . esc_url( $url_comprobante ) . '" target="_blank" style="display: inline-block; padding: 6px 12px; background-color: #7eb338; color: #ffffff; text-decoration: none; border-radius: 3px; font-weight: bold; font-size: 13px;">🔍 Ver / Descargar Comprobante</a></p>';
            echo '</div><br>';
        }
    }
}

/**
 * 2. Adjuntar el archivo físico al correo del vendedor (Administrador)
 */
add_filter( 'woocommerce_email_attachments', 'adjuntar_comprobante_por_id_vendedor', 10, 3 );
function adjuntar_comprobante_por_id_vendedor( $attachments, $email_id, $order ) {
    if ( in_array( $email_id, array( 'new_order' ), true ) && is_a( $order, 'WC_Order' ) ) {
        $url_comprobante = obtener_url_desde_wooccm_meta( $order );

        if ( ! empty( $url_comprobante ) ) {
            $upload_dir = wp_upload_dir();
            $ruta_archivo = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url_comprobante );

            if ( file_exists( $ruta_archivo ) ) {
                $attachments[] = $ruta_archivo;
            }
        }
    }
    return $attachments;
}

/**
 * Función que extrae la URL a partir del ID guardado en _billing_wooccm11
 */
function obtener_url_desde_wooccm_meta( $order ) {
    if ( ! is_a( $order, 'WC_Order' ) ) {
        return false;
    }

    // Buscamos específicamente el campo de wooccm o cualquier meta que contenga IDs numéricos de archivos
    foreach ( $order->get_meta_data() as $meta ) {
        $data  = $meta->get_data();
        $key   = $data['key'];
        $value = $data['value'];

        // Si la clave es la de wooccm o contiene la palabra file/wooccm
        if ( strpos( $key, 'wooccm' ) !== false || strpos( $key, 'file' ) !== false ) {
            // El valor puede ser un número o una lista separada por comas (ej: "2529,2530")
            $ids = explode( ',', (string) $value );
            foreach ( $ids as $id ) {
                $id = trim( $id );
                if ( is_numeric( $id ) && $id > 0 ) {
                    $url = wp_get_attachment_url( $id );
                    if ( $url ) {
                        return $url; // Retorna la URL real del archivo subido
                    }
                }
            }
        }
    }

    return false;
}
