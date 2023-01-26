<?php get_header();

while (have_posts()) {
    the_post();
    $photo = get_field('slide_image')['sizes']['slideHome'];
    pageBanner(array(
        'title'        => get_field('slide_title'),
        'subtitle'     => get_field('slide_subtitle'),
        'photo'        => $photo
    ));
?>


    <div class="container container--narrow page-section">

        <div class="generic-content">
            <?php the_content(); ?>
        </div>

    </div>

<?php
}
get_footer();
?>