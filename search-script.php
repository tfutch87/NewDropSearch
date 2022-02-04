<?php 

// used for styling
function themeslug_enqueue_style() {
    wp_enqueue_style( 'my-search', get_template_directory_uri() . '/assets/css/hico-search.css', false );
}
 

// used for javascript

function themeslug_enqueue_script() {
    wp_enqueue_script( 'my-js', get_template_directory_uri() . '/assets/js/Search.js',  array(), '1.0.0', true  );
}
 
add_action( 'wp_enqueue_scripts', 'themeslug_enqueue_style' );
add_action( 'wp_enqueue_scripts', 'themeslug_enqueue_script' );


// add endpoint
add_action( 'rest_api_init', 'api_getShoes');


function api_getShoes(){

    // create custom endpoint

        register_rest_route('newdropsearch/v1', 'search', array(

            'methods'  => WP_REST_SERVER::READABLE,
            'callback'  => 'newDropSearchResults'



        ));

}

// return data $json
function newDropSearchResults($data){

$mainQuery = new WP_Query(array(

    'post_type' => array('shoes', 'product'),
    'hide_empty'     => 1,
    'depth'          => 1,
    's' => sanitize_text_field($data['term'])
));


// json returned with all the data from the search 
$mainQueryResults = array(


'shoes' => array(),
'products' => array()


);

while($mainQuery->have_posts()){
    $mainQuery->the_post();

// checks the post type and pushes the data onto the main query array
    if(get_post_type() == 'shoes'){

        array_push($mainQueryResults['shoes'], array(

            'shoe_name' => get_field('name'),
            'brand' => get_field('brand'),
            'release_date' => get_field('release_date'),
            'image' => get_field('image'),
            'colorway' => get_field('colorway'),
            'link' => get_the_permalink(),
            'post_type' => get_post_type()
    
        ));


    }

    // checks the post type and pushes the data onto the main query array

    if(get_post_type() == 'product'){

        $author_id = get_post_field( 'post_author', get_the_id() );
        $store_url = dokan_get_store_url( $author_id );
        $categories = get_the_terms( get_the_ID(), 'product_cat' );
        $Vendors_Photo = get_avatar_url(  $author_id );

        array_push($mainQueryResults['products'], array(

            'product_name'  => get_the_title(),
            'product_img' => wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()), 'thumbnail' ),
            'product_url' => get_the_permalink(),
             'shop_url' =>  $store_url,
            'Vendor_Photo' => $Vendors_Photo,
            'store_name' => dokan_get_store_info($author_id)['store_name'],
            'post_type' => get_post_type()
                                    


    ) );


    }


}


return $mainQueryResults;


}