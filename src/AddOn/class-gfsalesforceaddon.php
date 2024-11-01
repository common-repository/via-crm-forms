<?php
namespace ViaGF;

use GuzzleHttp\Exception\ClientException;

use ViaGF\Actions\ActionNames;
use ViaGF\Filters\FilterNames;
use ViaGF\Settings;

\GFForms::include_feed_addon_framework();

class GFSalesforceAddOn extends \GFFeedAddOn {
    use GravityFormHandler;

    protected $_version = VIAGF_VERSION;
    protected $_min_gravityforms_version = '2.3';
    protected $_slug = 'via-gravityforms-salesforce';
    protected $_path = 'via-gravityforms-salesforce/via-gravityforms-salesforce.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Salesforce Add-On';
    protected $_short_title = 'Salesforce';
    protected $_supports_feed_ordering = true;

    private $_salesforce_objects = null;
    private static $_instance = null;

    public static function get_instance() {
        if (self::$_instance == null) {
            self::$_instance = new GFSalesforceAddOn();
        }

        return self::$_instance;
    }

    /**
     * Get a new instance of the Salesforce API.
     * @return  \ViaGF\Salesforce or null if login fails
     */
    public static function get_api() {
        $inst = gf_salesforce_addon();

        try {
            return new \ViaGF\Salesforce($inst->get_plugin_settings());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return null;
        }
    }

    public function styles() {
        $path = plugin_dir_url('via-gravityforms-salesforce.php') . '/via-gravityforms-salesforce/build/via-gravityforms.css';

        $styles = [
            [
                'handle'  => 'viagf_admin_css',
                'src'     => $path,
                'version' => $this->_version,
                'enqueue' => [
                    ['admin_page' => ['form_settings', 'plugin_settings', 'gf_settings', 'gf_edit_forms']]
                ]
            ]
        ];

        return array_merge(parent::styles(), $styles);
    }

    /**
     * Get a list of available Salesforce objects
     */
    public function available_objects() {
        if (!empty($this->_salesforce_objects)) {
            return $this->_salesforce_objects;
        }

        $api = self::get_api();

        if (!isset($api)) {
            return [];
        }

        list($code, $data) = $api->get('/services/data/v37.0/sobjects');

        $objs = [];
        foreach ($data['sobjects'] as $obj) {
            $objs[$obj['name']] = $obj['label'];
        }
        asort($objs);

        $this->_salesforce_objects = $objs;

        return $objs;
    }

    public function plugin_settings_fields() {
        $this->checkLicenseKey();
        $s = new Settings();
        $settings = [];
        $inst = gf_salesforce_addon();
        $checkmark = file_get_contents(__DIR__ . '/../../assets/img/checkmark.svg');
        $licenseDesc = '';

        if ($s->getOption('valid')) {
            $licenseDesc = <<<EOT
<div class="settings-box">
    <h3 class="success settings-title">$checkmark Valid License</h3>
</div>
EOT;
        } elseif ($s->getOption('msg')) {
            $licenseMsg = $s->getOption('msg');
            $licenseDesc = <<<EOT
<div class="settings-box">
    <h3 class="success settings-title"><i class="fa fa-exclamation-triangle gf_invalid"></i>Invalid License ($licenseMsg)</h3>
    <p>The plugin will continue to operate in free mode with limited functionality.</p>
</div>
EOT;
        }

        try {
            $api = new \ViaGF\Salesforce($inst->get_plugin_settings());

            if ($api->isLoggedIn()) {
                $usage = $api->usage();

                $remaining = intval($usage['Remaining']);
                $remainingFmt = number_format($remaining);

                $max = intval($usage['Max']);
                $maxFmt = number_format($max);

                if (($remaining / $max * 100) > 10) {
                    $requestClass = 'success';
                    $requestIcon = $checkmark;
                } else {
                    $requestClass = 'warning';
                    $requestIcon = '<i class="fa fa-exclamation-triangle gf_invalid"></i>';
                }


                $description = <<<EOT
<div class="settings-box">
    <h3 class="success settings-title">$checkmark Connected to Salesforce</h3>
</div>

<div class="settings-box">
    <h3 class="$requestClass settings-title api-requests">$requestIcon Daily API Requests Remaining:</h3>
    <h3 class="$requestClass settings-title api-requests">
        <span class="num">$remainingFmt</span> / <span class="num">$maxFmt</span>
    </h3>
</div>
EOT;
            } else {
                $description = <<<EOT
<div class="settings-box">
    <h3 class="warning settings-title"><i class="fa fa-exclamation-triangle gf_invalid"></i> Please enter your Salesforce Connected App credentials</h3>
</div>
EOT;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $description = '<span class="notice-error notice"><strong>Invalid Salesforce login credentials</strong></span>';
        }

        $settings[] = [
            'description' => $licenseDesc . $description,
            'fields' => [],
        ];

        $settings[] = [
            'title' => esc_html__('Plugin License', 'viagf'),
            'fields' => [
                [
                    'name' => 'license_key',
                    'label' => esc_html__('License Key', 'viagf'),
                    'type' => 'text',
                    'required' => true,
                    'class' => 'medium'
                ]
            ]
        ];

        $settings[] = [
            'title' => esc_html__('Salesforce Settings', 'viagf'),
            'fields' => [
                [
                    'name' => 'salesforce_user',
                    'label' => esc_html__('Salesforce User', 'viagf'),
                    'type' => 'text',
                    'required' => true,
                    'class' => 'medium'
                ],
                [
                    'name' => 'salesforce_pass',
                    'label' => esc_html__('Salesforce Password', 'viagf'),
                    'type' => 'text',
                    'input_type' => 'password',
                    'required' => true,
                    'class' => 'medium'
                ],
                [
                    'name' => 'salesforce_key',
                    'label' => esc_html__('Consumer Key', 'viagf'),
                    'type' => 'text',
                    'required' => true,
                    'class' => 'medium'
                ],
                [
                    'name' => 'salesforce_secret',
                    'label' => esc_html__('Consumer Secret', 'viagf'),
                    'type' => 'text',
                    'input_type' => 'password',
                    'required' => true,
                    'class' => 'medium'
                ],
                [
                    'name' => 'salesforce_token',
                    'label' => esc_html__('Security Token', 'viagf'),
                    'type' => 'text',
                    'class' => 'medium'
                ],
            ]
        ];

        $settings[] = [
            'title' => esc_html__('Documentation', 'viagf'),
            'description' => 'If you need help with the settings, please review the documentation on <a href="https://plugins.viastudio.com/plugin/via-lead-integration" target="_blank" rel="noopener noreferrer">our website</a>.',
            'fields' => [],
        ];

        return $settings;
    }

    /**
     * This is the HTML output for our custom field.
     * This includes a JS object containing data for the current form as well as the entry point for the React app.
     */
    public function settings_viagf_settings_app($field) {
        $form = $this->get_current_form();
        $exclude_field_types = rgempty('exclude_field_types', $field) ? null : $field['exclude_field_types'];
        $field_type = rgempty('field_type', $field) ? null : $field['field_type'];
        $options = $this->get_field_map_choices($form['id'], $field_type, $exclude_field_types);
        $data = [
            'id' => $form['id'],
            'fields' => $options
        ];
?>
        <script>
            window.viagf = window.viagf || {};

            window.viagf.form = JSON.parse('<?php echo json_encode($data); ?>');
        </script>
        <div id="viagf_settings_app"></div>
<?php
    }

    public function settings_viagf_update_dupes($field) {
?>
        <div id="viagf_update_dupes_app"></div>
<?php
    }

    public function feed_settings_fields() {
        $fields = [
            [
                'title' => esc_html__('Feed Settings', 'viagf'),
                'fields' => [
                    [
                        'name' => 'feedName',
                        'label' => esc_html__('Step 1: Feed name', 'viagf'),
                        'type' => 'text',
                    ],
                    [
                        'name' => 'salesforceObject',
                        'label' => esc_html__('Step 2: Select Salesforce Object to use'),
                        'tooltip' => '<h6>' . esc_html__('Select Salesforce Object', 'viagf') . '</h6>' . esc_html__('Select which Salesforce object the form will save to.', 'viagf'),
                        'type' => 'select',
                        'choices' => $this->salesforce_object_map()
                    ],
                ]
            ],
            [
                'title' => esc_html__('Field Settings', 'viagf'),
                'fields' => [
                    [
                        'name' => 'salesforceSettingsApp',
                        'label' => esc_html__('Map Gravityform Fields to Salesforce', 'viagf'),
                        'type' => 'viagf_settings_app',
                        'exclude_field_types' => 'creditcard'
                    ],
                ]
            ],
            [
                'title' => esc_html__('Advanced Settings', 'viagf'),
                'fields' => [
                    [
                        'type' => 'feed_condition',
                        'name' => 'viagf_condition',
                        'label'  => esc_html__('Conditional Logic', 'viagf'),
                    ],
                    [
                        'name' => 'viagf_update_dupes',
                        'label' => esc_html__('Duplicates', 'viagf'),
                        'tooltip' => '<h6>' . esc_html__('Update Duplicates', 'viagf') . '</h6>' . esc_html__('Select if the plugin will update existing Salesforce objects with the information from the form. You must select an Salesforce Field to use to find the existing Salesforce object.', 'viagf'),
                        'type' => 'viagf_update_dupes',
                        'exclude_field_types' => 'creditcard'
                    ],

                ]
            ]
        ];

        return $fields;
    }

    /**
     * Process the feed and send form data to Salesforce
     *
     * @param array $feed The feed object to be processed.
     * @param array $entry The entry object currently being processed.
     * @param array $form The form object currently being processed.
     *
     * @return bool|void
     */
    public function process_feed($feed, $entry, $form) {
        $this->checkLicenseKey();
        $this->processFormEntry($entry, $form);

        $settings = new Settings();
        if ($settings->getOption('invalid')) {
            do_action(ActionNames::CREATE_SOBJECT_ERROR, [
                "data" => ['error' => 'Invalid license'],
                "fields" => null,
                "form" => null,
                "entry" => null,
                "feed" => null
            ]);

            return;
        }

        $feed_meta = viagf_get_form_meta($form['id'], "{$feed['id']}_feed");
        $feed_meta = json_decode($feed_meta, true);

        if (empty($feed_meta)) {
            return;
        }

        $api = self::get_api();
        $fields = [];

        $entry = apply_filters(FilterNames::BEFORE_CREATE_SOBJECT, $entry, $form, $feed);

        $dupes_meta = json_decode(viagf_get_form_meta($form['id'], "{$feed['id']}_update_dupes"), true);
        $objSettings = isset($dupes_meta[$feed['meta']['salesforceObject']]) ? $dupes_meta[$feed['meta']['salesforceObject']] : null;
        //Get the identity field from the settings
        $identityField = $objSettings['identity_field'];

        //Once we have the identity field, we need to
        //find the GravityForm field which maps to the identity
        //field so we can retrieve its value from the form
        $identityValue = null;
        foreach ($feed_meta[$feed['meta']['salesforceObject']]['field_map'] as $map) {
            if ($map['sf'] == $identityField) {
                $identityValue = $this->fieldValues[$this->fieldIndex[$map['gf']]];
            }
        }

        foreach ($feed_meta[$feed['meta']['salesforceObject']]['field_map'] as $idx => $field) {
            if (!$settings->getOption('valid') && $idx >= 1) {
                continue;
            }

            $value = $this->get_field_value($form, $entry, $field['gf']);
            if (empty($value)) {
                continue;
            }

            $fields[$field['sf']] = $value;
        }

        if (empty($fields)) {
            return;
        }

        try {
            list ($code, $ret) = $api->create("/services/data/v37.0/sobjects/{$feed['meta']['salesforceObject']}", $fields);
            $data = json_decode($ret, true);
        } catch (ClientException $e) {
            //In the case of a REST API exception, the error response will be passed to the `CREATE_SOBJECT_ERROR` action
            if ($e->hasResponse()) {
                $resp = $e->getResponse();
                $ret = (string) $resp->getBody();
                $data = json_decode($ret, true);
            }
        } finally {
            if ($data === false || !isset($data['success']) || !boolval($data['success'])) {
                $doError = true;

                if (is_array($data) && strpos($data[0]['errorCode'], 'DUPLICATE') !== false) {
                    //If we have a duplicate error from Salesforce
                    //check to see if this feed has duplicate handling enabled
                    //and check that the identity field is set on the form entry
                    if (!empty($objSettings) && $objSettings['enabled'] && !empty($identityValue)) {
                        $doError = false;

                        //If duplicate handling is enabled, attempt to update the object
                        $soql = "SELECT Id FROM Contact WHERE {$identityField} = '{$identityValue}'";

                        list ($code, $data) = $api->query($soql);

                        if (isset($data['totalSize']) && $data['totalSize'] == 1) {
                            $id = $data['records'][0]['Id'];
                            $url = "/services/data/v20.0/sobjects/{$feed['meta']['salesforceObject']}/$id";

                            list($code, $ret) = $api->update($url, $fields);
                            $data = json_decode($ret, true);

                            do_action(ActionNames::AFTER_UPDATE_SOBJECT, [
                                "data" => $data,
                                "fields" => $fields,
                                "form" => $form,
                                "entry" => $entry,
                                "feed" => $feed
                            ]);
                        } else {
                            do_action(ActionNames::UPDATE_SOBJECT_ERROR, [
                                "data" => $data,
                                "fields" => $fields,
                                "form" => $form,
                                "entry" => $entry,
                                "feed" => $feed
                            ]);
                        }
                    }
                }

                if ($doError) {
                    do_action(ActionNames::CREATE_SOBJECT_ERROR, [
                        "data" => $data,
                        "fields" => $fields,
                        "form" => $form,
                        "entry" => $entry,
                        "feed" => $feed
                    ]);
                }
            } else {
                do_action(ActionNames::AFTER_CREATE_SOBJECT, [
                    "id" => $data['id'],
                    "form" => $form,
                    "entry" => $entry,
                    "feed" => $feed
                ]);
            }
        }

        die('<hr />debug: done');
    }

    public function salesforce_object_map() {
        $options = [
            ['value' => '', 'label' => '']
        ];
        $objs = $this->available_objects();
        foreach ($objs as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $options;
    }

    /**
     * Configures which columns should be displayed on the feed list page.
     *
     * @return array
     */
    public function feed_list_columns() {
        return array(
            'feedName'  => esc_html__('Name', 'viagf'),
            'salesforceObject' => esc_html__('Salesforce Object', 'viagf')
        );
    }

    public function get_column_value_salesforceObject($feed) {
        if (empty($feed['meta']['salesforceObject'])) {
            return;
        }

        $objects = $this->available_objects();
        return $objects[$feed['meta']['salesforceObject']];
    }

    /**
     * Prevent feeds being listed or created if an api key isn't valid.
     *
     * @return bool
     */
    public function can_create_feed() {
        $api = self::get_api();
        return $api->isLoggedIn();
    }

    /**
     *  Override to pre-save new feeds.  We need the feed ID in order to save field mapping.
     * @return int
     */
    public function maybe_save_feed_settings($feed_id, $form_id) {

        if ($feed_id === "0") {
            $feed_id = $this->save_feed_settings($feed_id, $form_id, ['feedName' => $this->get_default_feed_name()]);
        }
        return parent::maybe_save_feed_settings($feed_id, $form_id);
    }

    public function checkLicenseKey() {
        $settings = new Settings();

        $options = get_option('gravityformsaddon_via-gravityforms-salesforce_settings');
        $licenseMessage = '';

        if (!isset($options['license_key']) || !\Via\PhpLicense\License::verify($options['license_key'], VIAGF_VERSION, $licenseMessage)) {
            //Show license nag message
            $settings->removeOption('valid');
            $settings->setOption('invalid', true);
            $settings->setOption('msg', $licenseMessage);
        } else {
            $settings->removeOption('invalid');
            $settings->removeOption('msg');
            $settings->setOption('valid', true);
        }
    }
}
