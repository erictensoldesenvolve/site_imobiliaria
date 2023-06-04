<?php
/**
 * The template for Locations Management.
 *
 * This is the template that table, search layout
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap wdk-wrap">
    <h1 class="wp-heading-inline"><?php echo __('Locations','wpdirectorykit'); ?> <a href="<?php echo get_admin_url() . "admin.php?page=wdk_location&function=edit"; ?>" class="button button-primary" id="add_location_button"><?php echo __('Add location','wpdirectorykit'); ?></a></h1>
    <br />
    <?php
        $success_message = NULL;
        if(isset($_GET['custom_message']))
            $success_message = esc_html(urldecode($_GET['custom_message']));

        $form->messages('class="alert alert-danger"', $success_message);
    ?>
    <br />
    <form method="GET" action="<?php echo wmvc_current_edit_url(); ?>" novalidate="novalidate">
        <table class="wp-list-table widefat fixed striped table-view-list pages">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php echo __('Select All', 'wpdirectorykit'); ?></label><input id="cb-select-all-1" type="checkbox"></td>
                    <th><?php echo __('Title','wpdirectorykit'); ?></th>
                    <th><?php echo __('Order','wpdirectorykit'); ?></th>
                    <th><?php echo __('Level','wpdirectorykit'); ?></th>
                    <th><?php echo __('Date','wpdirectorykit'); ?></th>
                    <th class="actions_column"><?php echo __('Actions','wpdirectorykit'); ?></th>
                </tr>
            </thead>

            <?php if(count($locations) == 0): ?>
                <tr class="no-items"><td class="colspanchange" colspan="5"><?php echo __('No Locations found.','wpdirectorykit'); ?></td></tr>
            <?php endif; ?>

            <?php foreach ( $locations as $location ):?>
                <tr>
                    <th scope="row" class="check-column">
                        <input id="cb-select-<?php echo wmvc_show_data('idlocation', $location, '-'); ?>" type="checkbox" name="post[]" value="<?php echo wmvc_show_data('idlocation', $location, '-'); ?>">
                        <div class="locked-indicator">
                            <span class="locked-indicator-icon" aria-hidden="true"></span>
                            <span class="screen-reader-text"><?php echo __('Is Locked', 'wpdirectorykit'); ?></span>
                        </div>
                    </th>
                    <td>
                        <?php echo str_pad('', wmvc_show_data('level', $location, 0)*12, '&nbsp;').'|-'; ?><a href="<?php echo get_admin_url() . "admin.php?page=wdk_location&function=edit&id=".wmvc_show_data('idlocation', $location, '-'); ?>"><?php echo wmvc_show_data('location_title', $location, '-').' #'.wmvc_show_data('idlocation', $location, '-'); ?></a>
                    </td>
                    <td>
                        <?php echo wmvc_show_data('order_index', $location, '-'); ?>
                    </td>
                    <td>
                        <?php echo wmvc_show_data('level', $location, '-'); ?>
                    </td>
                    <td>
                        <?php echo wdk_get_date(wmvc_show_data('date', $location), false); ?>
                    </td>

                    <td class="actions_column">
                        <a href="<?php echo get_admin_url() . "admin.php?page=wdk_location&function=edit&id=".wmvc_show_data('idlocation', $location, '-'); ?>" title="<?php echo esc_attr__('Edit','wpdirectorykit');?>"><span class="dashicons dashicons-edit"></span></a>
                        <a class="question_sure" href="<?php echo get_admin_url() . "admin.php?page=wdk_location&function=delete&id=".wmvc_show_data('idlocation', $location, '-'); ?>&_wpnonce=<?php echo wp_create_nonce( 'wdk-location-delete_'.wmvc_show_data('idlocation', $location, '-'));?>" title="<?php echo esc_attr__('Remove','wpdirectorykit');?>"><span class="dashicons dashicons-no"></span></a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-2"><?php echo __('Select All', 'wpdirectorykit'); ?></label><input id="cb-select-all-2" type="checkbox"></td>
                    <th><?php echo __('Title','wpdirectorykit'); ?></th>
                    <th><?php echo __('Order','wpdirectorykit'); ?></th>
                    <th><?php echo __('Level','wpdirectorykit'); ?></th>
                    <th><?php echo __('Date','wpdirectorykit'); ?></th>
                    <th class="actions_column"><?php echo __('Actions','wpdirectorykit'); ?></th>
                </tr>
            </tfoot>
        </table>
        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <?php wp_nonce_field( 'wdk-location-bulk', '_wpnonce'); ?>
                <label for="bulk-action-selector-bottom" class="screen-reader-text"><?php echo __('Select bulk action', 'wpdirectorykit'); ?></label>
                <select name="action" id="bulk-action-selector-bottom">
                    <option value="-1"><?php echo __('Bulk actions', 'wpdirectorykit'); ?></option>
                    <option value="delete" class="hide-if-no-js"><?php echo __('Delete', 'wpdirectorykit'); ?></option>
                </select>
                <input type="hidden" name="page" value="wdk_location" />
                <input type="submit" id="table_action" class="button action" name="table_action" value="<?php echo esc_attr__('Apply', 'wpdirectorykit'); ?>">
            </div>
            <br class="clear">
        </div>
    </form>
</div>

<script>
    // Generate table
    jQuery(document).ready(function($) {
        $('.question_sure').on('click', function(){
            return confirm("<?php echo esc_js(__('Are you sure? Selected item will be completely removed!','wpdirectorykit')); ?>");
        });
    });
</script>

<?php $this->view('general/footer', $data); ?>
