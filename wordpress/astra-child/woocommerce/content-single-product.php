<?php
/**
 * Single Product personalizado — Limpia Mar MC
 *
 * Ubicación:
 * /wp-content/themes/astra-child/woocommerce/content-single-product.php
 *
 * IMPORTANTE:
 * - NO se carga header.php ni footer.php aquí.
 * - Astra/WooCommerce se encargan de Header, Main Navigator y Footer.
 * - NO ejecutamos woocommerce_before_single_product_summary()
 *   para evitar duplicar la galería.
 * - NO ejecutamos woocommerce_after_single_product_summary()
 *   para evitar duplicar tabs/productos relacionados.
 * - El botón "Comprar ahora" lo genera el plugin Quick Buy Now Button
 *   a través del hook woocommerce_after_add_to_cart_button.
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Seguridad:
 * si el producto requiere contraseña, respetamos la protección.
 */
if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

/**
 * Aseguramos que $product corresponde al producto actual.
 */
$product = wc_get_product( get_the_ID() );

if ( ! $product ) {
	return;
}

/**
 * Datos del producto.
 */
$product_id = $product->get_id();

$title = $product->get_name();

$short_description = $product->get_short_description();

$description = $product->get_description();

$sku = $product->get_sku();

$average_rating = $product->get_average_rating();

$review_count = $product->get_review_count();

$price_html = $product->get_price_html();

$main_image_id = $product->get_image_id();

$gallery_image_ids = $product->get_gallery_image_ids();

/**
 * Categorías.
 */
$categories = wc_get_product_category_list(
	$product_id,
	', ',
	'',
	''
);

/**
 * Imagen principal.
 */
$main_image_url = $main_image_id
	? wp_get_attachment_image_url( $main_image_id, 'woocommerce_single' )
	: wc_placeholder_img_src( 'woocommerce_single' );

/**
 * Alt de la imagen principal.
 */
$main_image_alt = $main_image_id
	? get_post_meta( $main_image_id, '_wp_attachment_image_alt', true )
	: '';

if ( ! $main_image_alt ) {
	$main_image_alt = $title;
}

/**
 * Construimos el array completo de imágenes:
 * imagen principal + galería.
 */
$product_images = array();

if ( $main_image_id ) {
	$product_images[] = array(
		'id'  => $main_image_id,
		'url' => wp_get_attachment_image_url( $main_image_id, 'woocommerce_single' ),
		'thumb' => wp_get_attachment_image_url( $main_image_id, 'woocommerce_thumbnail' ),
		'alt' => $main_image_alt,
	);
}

foreach ( $gallery_image_ids as $image_id ) {

	$image_url = wp_get_attachment_image_url(
		$image_id,
		'woocommerce_single'
	);

	$thumb_url = wp_get_attachment_image_url(
		$image_id,
		'woocommerce_thumbnail'
	);

	$image_alt = get_post_meta(
		$image_id,
		'_wp_attachment_image_alt',
		true
	);

	if ( ! $image_alt ) {
		$image_alt = $title;
	}

	if ( $image_url ) {
		$product_images[] = array(
			'id'    => $image_id,
			'url'   => $image_url,
			'thumb' => $thumb_url ? $thumb_url : $image_url,
			'alt'   => $image_alt,
		);
	}
}

/**
 * Si el producto no tiene imagen, usamos placeholder.
 */
if ( empty( $product_images ) ) {
	$product_images[] = array(
		'id'    => 0,
		'url'   => wc_placeholder_img_src( 'woocommerce_single' ),
		'thumb' => wc_placeholder_img_src( 'woocommerce_thumbnail' ),
		'alt'   => $title,
	);
}

/**
 * URL de checkout para la barra móvil.
 */
$checkout_url = wc_get_checkout_url();

/**
 * URL del producto.
 */
$product_url = get_permalink( $product_id );

/**
 * -------------------------------------------------------------------------
 * PRODUCTO
 * -------------------------------------------------------------------------
 */
?>

<div
	id="product-<?php echo esc_attr( $product_id ); ?>"
	<?php wc_product_class( 'lmc-single-product', $product ); ?>
>


<!-- ================================================================
     CONTENIDO PRINCIPAL
     ================================================================ -->

<main class="lmc-product-main">

	<!-- ===================== BREADCRUMB ===================== -->

	<div class="lmc-breadcrumb">

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			Inicio
		</a>

		<span>/</span>

		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
			Productos
		</a>

		<span>/</span>

		<span class="lmc-breadcrumb-current">
			<?php echo wp_kses_post( $categories ); ?>
		</span>

	</div>


	<!-- ===================== PRODUCTO ===================== -->

	<section class="lmc-product">

		<!-- ========================================================
		     GALERÍA
		     ======================================================== -->

		<div class="lmc-gallery-column">

			<div class="lmc-gallery-main">

				<?php if ( count( $product_images ) > 1 ) : ?>

					<button
						type="button"
						class="lmc-gallery-arrow lmc-gallery-prev"
						aria-label="Imagen anterior"
					>
						‹
					</button>

				<?php endif; ?>


				<img
					id="lmc-main-product-image-<?php echo esc_attr( $product_id ); ?>"
					class="lmc-main-image"
					src="<?php echo esc_url( $product_images[0]['url'] ); ?>"
					alt="<?php echo esc_attr( $product_images[0]['alt'] ); ?>"
					loading="eager"
				>


				<?php if ( count( $product_images ) > 1 ) : ?>

					<button
						type="button"
						class="lmc-gallery-arrow lmc-gallery-next"
						aria-label="Siguiente imagen"
					>
						›
					</button>

				<?php endif; ?>

			</div>


			<!-- ================= MINIATURAS ================= -->

			<?php if ( count( $product_images ) > 1 ) : ?>

				<div class="lmc-thumbs">

					<?php foreach ( $product_images as $index => $image ) : ?>

						<button
							type="button"
							class="lmc-thumb <?php echo 0 === $index ? 'active' : ''; ?>"
							data-index="<?php echo esc_attr( $index ); ?>"
							data-image="<?php echo esc_url( $image['url'] ); ?>"
							data-alt="<?php echo esc_attr( $image['alt'] ); ?>"
							aria-label="<?php echo esc_attr( 'Ver imagen ' . ( $index + 1 ) ); ?>"
						>

							<img
								src="<?php echo esc_url( $image['thumb'] ); ?>"
								alt="<?php echo esc_attr( $image['alt'] ); ?>"
								loading="lazy"
							>

						</button>

					<?php endforeach; ?>

				</div>

			<?php endif; ?>

		</div>


		<!-- ========================================================
		     INFORMACIÓN DEL PRODUCTO
		     ======================================================== -->

		<div class="lmc-product-info">

			<!-- Categoría -->

			<div class="lmc-category">

				<?php echo wp_kses_post( $categories ); ?>

			</div>


			<!-- Título -->

			<h1 class="lmc-product-title">

				<?php echo esc_html( $title ); ?>

			</h1>


			<!-- Valoración -->

			<div class="lmc-rating">

				<?php if ( $review_count > 0 ) : ?>

					<span class="lmc-stars" aria-label="<?php echo esc_attr( $average_rating . ' de 5 estrellas' ); ?>">

						<?php echo wc_get_rating_html( $average_rating, $review_count ); ?>

					</span>

					<a
						href="#lmc-reviews"
						class="lmc-review-link"
					>
						<?php
						echo esc_html(
							sprintf(
								_n(
									'%d valoración',
									'%d valoraciones',
									$review_count,
									'woocommerce'
								),
								$review_count
							)
						);
						?>
					</a>

				<?php else : ?>

					<span class="lmc-stars lmc-no-rating">
						★★★★★
					</span>

					<a
						href="#lmc-reviews"
						class="lmc-review-link"
					>
						Sé el primero en valorar
					</a>

				<?php endif; ?>

			</div>


			<!-- Precio -->

			<div class="lmc-price">

				<?php echo wp_kses_post( $price_html ); ?>

			</div>


			<!-- Descripción corta -->

			<?php if ( $short_description ) : ?>

				<div class="lmc-short-description">

					<?php
					echo wp_kses_post(
						wpautop( $short_description )
					);
					?>

				</div>

			<?php endif; ?>


			<!-- ================= BENEFICIOS ================= -->

			<div class="lmc-benefits">

				<div class="lmc-benefit">
					<span>✓</span>
					Producto netamente natural
				</div>

				<div class="lmc-benefit">
					<span>✓</span>
					Envíos nacionales
				</div>

				<div class="lmc-benefit">
					<span>✓</span>
					Atención personalizada
				</div>

				<div class="lmc-benefit">
					<span>✓</span>
					Compra segura
				</div>

			</div>


			<!-- ====================================================
			     CARRITO / QUICK BUY
			     ==================================================== -->

			<div class="lmc-actions">

				<?php
				/**
				 * MUY IMPORTANTE:
				 *
				 * Esta función genera el formulario real de WooCommerce.
				 *
				 * Dentro de ese formulario, WooCommerce ejecuta:
				 *
				 * woocommerce_after_add_to_cart_button
				 *
				 * Ahí es donde tu plugin Quick Buy Now Button puede
				 * insertar su botón "Comprar ahora".
				 *
				 * NO añadimos manualmente otro botón Comprar ahora.
				 */
				woocommerce_template_single_add_to_cart();
				?>

			</div>


			<!-- ================= META ================= -->

			<div class="lmc-meta">

				<?php if ( $sku ) : ?>

					<div>
						<strong>SKU:</strong>
						<?php echo esc_html( $sku ); ?>
					</div>

				<?php endif; ?>


				<?php if ( $categories ) : ?>

					<div>
						<strong>Categoría:</strong>
						<?php echo wp_kses_post( $categories ); ?>
					</div>

				<?php endif; ?>

				<div>
					<strong>Envío:</strong>
				>Nacional · Consulta disponibilidad
				</div>

			</div>

		</div>

	</section>


	<!-- ============================================================
	     DESCRIPCIÓN / VALORACIONES
	     SOLO DOS SECCIONES
	     ============================================================ -->

	<section class="lmc-details">

		<div class="lmc-tabs">

			<button
				type="button"
				class="lmc-tab active"
				data-tab="description"
			>
				Descripción
			</button>

			<button
				type="button"
				class="lmc-tab"
				data-tab="reviews"
			>
				Valoraciones
			</button>

		</div>


		<div class="lmc-panels">

			<!-- ================= DESCRIPCIÓN ================= -->

			<div
				id="lmc-description"
				class="lmc-panel active"
			>

				<h2>
					Descripción del producto
				</h2>

				<?php if ( $description ) : ?>

					<div class="lmc-description-content">

						<?php
						echo apply_filters(
							'the_content',
							$description
						);
						?>

					</div>

				<?php else : ?>

					<p>
						Este producto aún no tiene una descripción detallada.
					</p>

				<?php endif; ?>

			</div>


			<!-- ================= VALORACIONES ================= -->

			<div
				id="lmc-reviews"
				class="lmc-panel"
			>

				<h2>
					Valoraciones
				</h2>

				<div class="lmc-reviews-content">

					<?php
					/**
					 * Utilizamos el contenido real de WooCommerce
					 * para mostrar las reseñas y el formulario.
					 */
					comments_template();
					?>

				</div>

			</div>

		</div>

	</section>


	<!-- ============================================================
	     PRODUCTOS RELACIONADOS
	     ============================================================ -->

	<?php

	$related_products = wc_get_related_products(
		$product_id,
		4
	);

	if ( $related_products ) :

	?>

		<section class="lmc-related">

			<h2 class="lmc-section-title">
				También te puede interesar
			</h2>

			<p class="lmc-section-subtitle">
				Descubre otros productos naturales de Limpia Mar MC
			</p>


			<div class="lmc-related-grid">

				<?php foreach ( $related_products as $related_id ) : ?>

					<?php

					$related_product = wc_get_product( $related_id );

					if ( ! $related_product ) {
						continue;
					}

					$related_image = $related_product->get_image(
						'woocommerce_thumbnail'
					);

					$related_categories = wc_get_product_category_list(
						$related_id,
						', '
					);

					?>

					<article class="lmc-related-card">

						<a
							href="<?php echo esc_url( get_permalink( $related_id ) ); ?>"
							class="lmc-related-image"
						>

							<?php
							echo wp_kses_post( $related_image );
							?>

						</a>


						<div class="lmc-related-body">

							<div class="lmc-related-category">

								<?php
								echo wp_kses_post( $related_categories );
								?>

							</div>


							<h3>

								<a
									href="<?php echo esc_url( get_permalink( $related_id ) ); ?>"
								>

									<?php
									echo esc_html(
										$related_product->get_name()
									);
									?>

								</a>

							</h3>


							<div class="lmc-related-price">

								<?php
								echo wp_kses_post(
									$related_product->get_price_html()
								);
								?>

							</div>

						</div>

					</article>

				<?php endforeach; ?>

			</div>

		</section>

	<?php endif; ?>

</main>


<!-- ================================================================
     BARRA FIJA MOBILE
     ================================================================ -->

<div
	class="lmc-sticky"
	aria-hidden="true"
>

	<span class="lmc-sticky-price">

		<?php echo wp_kses_post( $price_html ); ?>

	</span>


	<button
		type="button"
		class="lmc-sticky-button"
	>
		Comprar ahora
	</button>

</div>


</div>

<style>

/* ================================================================
   VARIABLES
   ================================================================ */

.lmc-single-product {

	--lmc-navy: #2F416F;
	--lmc-navy-dark: #1C244B;
	--lmc-green: #7BB932;
	--lmc-green-dark: #67A125;
	--lmc-text: #333333;
	--lmc-muted: #747B8C;
	--lmc-line: #E7EBF0;
	--lmc-cream: #F7F8F3;
	--lmc-white: #FFFFFF;

	font-family: Poppins, sans-serif;
	color: var(--lmc-text);
}

.lmc-single-product *,
.lmc-single-product *::before,
.lmc-single-product *::after {
	box-sizing: border-box;
}


/* ================================================================
   CONTENEDOR
   ================================================================ */

.lmc-product-main {

	max-width: 1180px;
	margin: 0 auto;
	padding: 28px 22px 70px;
	background: #ffffff;
    border-radius: 25px;
}


/* ================================================================
   BREADCRUMB
   ================================================================ */

.lmc-breadcrumb {

	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 7px;

	font-size: 12px;
	color: #8B92A0;

	margin: 4px 0 28px;

}

.lmc-breadcrumb a {

	color: #8B92A0;
	text-decoration: none;

}

.lmc-breadcrumb a:hover {

	color: var(--lmc-green);

}

.lmc-breadcrumb-current {

	color: var(--lmc-green);

}


/* ================================================================
   PRODUCTO
   ================================================================ */

.lmc-product {

	display: grid;

	grid-template-columns:
		minmax(0, 1.08fr)
		minmax(390px, .92fr);

	gap: 68px;

	align-items: start;

}


/* ================================================================
   GALERÍA
   ================================================================ */

.lmc-gallery-column {

	min-width: 0;

}

.lmc-gallery-main {

	background: var(--lmc-cream);

	border-radius: 24px;

	aspect-ratio: 1 / 1;

	display: flex;

	align-items: center;

	justify-content: center;

	overflow: hidden;

	position: relative;

}

.lmc-main-image {

	width: 100%;
	height: 100%;

	object-fit: cover;

	display: block;

	transition: opacity .25s ease;

}

.lmc-gallery-arrow {

	position: absolute;

	top: 50%;

	transform: translateY(-50%);

	width: 42px;
	height: 42px;

	border: 0;

	border-radius: 50%;

	background: #fff;

	box-shadow: 0 5px 20px rgba(28,36,75,.10);

	color: var(--lmc-navy);

	font-size: 22px;

	cursor: pointer;

	z-index: 2;

	transition: .2s ease;

}

.lmc-gallery-arrow:hover {

	background: var(--lmc-navy);

	color: #fff;

}

.lmc-gallery-prev {

	left: 16px;

}

.lmc-gallery-next {

	right: 16px;

}


/* ================================================================
   MINIATURAS
   ================================================================ */

.lmc-thumbs {

	display: flex;

	flex-wrap: wrap;

	gap: 12px;

	margin-top: 14px;

}

.lmc-thumb {

	width: 76px;
	height: 76px;

	padding: 0;

	border-radius: 12px;

	overflow: hidden;

	border: 2px solid transparent;

	background: var(--lmc-cream);

	cursor: pointer;

	transition: border-color .2s ease;

}

.lmc-thumb.active,
.lmc-thumb:hover {

	border-color: var(--lmc-green);

}

.lmc-thumb img {

	width: 100%;
	height: 100%;

	object-fit: cover;

	display: block;

}


/* ================================================================
   INFORMACIÓN
   ================================================================ */

.lmc-product-info {

	padding-top: 8px;

	min-width: 0;

}

.lmc-category {

	font-size: 12px;

	text-transform: uppercase;

	letter-spacing: .12em;

	color: var(--lmc-green-dark);

	font-weight: 600;

	margin-bottom: 10px;

}

.lmc-category a {

	color: inherit;

	text-decoration: none;

}

.lmc-product-title {

	font-size: clamp(30px, 4vw, 45px);

	line-height: 1.12;

	color: var(--lmc-navy-dark);

	margin: 0 0 14px;

	font-weight: 600;

}

.lmc-rating {

	display: flex;

	align-items: center;

	gap: 9px;

	font-size: 13px;

	color: #777;

	margin-bottom: 24px;

}

.lmc-stars {

	color: #F2A623;

	letter-spacing: 2px;

}

.lmc-stars .star-rating {

	color: #F2A623;

}

.lmc-review-link {

	color: #777;

	text-decoration: underline;

}

.lmc-price {

	font-size: 30px;

	color: var(--lmc-navy);

	font-weight: 600;

	margin-bottom: 20px;

}

.lmc-price del {

	color: #AAB0BB;

	font-size: 17px;

	font-weight: 400;

	margin-right: 10px;

}

.lmc-price ins {

	text-decoration: none;

}

.lmc-short-description {

	font-size: 15px;

	line-height: 1.8;

	color: #5E6573;

	margin-bottom: 25px;

}

.lmc-short-description p {

	margin: 0 0 10px;

}

.lmc-short-description p:last-child {

	margin-bottom: 0;

}


/* ================================================================
   BENEFICIOS
   ================================================================ */

.lmc-benefits {

	display: grid;

	grid-template-columns: 1fr 1fr;

	gap: 10px;

	margin: 0 0 25px;

}

.lmc-benefit {

	background: #F7F9F4;

	border-radius: 12px;

	padding: 12px;

	font-size: 12px;

	color: #59634E;

}

.lmc-benefit span {

	color: var(--lmc-green);

	font-weight: 700;

	margin-right: 5px;

}


/* ================================================================
   CARRITO
   ================================================================ */

.lmc-actions {

	margin-bottom: 16px;

}

.lmc-actions form.cart {

	display: flex;

	flex-wrap: wrap;

	align-items: center;

	gap: 10px;

	margin: 0;

}

/* ================================================================
SELECTOR DE CANTIDAD — − 1 +
================================================================ */

.lmc-actions .lmc-quantity-wrapper {

display: flex;

align-items: center;

height: 50px;

border: 1px solid #DFE4EB;

border-radius: 10px;

overflow: hidden;

background: #fff;

flex-shrink: 0;

}

.lmc-actions .lmc-quantity-button {

width: 38px;

height: 50px;

padding: 0;

border: 0;

background: #F5F6F8;

color: var(--lmc-navy);

font-size: 18px;

font-family: Poppins, sans-serif;

cursor: pointer;

display: flex;

align-items: center;

justify-content: center;

transition: background .2s ease, color .2s ease;

}

.lmc-actions .lmc-quantity-button:hover {

background: var(--lmc-navy);

color: #fff;

}

.lmc-actions .lmc-quantity-wrapper .quantity {

border: 0;

height: 50px;

margin: 0 !important;

display: flex;

align-items: center;

}

.lmc-actions .lmc-quantity-wrapper .quantity .qty {

width: 50px;

height: 48px;

border: 0;

padding: 0;

text-align: center;

font: 500 14px Poppins, sans-serif;

color: var(--lmc-navy);

outline: 0;

-moz-appearance: textfield;

}

.lmc-actions .lmc-quantity-wrapper .quantity .qty::-webkit-inner-spin-button,
.lmc-actions .lmc-quantity-wrapper .quantity .qty::-webkit-outer-spin-button {

-webkit-appearance: none;

margin: 0;

}

/* ================================================================
MOBILE
================================================================ */

@media (max-width: 600px) {

.lmc-actions .lmc-quantity-wrapper {

	width: 100%;

	justify-content: center;

}

.lmc-actions .lmc-quantity-wrapper .quantity {

	flex: 1;

}

.lmc-actions .lmc-quantity-wrapper .quantity .qty {

	width: 100%;

}

.lmc-actions .lmc-quantity-button {

	width: 50px;

}

}


.lmc-actions .quantity {

	display: flex;

	align-items: center;

	border: 1px solid #DFE4EB;

	border-radius: 10px;

	overflow: hidden;

	height: 50px;

	margin: 0 !important;

}

.lmc-actions .quantity .qty {

	width: 50px;

	height: 48px;

	border: 0;

	text-align: center;

	font: 500 14px Poppins, sans-serif;

	outline: 0;

	padding: 0;

	-moz-appearance: textfield;

}

.lmc-actions .quantity .qty::-webkit-inner-spin-button,
.lmc-actions .quantity .qty::-webkit-outer-spin-button {

	-webkit-appearance: none;

	margin: 0;

}


/*
 * Botón Añadir al carrito
 */

.lmc-actions .single_add_to_cart_button {

	height: 50px;

	border-radius: 10px;

	padding: 0 24px;

	font: 600 14px Poppins, sans-serif;

	background: #fff;

	border: 2px solid var(--lmc-navy);

	color: var(--lmc-navy);

	cursor: pointer;

	transition: .2s ease;

	margin: 0 !important;

	flex: 1;

}

.lmc-actions .single_add_to_cart_button:hover {

	background: var(--lmc-navy);

	color: #fff;

}


/*
 * Quick Buy Now Button
 *
 * Se estiliza sin generarlo manualmente.
 */

.lmc-actions a[href*="wc-quick-buy-now"],
.lmc-actions .quick-buy-now-button,
.lmc-actions .buy-now-button,
.lmc-actions [class*="quick-buy"] {

	display: inline-flex !important;

	align-items: center;

	justify-content: center;

	height: 50px;

	border-radius: 10px !important;

	padding: 0 24px !important;

	font: 600 14px Poppins, sans-serif !important;

	background: var(--lmc-green) !important;

	border: 2px solid var(--lmc-green) !important;

	color: #fff !important;

	text-decoration: none !important;

	cursor: pointer;

	transition: .2s ease;

	flex: 1;

}

.lmc-actions a[href*="wc-quick-buy-now"]:hover,
.lmc-actions .quick-buy-now-button:hover,
.lmc-actions .buy-now-button:hover,
.lmc-actions [class*="quick-buy"]:hover {

	background: var(--lmc-green-dark) !important;

	border-color: var(--lmc-green-dark) !important;

}


/* ================================================================
   META
   ================================================================ */

.lmc-meta {

	border-top: 1px solid var(--lmc-line);

	padding-top: 18px;

	font-size: 12px;

	color: #858B97;

	line-height: 2;

}

.lmc-meta strong {

	color: var(--lmc-navy);

}

.lmc-meta a {

	color: var(--lmc-navy);

	text-decoration: none;

}


/* ================================================================
   DESCRIPCIÓN / VALORACIONES
   ================================================================ */

.lmc-details {

	margin-top: 70px;

	border-top: 1px solid var(--lmc-line);

	padding-top: 36px;

	display: grid;

	grid-template-columns: 180px 1fr;

	gap: 50px;

}

.lmc-tabs {

	display: flex;

	flex-direction: column;

	gap: 8px;

}

.lmc-tab {

	padding: 12px 15px;

	border: 0;

	background: none;

	text-align: left;

	font: 500 13px Poppins, sans-serif;

	color: #9299A6;

	border-radius: 8px;

	cursor: pointer;

	transition: .2s ease;

}

.lmc-tab.active,
.lmc-tab:hover {

	background: #F2F6EB;

	color: var(--lmc-navy);

}

.lmc-panel {

	display: none;

}

.lmc-panel.active {

	display: block;

}

.lmc-panel h2 {

	margin: 0 0 14px;

	color: var(--lmc-navy-dark);

	font-size: 24px;

}

.lmc-description-content {

	font-size: 14px;

	line-height: 1.9;

	color: #646B78;

}

.lmc-description-content p {

	margin: 0 0 15px;

}

.lmc-description-content img {

	max-width: 100%;

	height: auto;

}

.lmc-reviews-content {

	font-size: 14px;

	line-height: 1.8;

}


/* ================================================================
   PRODUCTOS RELACIONADOS
   ================================================================ */

.lmc-related {

	margin-top: 75px;

}

.lmc-section-title {

	text-align: center;

	color: var(--lmc-navy-dark);

	font-size: 26px;

	margin: 0;

}

.lmc-section-subtitle {

	text-align: center;

	color: #858B97;

	font-size: 13px;

	margin: 7px 0 28px;

}

.lmc-related-grid {

	display: grid;

	grid-template-columns: repeat(4, 1fr);

	gap: 18px;

}

.lmc-related-card {

	border: 1px solid var(--lmc-line);

	border-radius: 16px;

	overflow: hidden;

	background: #fff;

	transition: .2s ease;

}

.lmc-related-card:hover {

	transform: translateY(-3px);

	box-shadow: 0 10px 30px rgba(47,65,111,.07);

}

.lmc-related-image {

	aspect-ratio: 1;

	background: var(--lmc-cream);

	display: flex;

	align-items: center;

	justify-content: center;

	overflow: hidden;

}

.lmc-related-image img {

	width: 100%;

	height: 100%;

	object-fit: cover;

}

.lmc-related-body {

	padding: 15px;

}

.lmc-related-category {

	font-size: 10px;

	color: var(--lmc-green-dark);

	text-transform: uppercase;

}

.lmc-related-card h3 {

	font-size: 14px;

	color: var(--lmc-navy);

	margin: 5px 0;

}

.lmc-related-card h3 a {

	color: inherit;

	text-decoration: none;

}

.lmc-related-price {

	font-weight: 600;

	font-size: 14px;

	color: var(--lmc-navy);

}


/* ================================================================
   STICKY MOBILE
   ================================================================ */

.lmc-sticky {

	display: none;

	position: fixed;

	bottom: 0;

	left: 0;

	right: 0;

	background: #fff;

	padding: 10px 14px;

	box-shadow: 0 -5px 25px rgba(0,0,0,.08);

	z-index: 9999;

	align-items: center;

	gap: 12px;

}

.lmc-sticky-price {

	font-weight: 600;

	color: var(--lmc-navy);

	font-size: 16px;

	flex: 1;

}

.lmc-sticky-price del {

	font-size: 12px;

	color: #999;

}

.lmc-sticky-price ins {

	text-decoration: none;

}

.lmc-sticky-button {

	background: var(--lmc-green);

	color: #fff;

	border: 0;

	border-radius: 9px;

	padding: 12px 18px;

	font: 600 13px Poppins, sans-serif;

	cursor: pointer;

}


/* ================================================================
   RESPONSIVE
   ================================================================ */

@media (max-width: 850px) {

	.lmc-product {

		grid-template-columns: 1fr;

		gap: 32px;

	}

	.lmc-product-info {

		padding: 0;

	}

	.lmc-details {

		grid-template-columns: 1fr;

		gap: 15px;

	}

	.lmc-tabs {

		flex-direction: row;

		overflow-x: auto;

	}

	.lmc-tab {

		white-space: nowrap;

	}

	.lmc-related-grid {

		grid-template-columns: repeat(2, 1fr);

	}

	.lmc-sticky {

		display: flex;

	}

	.lmc-product-main {

		padding-bottom: 90px;

	}

}


@media (max-width: 600px) {

	.lmc-product-main {

		padding: 18px 14px 90px;
		border-radius: 12px;

	}

	.lmc-gallery-main {

		border-radius: 17px;

	}

	.lmc-thumb {

		width: 62px;

		height: 62px;

	}

	.lmc-product-title {

		font-size: 30px;

	}

	.lmc-price {

		font-size: 27px;

	}

	.lmc-actions form.cart {

		flex-direction: column;

		align-items: stretch;

	}

	.lmc-actions .quantity {

		width: 100%;

		justify-content: center;

	}

	.lmc-actions .quantity .qty {

		flex: 1;

	}

	.lmc-actions .single_add_to_cart_button,
	.lmc-actions a[href*="wc-quick-buy-now"],
	.lmc-actions .quick-buy-now-button,
	.lmc-actions .buy-now-button,
	.lmc-actions [class*="quick-buy"] {

		width: 100%;

		flex: none;

	}

	.lmc-benefits {

		grid-template-columns: 1fr;

	}

	.lmc-related-grid {

		gap: 10px;

	}

	.lmc-related-body {

		padding: 11px;

	}

}


/* ================================================================
   OCULTAR ELEMENTOS QUE NO QUEREMOS
   ================================================================ */

/*
 * WooCommerce puede introducir algunos elementos propios.
 * Los tabs se generan exclusivamente con nuestra estructura.
 */

.lmc-single-product .woocommerce-tabs {
	display: none !important;
}


/*
 * Ocultar algunos wrappers innecesarios de Astra/WooCommerce
 * solamente dentro de nuestro producto.
 */

.lmc-single-product .woocommerce-product-gallery__trigger {
	display: none;
}

</style>

<script>
(function () {

	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		const root = document.querySelector(
			'.lmc-single-product'
		);

		if (!root) {
			return;
		}


		/* ============================================================
		   GALERÍA
		   ============================================================ */

		const mainImage = root.querySelector(
			'.lmc-main-image'
		);

		const thumbs = root.querySelectorAll(
			'.lmc-thumb'
		);

		const prevButton = root.querySelector(
			'.lmc-gallery-prev'
		);

		const nextButton = root.querySelector(
			'.lmc-gallery-next'
		);

		let currentIndex = 0;


		function showImage(index) {

			if (!thumbs.length || !mainImage) {
				return;
			}

			if (index < 0) {
				index = thumbs.length - 1;
			}

			if (index >= thumbs.length) {
				index = 0;
			}

			currentIndex = index;

			const thumb = thumbs[currentIndex];

			const imageUrl = thumb.getAttribute(
				'data-image'
			);

			const imageAlt = thumb.getAttribute(
				'data-alt'
			);


			mainImage.style.opacity = '0';


			setTimeout(function () {

				mainImage.src = imageUrl;

				mainImage.alt = imageAlt || '';

				mainImage.style.opacity = '1';

			}, 120);


			thumbs.forEach(function (item, itemIndex) {

				item.classList.toggle(
					'active',
					itemIndex === currentIndex
				);

			});

		}


		thumbs.forEach(function (thumb, index) {

			thumb.addEventListener(
				'click',
				function () {

					showImage(index);

				}
			);

		});


		if (prevButton) {

			prevButton.addEventListener(
				'click',
				function () {

					showImage(currentIndex - 1);

				}
			);

		}


		if (nextButton) {

			nextButton.addEventListener(
				'click',
				function () {

					showImage(currentIndex + 1);

				}
			);

		}


		/* ============================================================
		   TECLADO GALERÍA
		   ============================================================ */

		document.addEventListener(
			'keydown',
			function (event) {

				if (event.key === 'ArrowLeft') {

					showImage(currentIndex - 1);

				}

				if (event.key === 'ArrowRight') {

					showImage(currentIndex + 1);

				}

			}
		);

        /* ============================================================
        SELECTOR DE CANTIDAD − 1 +
        ============================================================ */
        
        const cartForms = root.querySelectorAll(
        '.lmc-actions form.cart'
        );
        
        cartForms.forEach(function (form) {
        
        const quantity = form.querySelector(
        	'.quantity'
        );
        
        if (!quantity) {
        	return;
        }
        
        /*
         * Evitamos crear los botones más de una vez.
         */
        if (
        	quantity.parentElement &&
        	quantity.parentElement.classList.contains(
        		'lmc-quantity-wrapper'
        	)
        ) {
        	return;
        }
        
        
        /*
         * Creamos el contenedor:
         *
         * − | cantidad | +
         */
        const wrapper = document.createElement(
        	'div'
        );
        
        wrapper.className =
        	'lmc-quantity-wrapper';
        
        
        /*
         * Botón -
         */
        const minusButton = document.createElement(
        	'button'
        );
        
        minusButton.type = 'button';
        
        minusButton.className =
        	'lmc-quantity-button lmc-quantity-minus';
        
        minusButton.setAttribute(
        	'aria-label',
        	'Disminuir cantidad'
        );
        
        minusButton.textContent = '−';
        
        
        /*
         * Botón +
         */
        const plusButton = document.createElement(
        	'button'
        );
        
        plusButton.type = 'button';
        
        plusButton.className =
        	'lmc-quantity-button lmc-quantity-plus';
        
        plusButton.setAttribute(
        	'aria-label',
        	'Aumentar cantidad'
        );
        
        plusButton.textContent = '+';
        
        
        /*
         * Insertamos el wrapper en el mismo formulario.
         */
        form.insertBefore(
        	wrapper,
        	quantity
        );
        
        
        /*
         * Movemos la cantidad real de WooCommerce
         * dentro del wrapper.
         */
        wrapper.appendChild(
        	minusButton
        );
        
        wrapper.appendChild(
        	quantity
        );
        
        wrapper.appendChild(
        	plusButton
        );
        
        
        const input = quantity.querySelector(
        	'input.qty'
        );
        
        if (!input) {
        	return;
        }
        
        
        /*
         * Obtener mínimo.
         */
        function getMin() {
        
        	const min = parseFloat(
        		input.getAttribute('min')
        	);
        
        	return Number.isFinite(min)
        		? min
        		: 1;
        
        }
        
        
        /*
         * Obtener máximo.
         */
        function getMax() {
        
        	const max = parseFloat(
        		input.getAttribute('max')
        	);
        
        	return Number.isFinite(max)
        		? max
        		: Infinity;
        
        }
        
        
        /*
         * Obtener step.
         */
        function getStep() {
        
        	const step = parseFloat(
        		input.getAttribute('step')
        	);
        
        	return Number.isFinite(step) && step > 0
        		? step
        		: 1;
        
        }
        
        
        /*
         * Normalizar cantidad.
         */
        function normalizeValue(value) {
        
        	const min = getMin();
        
        	const max = getMax();
        
        	const step = getStep();
        
        
        	value = Math.max(
        		min,
        		value
        	);
        
        
        	if (max !== Infinity) {
        
        		value = Math.min(
        			max,
        			value
        		);
        
        	}
        
        
        	/*
        	 * Ajustamos al step de WooCommerce.
        	 */
        	const decimals =
        		(step.toString().split('.')[1] || '').length;
        
        	value = Math.round(
        		value / step
        	) * step;
        
        
        	return Number(
        		value.toFixed(decimals)
        	);
        
        }
        
        
        /*
         * Botón -
         */
        minusButton.addEventListener(
        	'click',
        	function () {
        
        		const current =
        			parseFloat(input.value) || getMin();
        
        		const step =
        			getStep();
        
        		const newValue =
        			normalizeValue(
        				current - step
        			);
        
        
        		input.value =
        			newValue;
        
        
        		/*
        		 * Avisamos a WooCommerce
        		 * de que la cantidad cambió.
        		 */
        		input.dispatchEvent(
        			new Event(
        				'change',
        				{
        					bubbles: true
        				}
        			)
        		);
        
        	}
        );
        
        
        /*
         * Botón +
         */
        plusButton.addEventListener(
        	'click',
        	function () {
        
        		const current =
        			parseFloat(input.value) || getMin();
        
        		const step =
        			getStep();
        
        		const newValue =
        			normalizeValue(
        				current + step
        			);
        
        
        		input.value =
        			newValue;
        
        
        		/*
        		 * Avisamos a WooCommerce
        		 * de que la cantidad cambió.
        		 */
        		input.dispatchEvent(
        			new Event(
        				'change',
        				{
        					bubbles: true
        				}
        			)
        		);
        
        	}
        );

        
        });


		/* ============================================================
		   TABS
		   ============================================================ */

		const tabs = root.querySelectorAll(
			'.lmc-tab'
		);

		const panels = root.querySelectorAll(
			'.lmc-panel'
		);


		tabs.forEach(function (tab) {

			tab.addEventListener(
				'click',
				function () {

					const target = tab.getAttribute(
						'data-tab'
					);


					tabs.forEach(function (item) {

						item.classList.remove(
							'active'
						);

					});


					panels.forEach(function (panel) {

						panel.classList.remove(
							'active'
						);

					});


					tab.classList.add(
						'active'
					);


					const targetPanel = root.querySelector(
						'#lmc-' + target
					);


					if (targetPanel) {

						targetPanel.classList.add(
							'active'
						);

					}

				}
			);

		});
		
		
        /* ============================================================
           ENLACE "SÉ EL PRIMERO EN VALORAR"
           ACTIVAR VALORACIONES AUTOMÁTICAMENTE
           ============================================================ */
        
        const reviewLinks = root.querySelectorAll(
        	'.lmc-review-link'
        );
        
        reviewLinks.forEach(function (link) {
        
        	link.addEventListener(
        		'click',
        		function (event) {
        
        			event.preventDefault();
        
        			/* ----------------------------------------------------
        			   Activar tab Valoraciones
        			   ---------------------------------------------------- */
        
        			tabs.forEach(function (tab) {
        
        				tab.classList.remove('active');
        
        				if (
        					tab.getAttribute('data-tab') === 'reviews'
        				) {
        					tab.classList.add('active');
        				}
        
        			});
        
        
        			/* ----------------------------------------------------
        			   Ocultar todos los paneles
        			   ---------------------------------------------------- */
        
        			panels.forEach(function (panel) {
        
        				panel.classList.remove('active');
        
        			});
        
        
        			/* ----------------------------------------------------
        			   Mostrar panel de Valoraciones
        			   ---------------------------------------------------- */
        
        			const reviewsPanel = root.querySelector(
        				'#lmc-reviews'
        			);
        
        			if (reviewsPanel) {
        
        				reviewsPanel.classList.add('active');
        
        				/* ------------------------------------------------
        				   Esperamos un momento para que el panel
        				   esté visible antes de hacer scroll.
        				   ------------------------------------------------ */
        
        				setTimeout(function () {
        
        					reviewsPanel.scrollIntoView({
        						behavior: 'smooth',
        						block: 'start'
        					});
        
        				}, 50);
        
        			}
        
        		}
        	);
        
        });
        

		/* ============================================================
		   BARRA FIJA MOBILE
		   ============================================================ */

		const stickyButton = root.querySelector(
			'.lmc-sticky-button'
		);

		const actions = root.querySelector(
			'.lmc-actions'
		);

		const sticky = root.querySelector(
			'.lmc-sticky'
		);


		if (
			stickyButton &&
			actions &&
			sticky &&
			'IntersectionObserver' in window
		) {

			const observer = new IntersectionObserver(
				function (entries) {

					entries.forEach(function (entry) {

						if (
							window.innerWidth <= 850
						) {

							sticky.style.display =
								entry.isIntersecting
									? 'none'
									: 'flex';

						} else {

							sticky.style.display =
								'none';

						}

					});

				},
				{
					threshold: 0
				}
			);


			observer.observe(actions);


			window.addEventListener(
				'resize',
				function () {

					if (
						window.innerWidth > 850
					) {

						sticky.style.display =
							'none';

					}

				}
			);

		}


		/* ============================================================
		   STICKY → SCROLL HACIA EL CARRITO
		   ============================================================ */

		if (stickyButton && actions) {

			stickyButton.addEventListener(
				'click',
				function () {

					actions.scrollIntoView({
						behavior: 'smooth',
						block: 'center'
					});

				}
			);

		}

	});

})();
</script>