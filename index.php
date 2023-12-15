<?php

use Core\DB;

require 'config.php';
require APP_DIR . '/functions.php';
spl_autoload_register(function ($class) {
    require APP_DIR . '/Core/' . explode('\\', $class)[1] . '.php';
});
require APP_DIR . '/inc/header.php';

?>


<div class="container">
    <?php

    dump(DB::table('table_name')->getColumnsWithTypes()[5]->isNumeric());
    dump(DB::table('table_name')->getColumnsWithTypes()[5]);

    ?>
</div>


<?php include APP_DIR . "/inc/footer.php"; ?>