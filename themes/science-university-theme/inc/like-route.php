<?php

add_action('rest_api_init', 'su_like_routes');

function su_like_routes()
{

    register_rest_route('su/v1', 'manageLike', array(

        'methods'   => 'POST',
        'callback'  =>  'createLike'
    ));
    register_rest_route('su/v1', 'manageLike', array(

        'methods'   => 'DELETE',
        'callback'  =>  'deleteLike'
    ));
}

function createLike($data)
{

    if (is_user_logged_in()) {
        $professorId = sanitize_text_field($data['professorId']);

        $existQuery = new WP_Query(array(
            'author'    => get_current_user_id(),
            'post_type' => 'like',
            'meta_query'   => array(
                array(
                    'key'       => 'liked_professor_id',
                    'compare'   => '=',
                    'value'     => $professorId
                )
            )
        ));

        if ($existQuery->found_posts == 0 && get_post_type($professorId) == 'professor') {
            //create new like post

            return wp_insert_post(array(
                'post_type'     => 'like',
                'post_status'   => 'publish',
                'post_title'    => '2nd test',
                'meta_input'    => array(
                    'liked_professor_id'  => $professorId
                )

            ));
        } else {

            die("Invalid professor Id");
        }
    } else {

        die('Only logeed in users can create a like');
    }
}

function deleteLike($data)
{

    $likeId = sanitize_text_field($data['like']);

    if (get_current_user_id() == get_post_field('post_author', $likeId) && get_post_type($likeId) == 'like') {
        wp_delete_post($likeId, true);
        return "Congrats, like deleted";
    } else {
        die("You do not have permissions to delete that");
    }
}
