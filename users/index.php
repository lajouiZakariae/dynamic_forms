<?php

use Core\Table;

require '../config.php';
require '../functions.php';
require '../Core/DB.php';
require '../Core/SQLQuery.php';
require '../Core/Renderer.php';
require '../Core/Form.php';
require '../Core/Table.php';
require '../Core/Request.php';
require '../inc/header.php';

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-2"></div>
        <div class="col-10 px-5 py-4">
            <?php
            /**
             * I table name is not provided
             * it will take the name of the parent directory as a table name
             */
            Table::render(/* table name */) ?>
        </div>
    </div>
</div>
<?php require '../inc/footer.php';
