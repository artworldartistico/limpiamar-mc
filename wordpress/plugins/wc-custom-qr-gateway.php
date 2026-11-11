<?php
/**
 * Plugin Name: WooCommerce Custom QR Payment Gateway
 * Plugin URI: https://github.com/artworldartistico
 * Description: Pasarela de pago personalizada con código QR, lógica de envíos y diseño visual integrado.
 * Version: 1.0.0
 * Author: Andrés Felipe Rodríguez Barbosa
 * Author URI: https://github.com/artworldartistico
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-custom-qr-gateway
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 *
 * @package WC_Custom_QR_Gateway
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Verificar si WooCommerce está activo
add_action('plugins_loaded', 'wc_custom_qr_gateway_init', 11);

function wc_custom_qr_gateway_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wc_custom_qr_gateway_missing_wc_notice');
        return;
    }

    // Incluir la clase del gateway
    add_filter('woocommerce_payment_gateways', 'wc_add_custom_qr_gateway');
    
    function wc_add_custom_qr_gateway($gateways) {
        $gateways[] = 'WC_Custom_QR_Gateway';
        return $gateways;
    }

    /**
     * Clase principal del Gateway de Pago QR
     */
    class WC_Custom_QR_Gateway extends WC_Payment_Gateway {

        /**
         * Constructor del gateway
         */
        public function __construct() {
            $this->id = 'custom_qr_gateway';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = __('Pago con QR', 'wc-custom-qr-gateway');
            $this->method_description = __('Permite a los clientes pagar escaneando un código QR con lógica de envíos personalizada.', 'wc-custom-qr-gateway');

            // Cargar la configuración
            $this->init_form_fields();
            $this->init_settings();

            // Definir las propiedades del gateway
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->image_url = $this->get_option('qr_image_url');
            $this->payment_icon_url = $this->get_option('payment_icon_url');
            $this->allowed_shipping_methods = $this->get_option('allowed_shipping_methods', array());
            $this->city_extra_costs = $this->get_option('city_extra_costs', '');
            
            // Configurar el icono del método de pago
            if (!empty($this->payment_icon_url)) {
                $this->icon = $this->payment_icon_url;
            }

            // Hooks
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            
            // Inyectar CSS en el checkout
            add_action('wp_head', array($this, 'inject_checkout_styles'));
            
            // Validar disponibilidad según método de envío
            add_filter('woocommerce_available_payment_gateways', array($this, 'conditional_payment_gateways'));
            
            // Aplicar cargo extra por ciudad en el carrito
            add_action('woocommerce_cart_calculate_fees', array($this, 'add_city_extra_fee_to_cart'));
        }

        /**
         * Inicializar los campos del formulario de configuración
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __('Habilitar/Deshabilitar', 'wc-custom-qr-gateway'),
                    'type'    => 'checkbox',
                    'label'   => __('Habilitar pago con QR', 'wc-custom-qr-gateway'),
                    'default' => 'no'
                ),
                'title' => array(
                    'title'       => __('Título', 'wc-custom-qr-gateway'),
                    'type'        => 'text',
                    'description' => __('Título que el usuario ve durante el checkout.', 'wc-custom-qr-gateway'),
                    'default'     => __('Pago con QR', 'wc-custom-qr-gateway'),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __('Descripción', 'wc-custom-qr-gateway'),
                    'type'        => 'textarea',
                    'description' => __('Descripción que el usuario ve durante el checkout.', 'wc-custom-qr-gateway'),
                    'default'     => __('Pague escaneando nuestro código QR con su aplicación bancaria.', 'wc-custom-qr-gateway'),
                    'desc_tip'    => true,
                ),
                'qr_image_url' => array(
                    'title'       => __('Imagen del Código QR', 'wc-custom-qr-gateway'),
                    'type'        => 'text',
                    'description' => __('URL de la imagen del código QR. Use el botón "Subir Imagen" abajo para seleccionar desde la biblioteca.', 'wc-custom-qr-gateway'),
                    'default'     => '',
                    'desc_tip'    => true,
                    'placeholder' => 'https://ejemplo.com/ruta/a/imagen-qr.png'
                ),
                'upload_qr_button' => array(
                    'title'       => __('Subir Imagen QR', 'wc-custom-qr-gateway'),
                    'type'        => 'button',
                    'description' => __('Haga clic para abrir la biblioteca multimedia y seleccionar la imagen del QR.', 'wc-custom-qr-gateway'),
                    'desc_tip'    => true,
                ),
                'payment_icon_url' => array(
                    'title'       => __('Logo del Método de Pago', 'wc-custom-qr-gateway'),
                    'type'        => 'text',
                    'description' => __('URL del logo que aparecerá al lado del método de pago en el checkout. Use el botón "Subir Logo" abajo para seleccionar desde la biblioteca.', 'wc-custom-qr-gateway'),
                    'default'     => '',
                    'desc_tip'    => true,
                    'placeholder' => 'https://ejemplo.com/ruta/a/logo.png'
                ),
                'upload_logo_button' => array(
                    'title'       => __('Subir Logo de Pago', 'wc-custom-qr-gateway'),
                    'type'        => 'button_logo',
                    'description' => __('Haga clic para abrir la biblioteca multimedia y seleccionar el logo del método de pago.', 'wc-custom-qr-gateway'),
                    'desc_tip'    => true,
                ),
                'allowed_shipping_methods' => array(
                    'title'       => __('Métodos de Envío Permitidos', 'wc-custom-qr-gateway'),
                    'type'        => 'multiselect',
                    'description' => __('Seleccione los métodos de envío que permiten este método de pago. Deje vacío para permitir todos.', 'wc-custom-qr-gateway'),
                    'default'     => array(),
                    'options'     => $this->get_shipping_methods(),
                    'desc_tip'    => true,
                    'class'       => 'wc-enhanced-select',
                ),
                'city_extra_costs' => array(
                    'title'       => __('Costos Extra por Ciudad', 'wc-custom-qr-gateway'),
                    'type'        => 'textarea',
                    'description' => __('Ingrese una ciudad y su costo extra por línea en formato: Ciudad:Costo (ejemplo: Bogotá:5000)', 'wc-custom-qr-gateway'),
                    'default'     => '',
                    'desc_tip'    => true,
                    'placeholder' => "Bogotá:5000\nMedellín:3000\nCali:4000"
                ),
            );
        }

        /**
         * Generar campo personalizado de botón para subir imagen
         */
        public function generate_button_html($key, $data) {
            $field_key = $this->get_field_key($key);
            $defaults  = array(
                'title'             => '',
                'disabled'          => false,
                'class'             => '',
                'css'               => '',
                'placeholder'       => '',
                'type'              => 'text',
                'desc_tip'          => false,
                'description'       => '',
                'custom_attributes' => array(),
            );

            $data = wp_parse_args($data, $defaults);
            
            // Obtener la URL actual de la imagen si existe
            $current_image = $this->get_option('qr_image_url');

            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
                    <?php echo $this->get_tooltip_html($data); ?>
                </th>
                <td class="forminp">
                    <fieldset>
                        <button type="button" class="button button-primary qr-upload-image-button">
                            <span class="dashicons dashicons-upload" style="line-height: 1.3; margin-right: 5px;"></span>
                            <?php _e('Seleccionar Imagen', 'wc-custom-qr-gateway'); ?>
                        </button>
                        <?php if (!empty($current_image)): ?>
                            <div class="qr-image-preview" style="margin-top: 15px; padding: 10px; background: #f8f8f8; border: 1px solid #ddd; border-radius: 5px; display: inline-block;">
                                <p style="margin: 0 0 10px 0; font-weight: 600; color: #555;">
                                    <span class="dashicons dashicons-format-image" style="line-height: 1;"></span>
                                    Vista previa actual:
                                </p>
                                <img src="<?php echo esc_url($current_image); ?>" style="max-width: 200px; height: auto; display: block; border: 2px solid #ddd; border-radius: 4px;">
                            </div>
                        <?php endif; ?>
                        <?php echo $this->get_description_html($data); ?>
                    </fieldset>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }

        /**
         * Generar campo personalizado de botón para subir logo
         */
        public function generate_button_logo_html($key, $data) {
            $field_key = $this->get_field_key($key);
            $defaults  = array(
                'title'             => '',
                'disabled'          => false,
                'class'             => '',
                'css'               => '',
                'placeholder'       => '',
                'type'              => 'text',
                'desc_tip'          => false,
                'description'       => '',
                'custom_attributes' => array(),
            );

            $data = wp_parse_args($data, $defaults);
            
            // Obtener la URL actual del logo si existe
            $current_logo = $this->get_option('payment_icon_url');

            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
                    <?php echo $this->get_tooltip_html($data); ?>
                </th>
                <td class="forminp">
                    <fieldset>
                        <button type="button" class="button button-primary qr-upload-logo-button">
                            <span class="dashicons dashicons-awards" style="line-height: 1.3; margin-right: 5px;"></span>
                            <?php _e('Seleccionar Logo', 'wc-custom-qr-gateway'); ?>
                        </button>
                        <?php if (!empty($current_logo)): ?>
                            <div class="qr-logo-preview" style="margin-top: 15px; padding: 10px; background: #f8f8f8; border: 1px solid #ddd; border-radius: 5px; display: inline-block;">
                                <p style="margin: 0 0 10px 0; font-weight: 600; color: #555;">
                                    <span class="dashicons dashicons-format-image" style="line-height: 1;"></span>
                                    Vista previa del logo:
                                </p>
                                <img src="<?php echo esc_url($current_logo); ?>" style="max-width: 100px; max-height: 50px; height: auto; display: block; border: 2px solid #ddd; border-radius: 4px;">
                            </div>
                        <?php endif; ?>
                        <?php echo $this->get_description_html($data); ?>
                    </fieldset>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }

        /**
         * Obtener todos los métodos de envío disponibles
         */
        private function get_shipping_methods() {
            $shipping_methods = array();
            $zones = WC_Shipping_Zones::get_zones();

            foreach ($zones as $zone) {
                foreach ($zone['shipping_methods'] as $method) {
                    $shipping_methods[$method->instance_id] = $zone['zone_name'] . ' - ' . $method->title;
                }
            }

            // Agregar zona "Resto del mundo"
            $zone = new WC_Shipping_Zone(0);
            foreach ($zone->get_shipping_methods() as $method) {
                if ($method->enabled === 'yes') {
                    $shipping_methods[$method->instance_id] = __('Resto del mundo', 'wc-custom-qr-gateway') . ' - ' . $method->title;
                }
            }

            return $shipping_methods;
        }

        /**
         * Cargar scripts de administración
         */
        public function enqueue_admin_scripts($hook) {
            // Solo cargar en la página de configuración de WooCommerce
            if ('woocommerce_page_wc-settings' !== $hook) {
                return;
            }

            // Verificar que estamos en nuestra sección
            if (!isset($_GET['section']) || $_GET['section'] !== $this->id) {
                return;
            }

            // Cargar la biblioteca de medios de WordPress
            wp_enqueue_media();

            // Registrar y cargar nuestro script inline
            wp_add_inline_script('jquery', "
                jQuery(document).ready(function($) {
                    var qrMediaUploader;
                    var qrLogoUploader;
                    
                    // Uploader para la imagen QR
                    $(document).on('click', '.qr-upload-image-button', function(e) {
                        e.preventDefault();
                        
                        if (qrMediaUploader) {
                            qrMediaUploader.open();
                            return;
                        }
                        
                        qrMediaUploader = wp.media.frames.qrMediaUploader = wp.media({
                            title: 'Seleccionar o Subir Imagen del QR',
                            button: {
                                text: 'Usar esta imagen'
                            },
                            multiple: false,
                            library: {
                                type: ['image']
                            }
                        });
                        
                        qrMediaUploader.on('select', function() {
                            var attachment = qrMediaUploader.state().get('selection').first().toJSON();
                            $('#woocommerce_custom_qr_gateway_qr_image_url').val(attachment.url);
                        });
                        
                        qrMediaUploader.open();
                    });
                    
                    // Uploader para el logo del método de pago
                    $(document).on('click', '.qr-upload-logo-button', function(e) {
                        e.preventDefault();
                        
                        if (qrLogoUploader) {
                            qrLogoUploader.open();
                            return;
                        }
                        
                        qrLogoUploader = wp.media.frames.qrLogoUploader = wp.media({
                            title: 'Seleccionar o Subir Logo del Método de Pago',
                            button: {
                                text: 'Usar este logo'
                            },
                            multiple: false,
                            library: {
                                type: ['image']
                            }
                        });
                        
                        qrLogoUploader.on('select', function() {
                            var attachment = qrLogoUploader.state().get('selection').first().toJSON();
                            $('#woocommerce_custom_qr_gateway_payment_icon_url').val(attachment.url);
                        });
                        
                        qrLogoUploader.open();
                    });
                });
            ");
        }

        /**
         * Inyectar CSS en el checkout
         */
        public function inject_checkout_styles() {
            if (!is_checkout()) {
                return;
            }
            ?>
            <style type="text/css">
                .qr-payment-container{width:100%;padding:20px}.qr-card{max-width:420px;margin:auto;background:#fff;border-radius:16px;padding:24px 20px 28px;text-align:center;box-shadow:0 15px 40px rgba(0,0,0,.12);animation:fadeIn .6s ease-in-out}.qr-instructions{font-size:1rem;color:#333;margin-bottom:20px;line-height:1.5}.qr-image-wrapper{background:#f8fafc;border-radius:12px;padding:16px;margin-bottom:22px;border:1px solid #e0e6ef}.qr-image{width:100%;max-width:260px;height:auto;display:block;margin:auto}.security-badge{display:inline-flex;align-items:center;gap:8px;font-size:.9rem;color:#0a8f3c;background:#e9f8ef;padding:8px 14px;border-radius:20px;font-weight:600}.lock-icon{font-size:1.1rem}@keyframes fadeIn{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:translateY(0)}}@media (max-width:480px){.qr-card{padding:20px 16px}.qr-instructions{font-size:.95rem}}
            </style>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    // Actualizar checkout cuando se selecciona el método de pago QR
                    $(document.body).on('change', 'input[name="payment_method"]', function() {
                        if ($(this).val() === 'custom_qr_gateway') {
                            $('body').trigger('update_checkout');
                        }
                    });
                });
            </script>
            <?php
        }

        /**
         * Mostrar campos de pago (el QR) en el checkout
         */
        public function payment_fields() {
            if ($this->description) {
                echo wpautop(wp_kses_post($this->description));
            }

            if (empty($this->image_url)) {
                echo '<p style="color: #e74c3c;">' . __('Error: No se ha configurado la imagen del código QR. Por favor, contacte al administrador.', 'wc-custom-qr-gateway') . '</p>';
                return;
            }
            ?>
            <section class="qr-payment-container">
                <div class="qr-card">
                    <p class="qr-instructions">Escanee nuestro QR con su aplicación de cualquiera de las entidades habilitadas.</p>
                    <div class="qr-image-wrapper">
                        <img src="<?php echo esc_url($this->image_url); ?>" alt="<?php esc_attr_e('Código QR para pago', 'wc-custom-qr-gateway'); ?>" class="qr-image">
                    </div>
                    <div class="security-badge">
                        <span class="lock-icon">🔒</span>
                        <span>Pago seguro verificado</span>
                    </div>
                </div>
            </section>
            <?php
        }

        /**
         * Validar disponibilidad del gateway según el método de envío
         */
        public function conditional_payment_gateways($available_gateways) {
            if (is_admin()) {
                return $available_gateways;
            }

            if (!isset($available_gateways[$this->id])) {
                return $available_gateways;
            }

            // Si no hay métodos de envío configurados, permitir todos
            if (empty($this->allowed_shipping_methods)) {
                return $available_gateways;
            }

            // Obtener el método de envío seleccionado
            $chosen_methods = WC()->session->get('chosen_shipping_methods');
            
            if (empty($chosen_methods)) {
                return $available_gateways;
            }

            $chosen_method = $chosen_methods[0];
            
            // Extraer el instance_id del método elegido (formato: method_id:instance_id)
            $chosen_parts = explode(':', $chosen_method);
            $chosen_instance_id = isset($chosen_parts[1]) ? $chosen_parts[1] : '';

            // Verificar si el método de envío está permitido
            if (!in_array($chosen_instance_id, $this->allowed_shipping_methods)) {
                unset($available_gateways[$this->id]);
            }

            return $available_gateways;
        }

        /**
         * Agregar cargo extra por ciudad al carrito (se muestra en tiempo real en el checkout)
         */
        public function add_city_extra_fee_to_cart($cart) {
            if (is_admin() && !defined('DOING_AJAX')) {
                return;
            }

            // Solo aplicar si este método de pago está seleccionado
            $chosen_payment_method = WC()->session->get('chosen_payment_method');
            if ($chosen_payment_method !== $this->id) {
                return;
            }

            // Verificar si hay configuración de costos extra
            if (empty($this->city_extra_costs)) {
                return;
            }

            // Obtener la ciudad de envío
            $shipping_city = WC()->customer->get_shipping_city();
            
            if (empty($shipping_city)) {
                return;
            }

            // Parsear los costos extra por ciudad
            $extra_cost = $this->get_city_extra_cost($shipping_city);

            // Si hay costo extra, agregarlo
            if ($extra_cost > 0) {
                $cart->add_fee(
                    sprintf(__('Cargo adicional - %s', 'wc-custom-qr-gateway'), $shipping_city),
                    $extra_cost,
                    true
                );
            }
        }

        /**
         * Obtener el costo extra para una ciudad específica
         */
        private function get_city_extra_cost($city) {
            if (empty($this->city_extra_costs) || empty($city)) {
                return 0;
            }

            // Parsear los costos extra por ciudad
            $city_costs = array();
            $lines = explode("\n", $this->city_extra_costs);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                
                $parts = explode(':', $line);
                if (count($parts) === 2) {
                    $city_name = trim($parts[0]);
                    $cost = floatval(trim($parts[1]));
                    $city_costs[strtolower($city_name)] = $cost;
                }
            }

            // Buscar la ciudad (case-insensitive)
            $city_lower = strtolower(trim($city));
            
            if (isset($city_costs[$city_lower])) {
                return $city_costs[$city_lower];
            }

            return 0;
        }

        /**
         * Procesar el pago
         */
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            // Marcar como pendiente de pago
            $order->update_status('on-hold', __('Esperando confirmación del pago con QR.', 'wc-custom-qr-gateway'));

            // Reducir stock
            wc_reduce_stock_levels($order_id);

            // Vaciar el carrito
            WC()->cart->empty_cart();

            // Retornar éxito
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }

        /**
         * Página de agradecimiento
         */
        public function thankyou_page($order_id) {
            if ($this->description) {
                echo '<p><strong>' . __('Instrucciones:', 'wc-custom-qr-gateway') . '</strong></p>';
                echo wpautop(wptexturize($this->description));
            }
            
            echo '<p>' . __('Su pedido está pendiente de confirmación. Una vez que realice el pago, envíenos el comprobante para procesar su orden.', 'wc-custom-qr-gateway') . '</p>';
        }
    }
}

/**
 * Aviso si WooCommerce no está activo
 */
function wc_custom_qr_gateway_missing_wc_notice() {
    echo '<div class="error"><p><strong>' . sprintf(
        esc_html__('El plugin "WooCommerce Custom QR Payment Gateway" requiere que WooCommerce esté instalado y activado. Puedes descargar %s aquí.', 'wc-custom-qr-gateway'),
        '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>'
    ) . '</strong></p></div>';
}

/**
 * Declarar compatibilidad con HPOS (High-Performance Order Storage)
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
