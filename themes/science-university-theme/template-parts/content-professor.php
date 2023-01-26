<div class="post-item">
    <li class="professor-card__list-item">
        <a class="professor-card" href="<?php the_permalink(); ?>">
            <img class="profesor-card__image" src="<?php the_post_thumbnail_url('professorLandscape'); ?>" alt="">
            <span class="profesor-card__name"><?php the_title(); ?></span>
        </a>
    </li>
</div>