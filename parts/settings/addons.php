<?php

namespace BrownBox\Express;

/*
 Title: Addons list
 Setting: bbx-addons
 */

$bb_express = new Express();

$addons = $bb_express->get_all_addons();

?>

<table class="widefat">

    <thead>

        <tr>
            <th class="row-title">

                <fieldset>
                    <legend class="screen-reader-text"><span>Fieldset Example</span></legend>
                    <label for="users_can_register">
                        <input name="" type="checkbox" id="users_can_register" value="1" />
                    </label>
                </fieldset>

            </th>

            <th class="row-title">Addon name</th>
            <th class="row-title">Dependenices</th>
            <th class="row-title">Actions</th>
        </tr>

    </thead>

    <tbody>

        <?php foreach ( $addons as $addon ) : ?>

            <?php

            $name = $addon->get_name();
            $dependencies = $addon->get_dependencies();
            $version = $addon->get_current_version();
            $description = $addon->get_description();

            ?>

            <tr>

                <!-- Enabled ? -->
                <td class="row-title">
                    <input name="" type="checkbox" id="users_can_register" value="1" />
                </td>
                <!-- END: Enabled ? -->

                <!-- Addon name -->
                <td class="row-title">
                    <?= $name ?> <span class="badge"><?= $version ?></span>
                    <p><small><?= $description ?></small></p>
                </td>
                <!-- END: Addon name -->

                <!-- Addon dependencies -->
                <td>

                    <?php if ( ! empty( $dependencies ) ) : ?>

                        <?php foreach ( $dependencies as $dependency ) : ?>
                            <small><?= $dependency->get_name(); ?></small><br/>
                        <?php endforeach; ?>

                    <?php else : ?>

                        <small>No dependenies</small>

                    <?php endif; ?>


                </td>
                <!-- END: Addon dependencies -->

            </tr>

        <?php endforeach; ?>

    </tbody>

    <tfoot>

        <tr>
            <th class="row-title">

                <fieldset>
                    <legend class="screen-reader-text"><span>Fieldset Example</span></legend>
                    <label for="users_can_register">
                        <input name="" type="checkbox" id="users_can_register" value="1" />
                    </label>
                </fieldset>

            </th>

            <th class="row-title">Addon name</th>
            <th class="row-title">Dependenices</th>
            <th class="row-title">Actions</th>
        </tr>

    </tfoot>

</table>