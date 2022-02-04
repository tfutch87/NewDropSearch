<?php

// events for new shoes

function Shoes()
{
    $labels = array(
        'name'               => _x('Shoe', 'post type general name'),
        'singular_name'      => _x('Shoe', 'post type singular name'),
        'add_new'            => _x('Add New', 'Shoe'),
        'add_new_item'       => __('Add New Shoe'),
        'edit_item'          => __('Edit Shoes'),
        'new_item'           => __('New Shoe'),
        'all_items'          => __('All Shoes'),
        'view_item'          => __('View Shoes'),
        'search_items'       => __('Search Shoes'),
        'not_found'          => __('No Shoes found'),
        'not_found_in_trash' => __('No Shoes found in the Trash'),
        'parent_item_colon'  => '',
        'menu_name'          => 'Shoes'
    );
    $args = array(
        'labels'        => $labels,
        'description'   => 'Holds upcoming and on-demand Shoes.',
        'public'        => true,
        'menu_position' => 2,
        'show_in_rest'  => true,
        'supports'      => array('title'),
        'has_archive'   => true,
        'exclude_from_search' => false,
    );
    register_post_type('Shoes', $args);
}
add_action('init', 'Shoes');



add_action('wp_ajax_norpiv_getCount', 'getCount');

add_action('wp_ajax_getCount', 'getCount');


// returns the count of shoes loop through pages

function getCount()
{


    $args = array(
        'headers' => array(
            'x-rapidapi-host' => 'the-sneaker-database.p.rapidapi.com',
            'x-rapidapi-key' => '86a1190e61msh425a1f23165e5bfp1da1e5jsn5b6357537a3f'
        )
    );

    $currentPage =  0;
    $shoes = [];

    $url = 'https://the-sneaker-database.p.rapidapi.com/sneakers?limit=100&releaseYear=2021&page=' . $currentPage;

    $reponseBody = wp_remote_retrieve_body(wp_remote_get($url, $args));


    $reponseJson = (json_decode($reponseBody));


    GetShoesRequest( $reponseJson->count );



}

// returns the shoe object from the shoe API passing in PAGE number

function GetShoesRequest($count){


   

    $currentPage =  0;
    $shoes = [];

    while($currentPage <= 1){

        sleep(7);

        $args = array(
            'headers' => array(
                'x-rapidapi-host' => 'the-sneaker-database.p.rapidapi.com',
                'x-rapidapi-key' => '86a1190e61msh425a1f23165e5bfp1da1e5jsn5b6357537a3f'
            )
        );

        $url = 'https://the-sneaker-database.p.rapidapi.com/sneakers?limit=100&releaseYear=2021&page=' . $currentPage;

        $reponseBody = wp_remote_retrieve_body(wp_remote_get($url, $args));
    
        
        $shoeResults = (json_decode($reponseBody));
      

        

            // writeToFile($currentPage, $url  );
           
            $shoes[] = $shoeResults->results;

            foreach($shoes[0] as $shoe){

               
                $shoe_slug = sanitize_title( $shoe->name . " - " . $shoe->sku);

                
                // writeToFile($currentPage, $shoe);

                $inserted_shoe = wp_insert_post([

                    'post_name' => $shoe_slug,
                    'post_title' => $shoe->name,
                    'post_type' => 'shoes',
                    'post_status' => 'publish'
                ]);

                if(is_wp_error( $inserted_shoe )){

                            continue;

                }else{


                    $image = sanitize_url($shoe->image->original); 

                    // TODO add gender field , shoe
                    

                        $fillable = [
                            
                                'field_61e1077c92cdc' => 'id',
                                'field_61e1075e92cdb' => 'sku',
                                'field_61e107d492ce0' => 'brand',
                                'field_61e1071092cda' => 'name',
                                'field_61e1079c92cde' => 'colorway',
                                'field_61e4bc214275e' => 'releaseYear',
                                'field_61e107ba92cdf' => 'releaseDate'
                                

                        ];
                        

                foreach($fillable as $key => $name ){



                update_field($key, $shoe->$name, $inserted_shoe);
                update_field('field_61e107e292ce1', $image, $inserted_shoe);


                }



                };


            };
        
           

        $currentPage++;

        

    };





}




function writeToFile($count, $data){


    $file = get_stylesheet_directory() . '/report4.txt';


          
      file_put_contents($file, $count . ' current page is ' . $data . "\n\n", FILE_APPEND);

   

    $adminUrl = admin_url('admin-ajax.php?action=getCount');

    $admin_agrs = [

        'blocking' => false,
        'sslverify' => false,
        'body' => [

            'currentPage' => $count,

        ]

    ];


    // wp_remote_post($adminUrl, $admin_agrs);

    //   if( $currentPage <= $shoeCount){

    //     $currentPage = $currentPage += 1;

    //     }   

}

// add_action( 'init', 'GetShoesRequest' );

