<?php

use Core\DB;

$tables = DB::table("information_schema.TABLES")
    ->whereEquals("TABLE_SCHEMA", DBNAME)
    ->all(["TABLE_NAME"]);

$tables_names = array_column($tables, 'TABLE_NAME');
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">DB</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($tables_names as $name) : ?>
                    <li class="nav-item dropdown">

                        <a class="nav-link dropdown-toggle <?php echo isCurrentPage($name) ? 'active' : '' ?>" <?php echo isCurrentPage($name) ? 'aria-current="page"' : '' ?> href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">

                            <?php echo titleCase($name) ?>

                        </a>

                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?php echo '/' . $name . '/' ?>">Index</a></li>
                            <li><a class="dropdown-item" href="<?php echo '/' . $name . '/post.php?action=create' ?>">Create</a></li>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>
    </div>
</nav>