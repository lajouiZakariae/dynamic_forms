<?php

use Core\Form;

require './config.php';
require APP_DIR . '/helpers/http.php';
require APP_DIR . '/helpers/files.php';
require APP_DIR . '/helpers/database.php';
require APP_DIR . '/helpers/response.php';
require APP_DIR . '/vendor/autoload.php';
require APP_DIR . '/inc/header.php';
?>

<div class="container pt-3">

    <?php


    ?>
    <?php Form::render('empty'); ?>

</div>

<?php
require APP_DIR . '/inc/footer.php';
