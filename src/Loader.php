<?php
namespace ViaGF;

use ViaGF\AdminAjaxActions\FeedAction;
use ViaGF\AdminAjaxActions\SalesforceAction;
use ViaGF\Settings;

class Loader {
    static public $pluginBaseDir;
    static public $pluginBaseUrl;
    static public $version = "1.0";

    public function __construct($pluginBaseDir, $pluginBaseUrl) {
        static::$pluginBaseDir = $pluginBaseDir;
        static::$pluginBaseUrl = $pluginBaseUrl;
    }

    public function init() {
        $this->initActions();
        $this->initAdminAjaxActions();

        add_action('admin_head', function () {
            $settings = new Settings();
            $status = $settings->getOption('valid') ? 1 : 0;
?>
            <script>
            var <?php echo Settings::OPTION_NAME; ?> = '<?php echo $settings->getFieldName(); ?>';
            var viagf_settings = { '<?php echo $settings->getFieldName(); ?>': <?php echo $status; ?> };
            </script>
<?php
        });

        add_action('admin_notices', function () {
            $current_screen = get_current_screen();
            if ($current_screen->id === 'forms_page_gf_settings') {
                return;
            }

            $settings = new Settings();
            if (!$settings->getOption('valid')) {
?>
                <div class="notice notice-error is-dismissible">
                    <p>Please enter your VIA GravityForms Add-On license key to enable all functionality.
                    <a href="<?php echo get_admin_url(); ?>admin.php?page=gf_settings&subview=via-gravityforms-salesforce">(Settings)</a></p>
                </div>
<?php
            }
        });
    }

    private function initActions() {
        add_action('gform_after_delete_form', function ($form_id) {
            viagf_clear_form_meta($form_id);
        });
    }

    private function initAdminAjaxActions() {
        add_action('admin_head', function () {
            //Store our nonce in the admin header (so we can make admin ajax calls)
            $nonce = wp_create_nonce('viagf');

            echo <<<EOT
<script>
    var viagf_nonce = '$nonce';
</script>
EOT;
        });

        FeedAction::init();
        SalesforceAction::init();
    }

    public static function getErrorNotice($msg) {
        return <<<EOT
<div class="error notice">
    <p>$msg</p>
</div>
EOT;
    }

    //Initialize the database (when the plugin is activated)
    //If we make database changes, we need to increment the value of VIAGF_VERSION
    private static function initDatabase() {
        $wpdb = $GLOBALS['wpdb'];

        $installedVer = get_option(VIAGF_VERSION_KEY);

        if ($installedVer != VIAGF_VERSION) {
            $tableName = "{$wpdb->prefix}gf_viagf_meta";

            $sql = <<<EOT
CREATE TABLE $tableName (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    form_id mediumint(9) NOT NULL,
    meta_key varchar(255) NOT NULL,
    meta_value text,
PRIMARY KEY (id),
UNIQUE (form_id, meta_key)
);
EOT;

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            add_option(VIAGF_VERSION_KEY, VIAGF_VERSION);
        }
    }

    public function activatePlugin() {
        self::initDatabase();

        do_action('viagf_activate_plugin');
    }

    public function deactivatePlugin() {
        //TODO - Do we want to cleanup the database here?
        //Or maybe make it an option?

        do_action('viagf_deactivate_plugin');
    }
}
