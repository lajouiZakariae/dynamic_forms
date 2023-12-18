<?php

use Core\Table;

require './boot.php';
require APP_DIR . '/inc/header.php';

?>

<div class="container-fluid">

    <?php Table::render('empty'); ?>

</div>

<?php
require APP_DIR . '/inc/footer.php';
