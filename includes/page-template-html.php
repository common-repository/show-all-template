<?php
/**
* Show All Page Template
* Admin Page HTML view
* @since 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

?>
<div class="wrap">

    <h2>Page Templates</h2>
    <br/><br/>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th width="08%"><strong>S no.</strong></th>
                <th width="46%"><strong>Template Name</strong></th>
                <th><strong>File Name</strong></th>
                <th><strong>Count</strong></th>
            </tr>
        </thead>
        <tbody>
			<?php 

            $sno=0; 
            if($templates):
                
            $get_all_template =  $this->sapt_get_page_by_template();

            foreach ($templates as $template_name => $template_filename) : 
                $sno++; 
                $template_name_data = explode('/', $template_name);
                $temp_filename = end($template_name_data);
                // Generate a nonce
                $nonce = wp_create_nonce( 'total_count_url' );
                $total_count_url = home_url('/wp-admin/edit.php?post_type=page&page_template=' . $temp_filename. '&nonce=' . $nonce);
                ?>
				<tr>
                    <td><?php echo esc_html( $sno ); ?></td>
                    <td><?php echo esc_html($template_filename); ?></td>
                    <td><?php echo esc_html($template_name); ?></td>
                    <td>
                        <?php 
                        if(isset($get_all_template[$temp_filename])){
                            echo '<a href="' . esc_url($total_count_url) . '">' . esc_html(count($get_all_template[$temp_filename])) . '</a>';
                        }else{
                            echo '0';
                        } ?>
                    </td>
                </tr>
			<?php endforeach; endif;?>
		</tbody>
    </table>
</div>