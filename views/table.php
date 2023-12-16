<div>
    <a class="btn btn-primary" href="post.php">Add</a>
    <table class="table text-center">
        <thead>
            <thead>
                <tr>
                    <!-- Headers -->
                    <?php foreach ($columns as $column) : ?>
                        <th><?php echo $column->getName() ?></th>
                    <?php endforeach; ?>
                    <th colspan="2">Actions</th>
                </tr>
            </thead>
        </thead>
        <tbody>
            <?php foreach ($data as $item) : ?>
                <tr>
                    <!-- Data -->
                    <?php foreach ($item as $value) : ?>
                        <td>
                            <?php echo $value ?>
                        </td>
                    <?php endforeach; ?>

                    <td>
                        <a href="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $current_page . '&action=delete' . '&' . $primary_key . '=' . $item->id ?>">

                            <i class="fas fa-trash text-light bg-danger d-flex justify-content-center align-items-center" style="border-radius:50%; width:32px;height:32px;cursor:pointer;"></i>

                        </a>
                    </td>

                    <td>
                        <a href="<?php echo 'post.php?action=edit&' . $primary_key . '=' . $item->id ?>">

                            <i class="fas fa-pencil text-light bg-primary d-flex justify-content-center align-items-center" style="border-radius:50%; width:32px;height:32px;cursor:pointer;"></i>

                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($last) : ?>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $previous_url ? '' : 'disabled' ?>">
                    <a class="page-link" href="<?php echo $previous_url ?>" <?php echo $previous_url ? '' : 'aria-disabled="true"' ?>>
                        Previous
                    </a>
                </li>

                <?php foreach ($links as $link) : ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $link->url ?>">
                            <?php echo $link->page ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <li class="page-item <?php echo $next_url ? '' : 'disabled' ?>">
                    <a class="page-link" href="<?php echo $next_url ?>" <?php echo $next_url ? '' : 'aria-disabled="true"' ?>>
                        Next
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif ?>

</div>