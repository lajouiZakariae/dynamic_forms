<?php

use Core\Form;

require '../config.php';
require '../boot.php';
require APP_DIR . '/inc/header.php';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-2">
        </div>
        <div class="col-10 px-5 py-4">
            <?php
            /**
             * If table name is not provided
             * it will take the name of the parent directory as a table name
             */

            Form::render('table_name'/* table name */);
            ?>
        </div>
    </div>
</div>
<?php require '../inc/footer.php';
