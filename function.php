<?php
/**
 * Storefront functions and definitions.
 *
 * @link    https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Storefront Child
 */

function is_whatsapp()
{
    return true;
}
function master_number(){
    return '0311-1333290';
}

function site_name_shortcode(){
	return "New Gadgets";
}
add_shortcode( 'site_name', 'site_name_shortcode' );

function number_shortcode( $atts ){
	return master_number();
}
add_shortcode( 'site_number', 'number_shortcode' );

function email_shortcode( $atts ){
	return "info@newgadgets.pk";
}
add_shortcode( 'site_email', 'email_shortcode' );

function last_update_shortcode( $atts ){
    
    return date("d-M-Y",strtotime("-10 day"));;
}
add_shortcode( 'last_update', 'last_update_shortcode' );

add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_scripts', 20 );

function storefront_child_enqueue_scripts() {
    wp_enqueue_style( 'storefront-child', get_stylesheet_uri() );
}

function my_scripts_method() {
    wp_enqueue_script(
        'validate',
        get_stylesheet_directory_uri() . '/js/jquery.validate.min.js',
        array( 'jquery' )
	);
	wp_enqueue_script(
        'custom',
        get_stylesheet_directory_uri() . '/js/custom.js',
        array( 'jquery' )
    );
    wp_enqueue_script(
        'bootstrap',
        get_stylesheet_directory_uri() . '/js/bootstrap.min.js',
        array( 'jquery' )
    );
}

add_action( 'wp_enqueue_scripts', 'my_scripts_method' );
/***************
 * *************
 * PRODUCT PAGE*
 * *************
 * *************/

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );


add_action( 'woocommerce_after_add_to_cart_button', 'misha_before_add_to_cart_btn' );
 
function misha_before_add_to_cart_btn(){
    global $post;
    $product = wc_get_product( $post->ID );
    
    // $shipclass = $product->get_shipping_class();
    // if($shipclass){
    //     $shipping  =  wc_price(get_shipping_price($post->ID)) . ' Delivery charges will be applied!';
    // }else{
    //     $shipping  = $product->get_meta( 'shipping_element_title' );
    // }
    
    $number = rand(1,5);
    $fill = ($number/10)*100;
    // echo '<div class="bg-success text-white shipping-info">'.$shipping.'</div>';
    echo '<div class="hurry-offer mt-2">Hurry! Only <span class="text-danger">'.$number.'</span> in Stock</div>
            <div class="stock-countdown-border">
                <div class="stock-countdown-border" style="background:#000; width:'.$fill.'%;"></div>
            </div>';
}

function get_shipping_price($product_id){
    // HERE set your targeted shipping Zone type (method ID)
    $targeted_shipping_method_id = 'flat_rate';
    $product = wc_get_product( $product_id );
    // The product shipping class ID
    $product_class_id = $product->get_shipping_class_id();

    $zone_ids = array_keys( array('') + WC_Shipping_Zones::get_zones() );

    // Loop through Zone IDs
    foreach ( $zone_ids as $zone_id ) {
        
        // Get the shipping Zone object
        $shipping_zone = new WC_Shipping_Zone($zone_id);
        // Get all shipping method values for the shipping zone
        $shipping_methods = $shipping_zone->get_shipping_methods( true, 'values' );

        // Loop through Zone IDs
        foreach ( $shipping_methods as $instance_id => $shipping_method ) {
            // Shipping method rate ID
            $rate_id = $shipping_method->get_rate_id();

            // Shipping method ID
            $method_id = explode( ':', $rate_id);
            $method_id = reset($method_id);

            // Targeting a specific shipping method ID
            if( $method_id === $targeted_shipping_method_id ) {
                // Get Shipping method title (label)
                $title = $shipping_method->get_title();
                $title = empty($title) ? $shipping_method->get_method_title() : $title;

                // Get shipping method settings data
                $data = $shipping_method->instance_settings;

                ## COST:

                // For a defined shipping class
                if( isset($product_class_id) && ! empty($product_class_id) 
                && isset($data['class_cost_'.$product_class_id]) ) {
                    $cost = $data['class_cost_'.$product_class_id];
                }
                // For no defined shipping class when "no class cost" is defined
                elseif( isset($product_class_id) && empty($product_class_id) 
                && isset($data['no_class_cost']) && $data['no_class_cost'] > 0 ) {
                    $cost = $data['no_class_cost'];
                } 
                // When there is no defined shipping class and when "no class cost" is defined
                else {
                    $cost = $data['cost'];
                }

                // Testing output
                // echo '<p><strong>'.$title.'</strong>: '.$cost.'</p>';
                return $cost;
            }
        }
    }
}


/* change price html order ins first then del*/
if (!function_exists('my_commonPriceHtml')) {

    function my_commonPriceHtml($price_amt, $regular_price, $sale_price) {
        global $product;
        
        $html_price = '<div class="price row no-gutters">';
        $html_price .= '<div class="price-col">';
        //if product is in sale
        if (($price_amt == $sale_price) && ($sale_price != 0)) {
            $save = $product->get_regular_price() - $product->get_sale_price();
            $saving = ($product->get_sale_price() / $product->get_regular_price()) * 100;
            //$saving = ($product->get_regular_price() / $product->get_sale_price()) * 100;
            
            $html_price .= '<ins> ';
            $html_price .= wc_price($sale_price) . '</ins>';
            if(is_product()){
                $html_price .= '<del>' . wc_price($regular_price) . '</del>';

            }else{
                $html_price .= '<del>' . wc_price($regular_price) . '</del>';

            }
            if(is_product()){
                // $html_price .= '<div class="save-line text-danger">You Save '.wc_price($save).' ('.round(100-$saving, 0).'%)</div>';
                $html_price .= '<span class="save-line text-white label bg-success p-1">'.round(100-$saving, 0).'% OFF</span>';
                // $html_price .= '<div class="limited-offer">Limited time only</div>';
            }
        }
        //in sale but free
        else if (($price_amt == $sale_price) && ($sale_price == 0)) {
            $html_price .= '<ins>Free!</ins>';
            $html_price .= '<del>' . wc_price($regular_price) . '</del>';
        }
        //not is sale
        else if (($price_amt == $regular_price) && ($regular_price != 0)) {
            $html_price .= '<ins>' . wc_price($regular_price) . '</ins>';
        }
        //for free product
        else if (($price_amt == $regular_price) && ($regular_price == 0)) {
            $html_price .= '<ins>Free!</ins>';
        }
        if($sale_price){
            if(!is_product()){
                $percentage = ($sale_price / $regular_price);
                $html_price .= '<span class="percentage">( '.round((1-$percentage)*100,0).'% OFF )</span>';
            }
        }
        
        $html_price .= '</div>';
        
        $html_price .= '</div>';
        return $html_price;
    }

}

add_filter('woocommerce_get_price_html', 'my_simple_product_price_html', 100, 2);

function my_simple_product_price_html($price, $product) {
    if ($product->is_type('simple')) {
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        $price_amt = $product->get_price();
        return my_commonPriceHtml($price_amt, $regular_price, $sale_price);
    } else {
        return $price;
    }
}

add_filter('woocommerce_variation_sale_price_html', 'my_variable_product_price_html', 10, 2);
add_filter('woocommerce_variation_price_html', 'my_variable_product_price_html', 10, 2);

function my_variable_product_price_html($price, $variation) {
    $variation_id = $variation->variation_id;
    //creating the product object
    $variable_product = new WC_Product($variation_id);

    $regular_price = $variable_product->get_regular_price();
    $sale_price = $variable_product->get_sale_price();
    $price_amt = $variable_product->get_price();

    return my_commonPriceHtml($price_amt, $regular_price, $sale_price);
}

add_filter('woocommerce_variable_sale_price_html', 'my_variable_product_minmax_price_html', 10, 2);
add_filter('woocommerce_variable_price_html', 'my_variable_product_minmax_price_html', 10, 2);

function my_variable_product_minmax_price_html($price, $product) {
    $variation_min_price = $product->get_variation_price('min', true);
    $variation_max_price = $product->get_variation_price('max', true);
    $variation_min_regular_price = $product->get_variation_regular_price('min', true);
    $variation_max_regular_price = $product->get_variation_regular_price('max', true);

    if (($variation_min_price == $variation_min_regular_price) && ($variation_max_price == $variation_max_regular_price)) {
        $html_min_max_price = $price;
    } else {
        $html_price = '<p class="price">';
        $html_price .= '<ins>' . wc_price($variation_min_price) . '-' . wc_price($variation_max_price) . '</ins>';
        $html_price .= '<del>' . wc_price($variation_min_regular_price) . '-' . wc_price($variation_max_regular_price) . '</del>';
        $html_min_max_price = $html_price;
    }

    return $html_min_max_price;
}

add_filter( 'woocommerce_product_single_add_to_cart_text', 'woo_custom_single_add_to_cart_text' );  // 2.1 +
  
function woo_custom_single_add_to_cart_text() {
  
    return __( 'Buy Now', 'woocommerce' );
  
}

/*Checkout page */
add_action( 'woocommerce_before_checkout_form', 'show_summary', 5 );

function show_summary(){
    global $woocommerce;
	$cart_value = WC()->cart->get_total();
    foreach ( WC()->cart->get_cart() as $key => $cart_item ) {
		/** @var WC_Product $product */
		$product = $cart_item['data'];

		if ( $product->is_on_sale() ) {
            $regular_price  = wc_price($product->get_regular_price() * $cart_item['quantity']);
            $sale_price     = wc_price($product->get_sale_price() * $cart_item['quantity']);
			$savings = ( $product->get_regular_price() - $product->get_sale_price() ) * $cart_item['quantity'];
		}
	}

    $count = $woocommerce->cart->cart_contents_count;
    if($count == 2){
        $count++;
    }

    echo '<div class="summary-show checkout-top-bar collapsible">
        <span class="icon-show">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20 7h-4v-3c0-2.209-1.791-4-4-4s-4 1.791-4 4v3h-4l-2 17h20l-2-17zm-11-3c0-1.654 1.346-3 3-3s3 1.346 3 3v3h-6v-3zm-4.751 18l1.529-13h2.222v1.5c0 .276.224.5.5.5s.5-.224.5-.5v-1.5h6v1.5c0 .276.224.5.5.5s.5-.224.5-.5v-1.5h2.222l1.529 13h-15.502z"/></svg>
        <span class="cart-items-no">'. $count .'</span>
        </span>
        <span class="text-show">Show Order Summary</span>
        <span class="amount-show">
            <del class="text-muted">'. $regular_price .'</del>
            <div class="regular-price">' . $cart_value . '</div>
        </span>        
    </div>';
    echo '<div class="order-summary-details content">';
        echo $a = show_overview_template();
        do_action( "woocommerce_checkout_order_review" );
    echo '</div>';
}



function remove_quantity_text( $cart_item, $cart_item_key ) {
    $product_quantity= '';
    return $product_quantity;
}
 
add_filter ('woocommerce_checkout_cart_item_quantity', 'remove_quantity_text', 10, 2 );


/*
* It will add Delete button, Quanitity field on the checkout page Your Order Table.
*/
function add_quantity( $product_title, $cart_item, $cart_item_key ) {

    /* Checkout page check */
    if (  is_checkout() ) {
        /* Get Cart of the user */
        $cart     = WC()->cart->get_cart();
            foreach ( $cart as $cart_key => $cart_value ){
               if ( $cart_key == $cart_item_key ){
                    $product_id = $cart_item['product_id'];
                    $_product   = $cart_item['data'] ;
                    
                    /* Step 1 : Add delete icon */
                    // $return_value = sprintf(
                    //   '<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                    //   esc_url( WC()->cart->get_remove_url( $cart_key ) ),
                    //   __( 'Remove this item', 'woocommerce' ),
                    //   esc_attr( $product_id ),
                    //   esc_attr( $_product->get_sku() )
                    // );
                    
                    /* Step 2 : Add product name */
                    $return_value = '<span class = "product_name" >' . $product_title . '</span>' ;
                    
                    /* Step 3 : Add quantity selector */
                    if ( $_product->is_sold_individually() ) {
                      $return_value .= sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_key );
                    } else {
                      $return_value .= woocommerce_quantity_input( array(
                          'input_name'  => "cart[{$cart_key}][qty]",
                          'input_value' => $cart_item['quantity'],
                          'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
                          'min_value'   => '1',
                          ), $_product, false );
                    }
                    return $return_value;
                }
            }
    }else{
        /*
         * It will return the product name on the cart page.
         * As the filter used on checkout and cart are same.
         */
        $_product   = $cart_item['data'] ;
        $product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
        if ( ! $product_permalink ) {
            $return_value = $_product->get_title() . '&nbsp;';
        } else {
            $return_value = sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_title());
        }
        return $return_value;
      }
}
    
add_filter ('woocommerce_cart_item_name', 'add_quantity' , 10, 3 );



/* Add js at the footer */
function add_quanity_js(){
    if ( is_checkout() ) {
      wp_enqueue_script( 'checkout_script', get_stylesheet_directory_uri() . '/js/add_quantity.js', '', '', false );
      $localize_script = array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
      );
      wp_localize_script( 'checkout_script', 'add_quantity', $localize_script );
    }
}
  
add_action( 'wp_footer', 'add_quanity_js', 10 );

function load_ajax() {
    if ( !is_user_logged_in() ){
        add_action( 'wp_ajax_nopriv_update_order_review', 'update_order_review' );
    } else{
        add_action( 'wp_ajax_update_order_review',        'update_order_review' );
    }
  }
  add_action( 'init', 'load_ajax' );

  function update_order_review() {
    global $woocommerce;

    $values = array();
    parse_str($_POST['post_data'], $values);
    $cart = $values['cart'];
    foreach ( $cart as $cart_key => $cart_value ){
        WC()->cart->set_quantity( $cart_key, $cart_value['qty'], false );
        WC()->cart->calculate_totals();
        woocommerce_cart_totals();
    }

    echo $a = show_overview_template();
    do_action( "woocommerce_checkout_order_review" );
    exit();
    
    wp_die();
}

function show_overview_template(){
    global $woocommerce;

    $count = $woocommerce->cart->cart_contents_count;
    $woocommerce->cart->get_cart_total();


    $items = $woocommerce->cart->get_cart();
    echo '<div class="product-container-checkout">';
    if($count == 2){
        foreach($items as $item => $values) {

        
            $_product = wc_get_product( $values['data']->get_id());
            $price = get_post_meta($values['product_id'] , '_price', true);
    
            // $product_id = $cart_item['product_id'];
            $image = wp_get_attachment_image_src( get_post_thumbnail_id($values['data']->get_id() ), 'single-post-thumbnail' );
            echo '<div class="d-flex align-items-center px-0 py-1 mb-1">
                <div class="prod-img position-relative">
                    <span class="position-absolute cart-items-no ">'.$count.'</span>
                    <img src="'.$image[0].'" alt="kk" width="64" class="img-responsive checkout-p-img">
                </div>
                <div class="prod-title ml-3">
                    '.$_product->get_title().'
                </div>
                <div class="prod-pirce ml-auto">
                    '.wc_price($price*2).'
                </div>
            </div>';
        }
        foreach($items as $item => $values) {

        
            $_product = wc_get_product( $values['data']->get_id());
            $price = get_post_meta($values['product_id'] , '_price', true);
    
            $image = wp_get_attachment_image_src( get_post_thumbnail_id($values['data']->get_id() ), 'single-post-thumbnail' );
            echo '<div class="d-flex align-items-center px-0 py-1 mb-1">
                <div class="prod-img position-relative">
                    <span class="position-absolute qty-text cart-items-no ">1</span>
                    <img src="'.$image[0].'" alt="kk" width="64" class="img-responsive checkout-p-img">
                </div>
                <div class="prod-title ml-3">
                    '.$_product->get_title().'
                </div>
                <div class="prod-pirce ml-auto">
                    <span class="text-danger">FREE</span>
                </div>
            </div>';
        }   
    }else{
        foreach($items as $item => $values) {

        
            $_product = wc_get_product( $values['data']->get_id());
            $price = get_post_meta($values['product_id'] , '_price', true);
    
            $image = wp_get_attachment_image_src( get_post_thumbnail_id($values['data']->get_id() ), 'single-post-thumbnail' );
            echo '<div class="d-flex align-items-center px-0 py-1 mb-1">
                <div class="prod-img position-relative">
                    <span class="position-absolute cart-items-no ">'.$count.'</span>
                    <img src="'.$image[0].'" alt="kk" width="64" class="img-responsive checkout-p-img">
                </div>
                <div class="prod-title ml-3">
                    '.$_product->get_title().'
                </div>
                <div class="prod-pirce ml-auto">
                    '.wc_price($price).'
                </div>
            </div>';
        }
    }

    echo '</div>';

    
}




/*end checkout page*/
// add_action( 'woocommerce_single_product_summary', 'posst_titlee', 6 );

function posst_titlee(){
    global $post;
    $product = wc_get_product( $post->ID );
    $posst_title = $product->get_meta( 'posst_title' );

    echo '<div class="post-title">'. $posst_title .'</div>';
}



// add_action( 'woocommerce_single_product_summary', 'pre_title', 4 );
function pre_title(){
    global $post;
    $product = wc_get_product( $post->ID );
    $pre_title = $product->get_meta( 'pre_title' );

    echo '<div class="pre-title">'. $pre_title .'</div>';
}

// add_action( 'woocommerce_single_product_summary', 'add_rating', 7 );

function add_rating(){
    echo '
        <div class="rating-container">
            <img src="https://cdn.shopify.com/s/files/1/0493/4605/2261/files/star.svg?v=1600908500" alt="star">
            <img src="https://cdn.shopify.com/s/files/1/0493/4605/2261/files/star.svg?v=1600908500" alt="star">
            <img src="https://cdn.shopify.com/s/files/1/0493/4605/2261/files/star.svg?v=1600908500" alt="star">
            <img src="https://cdn.shopify.com/s/files/1/0493/4605/2261/files/star.svg?v=1600908500" alt="star">
            <img src="https://cdn.shopify.com/s/files/1/0493/4605/2261/files/star.svg?v=1600908500" alt="star">

            <span class="rating-text">
                Rated 4.9 by 489 Customers
            </span>
        </div>
    ';
}

// add_action( 'woocommerce_after_add_to_cart_quantity', 'add_offer', 5 );

function add_offer(){
    global $post;
    $product = wc_get_product( $post->ID );
    $offer = $product->get_meta( 'offer_enable' );
    if($offer == 1){
        $offer_1_name = $product->get_meta( 'offer_name_1' ) ;
        if($offer_1_name == ''){$offer_1_name = 'Buy 1 only';}

        $offer_2_name = $product->get_meta( 'offer_name_2' ) ;
        if($offer_2_name == ''){$offer_2_name = 'Buy 2 Get 1 FREE';}

        $offer_1_delv = $product->get_meta( 'offer_1_delivery' ) ;
        if($offer_1_delv == ''){$offer_1_delv = '+ RS 201 Delivery';}

        $offer_2_delv = $product->get_meta( 'offer_2_delivery' ) ;
        if($offer_2_delv == ''){$offer_2_delv = '+ RS 201 Delivery';}

        $popular_text = $product->get_meta( 'popular_text' ) ;
        if($popular_text == ''){$popular_text = 'Most Popular';}

    }

    if($offer == 1){
        echo '<div class="quantity-selector">
            <ul class="list-group list-unstyled m-0">
                <li class="">
                    <label for="offer-1" class="list-item-wrapper checked">
                        <input type="radio" name="offer" id="offer-1" class="offer-select" value="1">
                        <span class="button_check"></span>
                        <span class="button-title">'. $offer_1_name .'</span>
                        <span class="button-details">'. $offer_1_delv .'</span>
                    </label>
                </li>
                <li class="">
                    <label for="offer-2" class="list-item-wrapper">
                        <span class="title-tag">'. $popular_text .'</span>
                        <input type="radio" name="offer" id="offer-2" class="offer-select" value="2">
                        <span class="button_check"></span>
                        <span class="button-title">' . $offer_2_name . '</span>
                        <span class="button-details">'. $offer_2_delv .'</span>
                    </label>
                </li>
            </ul>
        </div>';
    }

    
    
}

add_action( 'woocommerce_after_add_to_cart_button', 'misha_after_add_to_cart_btn' );
 
function misha_after_add_to_cart_btn(){
    
    if(is_whatsapp()){
        global $post;
        $product = wc_get_product( $post->ID );
        $whatsapp = $product->get_meta( 'whatsapp_enable' );
        
        if($whatsapp == 1 || $whatsapp == ''){
            // echo do_shortcode('[ht-ctc-chat]');
        }
    }
    
}

function add_fixed_button(){
    global $product, $post;
    if($product->get_sale_price()){
        $pro_price = $product->get_sale_price();
    }else{
        $product = $product->get_regular_price();
    }
    echo    '<div id="fixed-button" class="d-sm-none" style="display:none;">
                <span class="product-price" style="float:left;text-align:left;">
                    <h6 class="mb-0" style="font-size: 0.9rem;text-decoration: line-through;font-weight:600;">'.wc_price($product->get_regular_price()).' </h6> 
                    <h4 class="mb-0" style="font-weight: bold;color: #000;font-size: 1.5rem;position:relative;left:-1px;">'.wc_price($pro_price).'</h4>
                </span>
                <a class="single_add_to_cart_button button alt fixed-button-a" href="'.get_site_url().'/checkout/?add-to-cart='.$post->ID.'">BUY NOW 
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="position:relative;top:-2px;">
                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                </svg>
                </a>
            </div>';

    if ( is_product() ) {
        global $post, $product;
        // echo '<meta property="og:image" content="'.get_the_post_thumbnail_url( $post->ID, 'shop_thumbnail' ).'">';
        }
}

add_action( 'woocommerce_after_single_product_summary', 'add_fixed_button', 40 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_filter( 'woocommerce_product_description_heading', 'product_description_tab', 10, 1 );

function product_description_tab( $title ) {
    return '';
}

add_filter( 'woocommerce_product_tabs', 'misha_rename_additional_info_tab' );
 
function misha_rename_additional_info_tab( $tabs ) {
 
	$tabs['description']['title'] = 'More Information';
 
	return $tabs;
 
}

function woocommerce_template_product_description() {
    global $product;
    $att = ``;
    echo '<div class="description-heading">Product Details</div>';
    echo $att;
    echo '<div class="description-details">';
        if(wp_is_mobile()){
            echo $product->get_short_description();
            if($product->get_description() != ''){
                add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
            }

        }else{
            // woocommerce_get_template( 'single-product/tabs/description.php' );
            echo $product->get_short_description();
            echo $product->get_description();

        }
    
    echo '</div>';
  }
//   add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_product_description', 20 );
  add_action( 'woocommerce_single_product_summary', 'woocommerce_template_product_description', 70 );

  function remove_image_zoom_support() {
    remove_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'after_setup_theme', 'remove_image_zoom_support', 100 );


    add_filter( 'woocommerce_get_image_size_gallery_thumbnail', function( $size ) {
        return array(
        'width' => 150,
        'height' => 150,
        'crop' => 0,
        );
        } );

/********************
 * ******************
 * END PRODUCT PAGE**
 * ******************
 * ******************/


// define the woocommerce_order_button_html callback 
function filter_woocommerce_order_button_html( $input_type_submit_class_button_alt_name_woocommerce_checkout_place_order_id_place_order_value_esc_attr_order_button_text_data_value_esc_attr_order_button_text ) { 
    // make filter magic happen here... 
    // return $input_type_submit_class_button_alt_name_woocommerce_checkout_place_order_id_place_order_value_esc_attr_order_button_text_data_value_esc_attr_order_button_text; 
    echo '';
}; 
add_filter( 'woocommerce_order_button_html', 'filter_woocommerce_order_button_html', 10, 1 );
add_filter('woocommerce_checkout_after_customer_details', 'order_btn');

function order_btn(){
    global $woocommerce;
	$cart_value = WC()->cart->get_total();
    $order_button_text = 'Place Order';
    echo '<div class="btn-container">
        <div class="bill-deatails">
            Total Bill <br> <span class="price-total">'. $cart_value .'</span>
        </div>
        <div class="order-button">
            <button type="submit" class="button alt submit-btn" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>
        </div>
    </div>';
    
    // echo $button_html = '<div class="btn-container"> <button type="submit" class="button alt submit-btn" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button></div>';
}

function ace_hide_shipping_title( $label ) {
	$pos = strpos( $label, ': ' );
	return substr( $label, ++$pos );
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'ace_hide_shipping_title' );

// add_action('storefront_header', 'number', 41);
// add_action('storefront_header', 'cart_icon', 41);

/* removed from template main files*/
remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
// remove_action('storefront_header', 'storefront_product_search');
remove_action( 'storefront_header', 'storefront_skip_links' );
remove_action( 'storefront_header', 'storefront_header_cart' );
// remove_action( 'storefront_header', 'storefront_product_search', 40 );
remove_action( 'storefront_header', 'storefront_header_cart', 60 );

/* removed from template main files*/

function cart_icon(){
    if(wp_is_mobile()){
        echo '<div class="cart-icon ml-auto">
        <span class="icon-show position-relative">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20 7h-4v-3c0-2.209-1.791-4-4-4s-4 1.791-4 4v3h-4l-2 17h20l-2-17zm-11-3c0-1.654 1.346-3 3-3s3 1.346 3 3v3h-6v-3zm-4.751 18l1.529-13h2.222v1.5c0 .276.224.5.5.5s.5-.224.5-.5v-1.5h6v1.5c0 .276.224.5.5.5s.5-.224.5-.5v-1.5h2.222l1.529 13h-15.502z"></path></svg>
        </span>
        </div>';
    }
    
}

function number(){
    if(!wp_is_mobile()){
        echo    '<div class="mobile-no">
                <div class="img-container">
                    <img src="/wp-content/uploads/2020/08/calliconpng-1.png" class="img-responsive"/>
                </div>
                <div class="link-container-header">
                    <a href="tel:'.master_number().'"> '.master_number().'</a>
                </div>      
            </div>';
    }
    
}
function numb(){
	echo '<span class="mobile-no-thankyou"><a href="tel:03111444351" style="color:#333;text-decoration:none;">'.master_number().'</a></span>';
}



function tester(){
    global $post;
    // Check for the custom field value
    
    if(is_product()){
        $product = wc_get_product( $post->ID );
        $title  = $product->get_meta( 'custom_text_field_title' );
        if($title != ''){
            $top            = $product->get_meta( 'custom_text_field_top' );
            $top_mobile     = $product->get_meta( 'custom_text_field_top_mobile' );
            if(wp_is_mobile()){
                $top = $top_mobile;
            }

            echo '<div class="site-branding custom-logo">
                <a href="'.site_url().'">
                    <img src="'.$title.'" style="width:auto;max-width: 140px;position:relative;top: '.$top.'"/>
                </a>
            </div>';
            echo '<style>
            .single-product .site-header .secondary-navigation{top: 10px;}
            .single-product .site-branding{display: none !important;}
            .single-product .site-branding.custom-logo{display: block !important;}
            </style>';
        } 
        
    }else{
        echo " ";
    }
}


function bbloomer_redirect_checkout_add_cart( $url ) {
	$url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) ); 
	return $url;
}
  
add_filter( 'woocommerce_add_to_cart_redirect', 'bbloomer_redirect_checkout_add_cart' );

remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 20 );

add_filter( 'woocommerce_add_to_cart_validation', 'empty_cart_before_adding_new_product' );
function empty_cart_before_adding_new_product( $cart_item_data ) {
 
    global $woocommerce;
    $woocommerce->cart->empty_cart();

    return true;
}
/*
 * Add item to cart on visit
 */
function add_product_to_cart() {
    if ( is_page( 'checkout' ) ) {
          //echo 'check';
          return;
      }
      global $woocommerce, $post;
      //echo WC()->cart->get_cart_contents_count();
      $woocommerce->cart->empty_cart();
      //echo $post->ID;
      WC()->cart->add_to_cart( $post->ID, 1 );
      //$woocommerce->cart->add_to_cart($post->ID,1);
  }
  add_action( 'woocommerce_before_single_product_summary', 'add_product_to_cart' );

// Removes Order Notes Title - Additional Information & Notes Field
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 9999 );



// Remove Order Notes Field
add_filter( 'woocommerce_checkout_fields' , 'remove_order_notes' );

function remove_order_notes( $fields ) {
     unset($fields['order']['order_comments']);
     return $fields;
}


add_action('storefront_after_footer', 'menu_handle', 15);
function menu_handle(){
    echo '<div class="footer-menu-container">';
    echo '<div class="col-full">';
    
    wp_nav_menu(array('theme_location'  => 'secondary',
                    'container_class'   => '',
                    'container'         => 'div'
    ));
    echo '</div>';
    echo '</div>';
    

}

add_action('storefront_after_footer', 'menu_section', 20);

function menu_section(){
    echo 
    '<div class="rowee" style="clear:both;background:#fff;">
        <div class="">
            <div class="footer-copyrights col-full" style="background:#fff;font-size: 0.8rem;padding: 8px 0;">©'.date('Y '). site_name_shortcode().'. All Rights Reserved</div> 
        </div>
    </div>';
}

add_filter( 'woocommerce_product_data_tabs', 'add_my_custom_product_data_tab' , 99 , 1 );
function add_my_custom_product_data_tab( $product_data_tabs ) {
    $product_data_tabs['my-custom-tab'] = array(
        'label' => __( 'My Custom Tab', 'my_text_domain' ),
        'target' => 'my_custom_product_data',
    );
    return $product_data_tabs;
}

function cfwc_create_custom_field() {
    /*
    $args = array(
    'id' => 'custom_text_field_title',
    'label' => __( 'Custom Logo URL', 'cfwc' ),
    'class' => 'cfwc-custom-field',
    'desc_tip' => true,
    'description' => __( 'Enter the url of of the logo to be shown on this product.', 'ctwc' ),
    );
        woocommerce_wp_text_input( $args );
    
    $args = array(
        'id' => 'shipping_element_title',
        'label' => __( 'Enter Shipping Details', 'cfwc' ),
        'class' => 'cfwc-custom-field',
        'desc_tip' => true,
        'description' => __( 'Enter the shipping details.', 'ctwc' )
        );
        woocommerce_wp_text_input( $args );
    $args = array(
            'id' => 'custom_text_field_top',
            'label' => __( 'Custom Logo top', 'cfwc' ),
            'class' => 'cfwc-custom-field',
            'desc_tip' => false,
            );
        woocommerce_wp_text_input( $args );
    $args = array(
            'id' => 'custom_text_field_top_mobile',
            'label' => __( 'Custom Logo top Mobile', 'cfwc' ),
            'class' => 'cfwc-custom-field',
            'desc_tip' => false,
    );
    woocommerce_wp_text_input( $args );
    $args = array(
            'id' => 'product_video_link',
            'label' => __( 'Embeded Video Link', 'cfwc' ),
            'class' => 'cfwc-custom-field',
            'desc_tip' => true,
            'description' => __( 'Enter the video embeded link.', 'ctwc' )
    );
    woocommerce_wp_text_input( $args );*/


    $args = array(
        'id' => 'whatsapp_enable',
        'label' => __( 'Enable Whatsapp', 'cfwc' ),
        'class' => 'cfwc-whatsapp',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );


    $args = array(
        'id' => 'offer_enable',
        'label' => __( 'Enable Offer', 'cfwc' ),
        'class' => 'cfwc-offer',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );

    $args = array(
        'id' => 'enable_header',
        'label' => __( 'Enable Header', 'cfwc' ),
        'class' => 'cfwc-offer',
        'default'=> '1',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );

    $args = array(
        'id' => 'pre_title',
        'label' => __( 'Pre Title', 'cfwc' ),
        'class' => 'pre-title',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );

    $args = array(
        'id' => 'posst_title',
        'label' => __( 'Post Title After', 'cfwc' ),
        'class' => 'post-title-after',
        'desc_tip' => false
    );

    woocommerce_wp_text_input( $args );

    $args = array(
        'id' => 'custom_title',
        'label' => __( 'Custom Title', 'cfwc' ),
        'class' => '',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );

    $args = array(
        'id' => 'offer_name_1',
        'label' => __( 'Offer 1 name', 'cfwc' ),
        'class' => '',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );
    $args = array(
        'id' => 'offer_1_delivery',
        'label' => __( 'Offer 1 Delivery', 'cfwc' ),
        'class' => '',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );
    $args = array(
        'id' => 'offer_name_2',
        'label' => __( 'Offer 2 name', 'cfwc' ),
        'class' => '',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );
    $args = array(
        'id' => 'offer_2_delivery',
        'label' => __( 'Offer 2 Delivery', 'cfwc' ),
        'class' => '',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );
    $args = array(
        'id' => 'popular_text',
        'label' => __( 'Popular text', 'cfwc' ),
        'class' => '',
        'desc_tip' => false
    );
    woocommerce_wp_text_input( $args );
   }
   add_action( 'woocommerce_product_options_general_product_data', 'cfwc_create_custom_field' );

   function cfwc_save_custom_field( $post_id ) {
    $product = wc_get_product( $post_id );
    $title                      = isset( $_POST['custom_text_field_title'] ) ? $_POST['custom_text_field_title'] : '';
    $top                        = isset( $_POST['custom_text_field_top'] ) ? $_POST['custom_text_field_top'] : '';
    $top_mobile                 = isset( $_POST['custom_text_field_top_mobile'] ) ? $_POST['custom_text_field_top_mobile'] : '';
    $shipping_element_title     = isset( $_POST['shipping_element_title'] ) ? $_POST['shipping_element_title'] : '';
    $product_video_link         = isset( $_POST['product_video_link'] ) ? $_POST['product_video_link'] : '';
    $whatsapp_enable            = isset( $_POST['whatsapp_enable'] ) ? $_POST['whatsapp_enable'] : '';
    $offer_enable               = isset( $_POST['offer_enable'] ) ? $_POST['offer_enable'] : '';
    $pre_title                  = isset( $_POST['pre_title'] ) ? $_POST['pre_title'] : '';
    $posst_title                = isset( $_POST['posst_title'] ) ? $_POST['posst_title'] : '';
    $enable_header              = isset( $_POST['enable_header'] ) ? $_POST['enable_header'] : '';
    $custom_title               = isset( $_POST['custom_title'] ) ? $_POST['custom_title'] : '';
    $offer_name_1               = isset( $_POST['offer_name_1'] ) ? $_POST['offer_name_1'] : '';
    $offer_name_2               = isset( $_POST['offer_name_2'] ) ? $_POST['offer_name_2'] : '';
    $offer_1_delivery           = isset( $_POST['offer_1_delivery'] ) ? $_POST['offer_1_delivery'] : '';
    $offer_2_delivery           = isset( $_POST['offer_2_delivery'] ) ? $_POST['offer_2_delivery'] : '';
    $popular_text               = isset( $_POST['popular_text'] ) ? $_POST['popular_text'] : '';

    
    $product->update_meta_data( 'custom_text_field_title', sanitize_text_field( $title ) );
    $product->update_meta_data( 'custom_text_field_top', sanitize_text_field( $top ) );
    $product->update_meta_data( 'custom_text_field_top_mobile', sanitize_text_field( $top_mobile ) );
    $product->update_meta_data( 'shipping_element_title', sanitize_text_field( $shipping_element_title ) );
    $product->update_meta_data( 'product_video_link', sanitize_text_field( $product_video_link ) );
    $product->update_meta_data( 'whatsapp_enable', sanitize_text_field( $whatsapp_enable ) );
    $product->update_meta_data( 'offer_enable', sanitize_text_field( $offer_enable ) );
    $product->update_meta_data( 'pre_title', sanitize_text_field( $pre_title ) );
    $product->update_meta_data( 'posst_title', sanitize_text_field( $posst_title ) );
    $product->update_meta_data( 'enable_header', sanitize_text_field( $enable_header ) );
    $product->update_meta_data( 'custom_title', sanitize_text_field( $custom_title ) );
    $product->update_meta_data( 'offer_name_1', sanitize_text_field( $offer_name_1 ) );
    $product->update_meta_data( 'offer_name_2', sanitize_text_field( $offer_name_2 ) );
    $product->update_meta_data( 'offer_1_delivery', sanitize_text_field( $offer_1_delivery ) );
    $product->update_meta_data( 'offer_2_delivery', sanitize_text_field( $offer_2_delivery ) );
    $product->update_meta_data( 'popular_text', sanitize_text_field( $popular_text ) );
    $product->save();
   }
   add_action( 'woocommerce_process_product_meta', 'cfwc_save_custom_field' );

   function cfwc_display_custom_field() {
    global $post;
    // Check for the custom field value
    $product = wc_get_product( $post->ID );
    $title = $product->get_meta( 'custom_text_field_title' );

   }
//    add_action( 'woocommerce_before_add_to_cart_button', 'cfwc_display_custom_field' );

/**
 * Get shipping methods.
 */
add_action( 'admin_post_nopriv_wc_cart_totals_shipping_html_custom_update', 'wc_cart_totals_shipping_html_custom_update' );
add_action( 'admin_post_wc_cart_totals_shipping_html_custom_update', 'wc_cart_totals_shipping_html_custom_update' );
function wc_cart_totals_shipping_html_custom_update() {

    
    
    // $result = WC()->shipping->get_shipping_methods();
    // wp_send_json($active_methods, 200);
}

function wc_cart_totals_shipping_html_custom() {
	$packages = WC()->shipping()->get_packages();
	$first    = true;

	foreach ( $packages as $i => $package ) {
		$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
		$product_names = array();

		if ( count( $packages ) > 1 ) {
			foreach ( $package['contents'] as $item_id => $values ) {
				$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
			}
			$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
		}

		wc_get_template(
			'cart/cart-shipping.php',
			array(
				'package'                  => $package,
				'available_methods'        => $package['rates'],
				'show_package_details'     => count( $packages ) > 1,
				'show_shipping_calculator' => is_cart() && apply_filters( 'woocommerce_shipping_show_shipping_calculator', $first, $i, $package ),
				'package_details'          => implode( ', ', $product_names ),
				/* translators: %d: shipping package number */
				'package_name'             => apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Delviery %d', 'shipping packages', 'woocommerce' ), ( $i + 1 ) ) : _x( 'Delivery Charges', 'shipping packages', 'woocommerce' ), $i, $package ),
				'index'                    => $i,
				'chosen_method'            => $chosen_method,
				'formatted_destination'    => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
				'has_calculated_shipping'  => WC()->customer->has_calculated_shipping(),
			)
		);

		$first = false;
	}
}

/**
 * Get order total html including inc tax if needed.
 */
function wc_cart_totals_order_total_html_new() {
	$value = WC()->cart->get_total();

	// If prices are tax inclusive, show taxes here.
	if ( wc_tax_enabled() && WC()->cart->display_prices_including_tax() ) {
		$tax_string_array = array();
		$cart_tax_totals  = WC()->cart->get_tax_totals();

		if ( get_option( 'woocommerce_tax_total_display' ) === 'itemized' ) {
			foreach ( $cart_tax_totals as $code => $tax ) {
				$tax_string_array[] = sprintf( '%s %s', $tax->formatted_amount, $tax->label );
			}
		} elseif ( ! empty( $cart_tax_totals ) ) {
			$tax_string_array[] = sprintf( '%s %s', wc_price( WC()->cart->get_taxes_total( true, true ) ), WC()->countries->tax_or_vat() );
		}

		if ( ! empty( $tax_string_array ) ) {
			$taxable_address = WC()->customer->get_taxable_address();
			/* translators: %s: country name */
			$estimated_text = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ? sprintf( ' ' . __( 'estimated for %s', 'woocommerce' ), WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] ) : '';
			$value .= '<small class="includes_tax">('
						/* translators: includes tax information */
						. esc_html__( 'includes', 'woocommerce' )
						. ' '
						. wp_kses_post( implode( ', ', $tax_string_array ) )
						. esc_html( $estimated_text )
						. ')</small>';
		}
	}

	echo apply_filters( 'woocommerce_cart_totals_order_total_html', $value ); // WPCS: XSS ok.
}

function additional_button(){
    global $post, $woocommerce, $product;

    if($product->get_sale_price()){
        $pro_price = $product->get_sale_price();
    }else{
        $product = $product->get_regular_price();
    }
    echo    '<div class="row mt-3 additional_price" style="display:none;">
                <div class="product-price col-5 price" style="float:left;text-align:left;">
                    <del class="mb-0 text-black" style="margin: 0 4px;">'.wc_price($product->get_regular_price()).' </del> 
                    <ins class="mb-0">'.wc_price($pro_price).'</ins>
                </div>
                <div class="col-7">
                <a class="single_add_to_cart_button button alt fixed-button-a text-center" href="'.get_site_url().'/checkout/?add-to-cart='.$post->ID.'">BUY NOW</a>
                </div>
            </div>';

}
if(!wp_is_mobile()){
    add_action('woocommerce_product_thumbnails', 'additional_button');
}
remove_filter( 'woocommerce_single_product_image_gallery_classes', 'filter_woocommerce_single_product_image_gallery_classes', 10, 1 ); 


function storefront_footer_widgets() {
    $rows    = intval( apply_filters( 'storefront_footer_widget_rows', 1 ) );
    $regions = intval( apply_filters( 'storefront_footer_widget_columns', 4 ) );

    for ( $row = 1; $row <= $rows; $row++ ) :

        // Defines the number of active columns in this footer row.
        for ( $region = $regions; 0 < $region; $region-- ) {
            if ( is_active_sidebar( 'footer-' . esc_attr( $region + $regions * ( $row - 1 ) ) ) ) {
                $columns = $region;
                break;
            }
        }

        if ( isset( $columns ) ) :
            ?>
            <div class=<?php echo '"footer-widgets  dddrow-' . esc_attr( $row ) . ' ddddcol-' . esc_attr( $columns ) . ' fix"'; ?>>
            <?php
            for ( $column = 1; $column <= $columns; $column++ ) :
                $footer_n = $column + $regions * ( $row - 1 );

                if ( is_active_sidebar( 'footer-' . esc_attr( $footer_n ) ) ) :
                    ?>
                <div class="block footer-widget-<?php echo esc_attr( $column ); ?>">
                    <?php dynamic_sidebar( 'footer-' . esc_attr( $footer_n ) ); ?>
                </div>
                    <?php
                endif;
            endfor;
            ?>
        </div><!-- .footer-widgets.row-<?php echo esc_attr( $row ); ?> -->
            <?php
            unset( $columns );
        endif;
    endfor;
}


function my_hide_shipping_when_free_is_available( $rates ) {
	$free = array();
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'free_shipping' === $rate->method_id ) {
			$free[ $rate_id ] = $rate;
			break;
		}
	}
	return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );



/***************************************
 ***************************************
 Start Copy from here for Admin Pannel *
 ***************************************
 ***************************************/


add_action('admin_head', 'hide_delete_note_from_edit_order');
function hide_delete_note_from_edit_order()
{
    $screen = get_current_screen();
    if ($screen->post_type === "shop_order" && $screen->base === "post") {
        echo '<style>a.delete_note { display:none; }</style>';
    }
}

add_action( 'wp_loaded', 'my_remove_bulk_actions' );
function my_remove_bulk_actions() {
if ( ! is_admin() )
   return;

if ( ! current_user_can( 'administrator' ) ) {
  add_filter( 'bulk_actions-edit-shop_order', '__return_empty_array', 100 );

 }
}



add_filter( 'manage_edit-shop_order_columns', 'custom_shop_order_column', 90 );
function custom_shop_order_column( $columns )
{
    $ordered_columns = array();
    // $current_post_status = get_post_status();
    // echo $current_post_status; //publish

    foreach( $columns as $key => $column ){
        $ordered_columns[$key] = $column;
        if( 'order_status' == $key ){
            $ordered_columns['order_notes'] = __( 'Reason / Agent', 'woocommerce');
        }
    }

    return $ordered_columns;
}

add_action( 'manage_shop_order_posts_custom_column' , 'custom_shop_order_list_column_content', 10, 1 );
function custom_shop_order_list_column_content( $column )
{
    global $post, $the_order;
    

    $customer_note = $post->post_excerpt;

    if ( $column == 'order_notes' ) {

        if ( $the_order->get_customer_note() ) {
            echo '<span class="note-on customer tips" data-tip="' . wc_sanitize_tooltip( $the_order->get_customer_note() ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>';
        }
        $order_status  = $the_order->get_status();
        if($order_status == 'hold' || $order_status == 'cancelledc'){
            if ( $post->comment_count ) {

                $latest_notes = wc_get_order_notes( array(
                    'order_id' => $post->ID,
                    'limit'    => 1,
                    'orderby'  => 'date_created_gmt',
                ) );
    
                $latest_note = current( $latest_notes );
                // print_r($latest_note);
    
                if ( isset( $latest_note->content ) && 1 == $post->comment_count ) {
                    // echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip( $latest_note->content ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>';
                } elseif ( isset( $latest_note->content ) ) {
                    // translators: %d: notes count
                    // echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip( $latest_note->content . '<br/><small style="display:block">' . sprintf( _n( 'Plus %d other note', 'Plus %d other notes', ( $post->comment_count - 1 ), 'woocommerce' ), $post->comment_count - 1 ) . '</small>' ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>'.$latest_note->content;
                    echo $latest_note->content . '<div class="text-light"><small>' . $latest_note->added_by . '</small></div>';
                     
                } else {
                    // translators: %d: notes count
                    echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip( sprintf( _n( '%d note', '%d notes', $post->comment_count, 'woocommerce' ), $post->comment_count ) ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>';
                }
            }   
        }elseif($order_status == 'confirmed' || $order_status == 'notresponding'){
            if ( $post->comment_count ) {

                $latest_notes = wc_get_order_notes( array(
                    'order_id' => $post->ID,
                    'limit'    => 1,
                    'orderby'  => 'date_created_gmt',
                ) );
    
                $latest_note = current( $latest_notes );
                // print_r($latest_note);
    
                if ( isset( $latest_note->content ) && 1 == $post->comment_count ) {
                    // echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip( $latest_note->content ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>';
                } elseif ( isset( $latest_note->content ) ) {
                    // translators: %d: notes count
                    // echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip( $latest_note->content . '<br/><small style="display:block">' . sprintf( _n( 'Plus %d other note', 'Plus %d other notes', ( $post->comment_count - 1 ), 'woocommerce' ), $post->comment_count - 1 ) . '</small>' ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>'.$latest_note->content;
                    echo '<div class="text-light">' . $latest_note->added_by . '</div>';
                     
                } else {
                    // translators: %d: notes count
                    echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip( sprintf( _n( '%d note', '%d notes', $post->comment_count, 'woocommerce' ), $post->comment_count ) ) . '">' . __( 'Yes', 'woocommerce' ) . '</span>';
                }
            }
        }
        
    }
}

// Set Here the WooCommerce icon for your action button
add_action( 'admin_head', 'add_custom_order_status_actions_button_css' );
function add_custom_order_status_actions_button_css() {
    echo '<style>
    td.order_notes > .note-on { display: inline-block !important;}
    span.note-on.customer { margin-right: 4px !important;}
    span.note-on.customer::after { font-family: woocommerce !important; content: "\e026" !important;}
    table.wp-list-table .column-customer_message, table.wp-list-table .column-order_notes{text-align:left;width:75px;}
    .post-type-shop_order .wp-list-table .column-order_date{width: 3ch;}
    .post-type-shop_order .wp-list-table .column-order_status{width: 4ch;}
    .post-type-shop_order .wp-list-table .column-order_number{width: 16ch;}
    .widefat .column-wc_actions a.wc-action-button-invoice.invoice::after{content: "\f680"  !important;font-family: "WC-SA-Icons" !important}
    .widefat .column-wc_actions a.wc-action-button-secondary_no.secondary_no::after{content: "\f680"  !important;font-family: "WC-SA-Icons" !important}
    </style>';

    // echo '<script>alert("hello");</script>';

    $action_slug = "invoice"; // The key slug defined for your action button

    echo '<style>.wc-action-button-'.$action_slug.'::after { font-family: woocommerce !important; content: "\e029" !important; }</style>';
}


function custom_admin_js() {
    echo "<script type='text/javascript' > 
document.body.className+=' folded';                 
</script>";

}
add_action('admin_footer', 'custom_admin_js');


/* Admin Whatsapp order status*/
// add_filter( 'woocommerce_admin_order_actions', 'add_custom_order_status_actions_button', 100, 2 );
function add_custom_order_status_actions_button( $actions, $order ) {
    $whatsapp = 'https://wa.me/92'. $order->get_billing_phone();
    // echo $order->get_status();
    if ( $order->has_status( array( 'notresponding' ) ) ) {

        // The key slug defined for your action button
        $action_slug = 'invoice';
         $status = $_GET['status'];
         $order_id = method_exists($the_order, 'get_id') ? $the_order->get_id() : $the_order->id;
        // Set the action button
        $actions[$action_slug] = array(
            'url'       => $whatsapp,
            'name'      => __( 'WhatsApp', 'woocommerce' ),
            'action'    => $action_slug,
            'target'    => '_blank',
        );
    }
    $order_meta = get_post_meta($order->get_id());
    $secondary_mobile = $order_meta['billing_alternate_number'][0];

    if ( $secondary_mobile && $order->has_status( array( 'notresponding' ) ) ) {
        $sec_whatsapp = 'https://wa.me/92'. $secondary_mobile;
        // The key slug defined for your action button
        $action_slug = 'secondary_no';
         $order_id = method_exists($the_order, 'get_id') ? $the_order->get_id() : $the_order->id;
        // Set the action button
        $actions[$action_slug] = array(
            'url'       => $sec_whatsapp,
            'name'      => __( 'WhatsApp 2nd Mobile', 'woocommerce' ),
            'action'    => $action_slug,
            'target'    => '_blank',
        );
    }

    return $actions;
}

/***************************************
 ***************************************
 End Copy from here for Admin Pannel *
 ***************************************
 ***************************************/
function hide_wc_order_statuses( $order_statuses ) {

    // Hide core statuses
    unset( $order_statuses['wc-refunded'] );
    unset( $order_statuses['wc-failed'] );
    unset( $order_statuses['wc-on-hold'] );
    unset( $order_statuses['wc-cancelled'] );
    unset( $order_statuses['wc-pending'] );
    unset( $order_statuses['wc-processing'] );
    unset( $order_statuses['wc-completed'] );

    return $order_statuses;
}
add_filter( 'wc_order_statuses', 'hide_wc_order_statuses' );


