<?php

class WC_Gateway_Upg_Riskclass
{
    public function __construct()
    {
        require_once(plugin_dir_path( __FILE__ ) . '/vendor/autoload.php');
        add_filter( 'woocommerce_product_options_general_product_data', array($this, 'addProductRiskClassField') );
        add_action( 'save_post', array($this, 'saveProductFields') );

        add_action( 'edit_user_profile', array($this, 'addCustomerRiskClassField'));
        add_action( 'personal_options_update', array($this, 'saveCustomerRiskClass'));
        add_action( 'edit_user_profile_update', array($this, 'saveCustomerRiskClass'));
    }

    public function addCustomerRiskClassField($user)
    {
        if ( current_user_can('edit_user', $user->ID) && current_user_can('edit_posts')){
            $riskClassOption = array(
                'notset' =>  __( 'No Risk class', 'upg' ),
                \Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT => __( 'Default', 'upg' ),
                \Upg\Library\Risk\RiskClass::RISK_CLASS_TRUSTED => __( 'Trusted', 'upg' ),
                \Upg\Library\Risk\RiskClass::RISK_CLASS_HIGH => __( 'High', 'upg' ),
            );

            $optionSet = self::customerHasRiskClass($user->ID);
            if($optionSet) {
                $userRiskClass =  get_the_author_meta('upg_riskclass', $user->ID);
            }else {
                $userRiskClass =  'notset';
            }
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="upg_riskclass">Customer Risk Class</label></th>
                    <td>
                        <select name="upg_riskclass">
                            <?php foreach($riskClassOption as $value=>$label): ?>
                                <option value="<?php echo $value; ?>" <?php if($userRiskClass == $value && $optionSet): ?>selected="selected"<?php endif; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php
        }
    }

    public static function customerHasRiskClass($user_id)
    {
        global $wpdb;

        $value = $wpdb->get_var($wpdb->prepare("SELECT COUNT(meta_key) FROM {$wpdb->prefix}usermeta WHERE user_id = %d AND meta_key = 'upg_riskclass'", $user_id));

        return ($value > 0?true:false);
    }

    public function saveCustomerRiskClass($user_id)
    {
        if ( !current_user_can( 'edit_user', $user_id ) && !current_user_can('edit_posts')) {
            return false;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $riskClass = $_POST['upg_riskclass'];
        if( is_numeric($riskClass) ) {
            update_user_meta($user_id, 'upg_riskclass', esc_attr($riskClass));
        } else {
            delete_user_meta( $user_id, 'upg_riskclass' );
        }

    }

    public function addProductRiskClassField()
    {
        $riskClassOption = array(
            'na' =>  __( 'No Risk class', 'upg' ),
            \Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT => __( 'Default', 'upg' ),
            \Upg\Library\Risk\RiskClass::RISK_CLASS_TRUSTED => __( 'Trusted', 'upg' ),
            \Upg\Library\Risk\RiskClass::RISK_CLASS_HIGH => __( 'High', 'upg' ),
        );
        woocommerce_wp_select(array('id'=>'upg_riskclass', 'label' => __( 'Risk class', 'upg' ), 'options' => $riskClassOption));
    }

    public function saveProductFields($post_id)
    {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $riskClass = array_key_exists('upg_riskclass', $_POST)?$_POST['upg_riskclass']:false;
        if($riskClass !== false) {
            if (is_numeric($riskClass)) {
                update_post_meta($post_id, 'upg_riskclass', esc_attr($riskClass));
            } else {
                delete_post_meta($post_id, 'upg_riskclass');
            }
        }
    }
}