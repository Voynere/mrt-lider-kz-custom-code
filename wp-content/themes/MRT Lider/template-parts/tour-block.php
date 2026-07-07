<?php
/**
 * 3D tour promo block for standard (non-animals) branches.
 */
$allowed_tags = array('h2', 'h3', 'p');
$heading_tag = isset($args['heading_tag']) && in_array($args['heading_tag'], $allowed_tags, true)
    ? $args['heading_tag']
    : 'h2';
$theme_uri = get_template_directory_uri();
?>
<div class="tour">
    <div class="container">
        <div class="tour__inner">
            <img src="<?php echo esc_url($theme_uri . '/assets/img/3d_tour.jpg'); ?>" alt="">
            <a href="#" class="tour__content">
                <<?php echo $heading_tag; ?> class="tour__title">
                    <span>ПРОЙДИТЕ 3D ТУР</span>
                    <span>ПО КЛИНИКЕ</span>
                    <span>«МРТ ЛИДЕР»</span>
                </<?php echo $heading_tag; ?>>
                <div class="tour__play">
                    <img src="<?php echo esc_url($theme_uri . '/assets/img/play_video.svg'); ?>" alt="">
                </div>
            </a>
        </div>
    </div>
</div>
