<form action="<?php echo $post_url ?>" method="post" style="max-width:650px;">

    <?php foreach ($columns as $col) :
        if ($col->getName() === $primary_key) continue; ?>

        <div class="row mb-2">

            <div class="col-3">
                <label for="varchar_col"><?php echo titleCase($col->getName()) ?></label>
            </div>

            <div class="col-9">
                <!-- Input Outputing -->

                <?php if ($col->isTextArea()) : ?>

                    <textarea class="form-control" name="<?php echo $col->getName() ?>"></textarea>

                <?php elseif ($col->isEnum()) : ?>

                    <select class="form-select" name="<?php echo $col->getName() ?>">

                        <?php foreach ($col->getAllowedValues() as $value) : ?>

                            <option value="<?php echo $value ?>" <?php echo $values[$col->getName()]->getvalue() === $value ? 'selected' : '' ?>>

                                <?php echo titleCase($value) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                <?php elseif ($col->isSet()) : ?>

                    <?php foreach ($col->getAllowedValues() as $value) :
                        $checked = null;
                        if ($values[$col->getName()]) {
                            $checked = $values[$col->getName()]->isValueExists($value);
                        }
                    ?>

                        <input type="checkbox" class="form-checkbox" name="<?php echo ($col->getName() . '[]') ?>" value="<?php echo $value ?>" <?php echo $checked ? "checked" : '' ?>>

                        <label>
                            <?php echo titleCase($value) ?>
                        </label>

                    <?php endforeach; ?>

                <?php else : ?>
                    <input type="<?php echo $col->getInputType() ?>" name="<?php echo $col->getName() ?>" class="form-control" name="varchar_col" id="varchar_col" value="<?php echo $values[$col->getName()]->getValue() ?>">

                <?php endif; ?>


            </div>
        </div>
    <?php endforeach ?>


    <div class="col-9 ms-auto">
        <button type="submit" class="btn btn-primary w-100">Save</button>
    </div>
</form>