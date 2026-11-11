<?php
/**
 * Snippet (plugin Code Snippets): "Cambios en checkout"
 *
 * Qué hace:
 *  1. Agrega una columna "ID" a la tabla de WooCommerce > Ajustes > Pagos, para
 *     conocer el identificador de cada pasarela.
 *  2. Asigna un icono a cada método de pago mediante el filtro
 *     'woocommerce_gateway_icon'.
 *
 * Nota de publicación: las URLs de los iconos se reemplazaron por marcadores
 * (example.com). En producción apuntan a imágenes de la biblioteca multimedia
 * de la tienda. Se omitieron las ramas de ejemplo para pasarelas que no se usan.
 * La pasarela "Pago con QR" (plugin propio) define su logo desde su propio campo.
 */

// 1) Columna ID en la tabla de pasarelas ---------------------------------
add_filter( 'woocommerce_payment_gateways_setting_columns', 'rudr_add_payment_method_column' );

function rudr_add_payment_method_column( $default_columns ) {

	$default_columns = array_slice( $default_columns, 0, 2 ) + array( 'id' => 'ID' ) + array_slice( $default_columns, 2, 3 );
	return $default_columns;

}

// woocommerce_payment_gateways_setting_column_{COLUMN ID}
add_action( 'woocommerce_payment_gateways_setting_column_id', 'rudr_populate_gateway_column' );

function rudr_populate_gateway_column( $gateway ) {

	echo '<td style="width:10%">' . $gateway->id . '</td>';

}

// 2) Iconos por método de pago --------------------------------------------
add_filter( 'woocommerce_gateway_icon', 'custom_payment_gateway_icons', 10, 2 );
function custom_payment_gateway_icons( $icon, $gateway_id ){

    foreach( WC()->payment_gateways->get_available_payment_gateways() as $gateway )
        if( $gateway->id == $gateway_id ){
            $title = $gateway->get_title();
            break;
        }

    // Asignar (o no) un icono personalizado según el ID de la pasarela
    if($gateway_id == 'bacs')
        $icon = '<img src="' . WC_HTTPS::force_https_url( "https://example.com/wp-content/uploads/logo-banco.png" ) . '" alt="' . esc_attr( $title ) . '" />';
	elseif( $gateway_id == 'nequipay' )
        $icon = '<img src="' . WC_HTTPS::force_https_url( "https://example.com/wp-content/uploads/logo-nequi.png" ) . '" alt="' . esc_attr( $title ) . '" />';
    elseif( $gateway_id == 'ppec_paypal' || 'paypal' )
        return $icon;

    return $icon;
}
