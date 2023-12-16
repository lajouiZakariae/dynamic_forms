<?php

use Core\DB;
use Core\Table;

require '../config.php';
require APP_DIR . '/functions.php';
require APP_DIR . '/vendor/autoload.php';
spl_autoload_register(function ($class) {
    require APP_DIR . '/Core/' . explode('\\', $class)[1] . '.php';
});
require APP_DIR . '/inc/header.php';

?>

<div class="container-fluid">
    <div class="row">
        <!-- <div class="col-2"></div> -->
        <div class="col px-5 py-4">
            <?php
            /**
             * If table name is not provided
             * it will take the name of the parent directory as a table name
             */
            Table::render('users'/* table name */);
            // Table::writeFile('users'/* table name */);
            ?>

        </div>
    </div>
</div>
<?php require '../inc/footer.php';
