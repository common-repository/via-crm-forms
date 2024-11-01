<?php
namespace ViaGF\AdminAjaxActions;

use ViaGF\GFSalesforceAddOn;

class SalesforceAction {
    public static function init() {
        //Load the list of available fields for a Salesforce object
        add_action('wp_ajax_via_load_sf_field_map', function () {
            $ret = ['status' => 'err', 'msg' => ''];

            try {
                //TODO - may want to cache these somewhere
                $api = GFSalesforceAddOn::get_api();
                list($code, $sobject) = $api->describe($_POST['sobject']);

                $fields = [];
                foreach ($sobject['fields'] as $field) {
                    if (!boolval($field['createable']) && !boolval($field['updateable'])) {
                        continue;
                    }

                    $fields[] = [
                        'name' => $field['name'],
                        'label' => $field['label']
                    ]; 
                }

                usort($fields, function ($a, $b) {
                    return strcmp($a['label'], $b['label']);
                });

                $ret['fields'] = $fields;
                $ret['status'] = 'ok';
            } catch (\Exception $e) {
                $ret['msg'] = "error: " . $e->getMessage();
            } finally {
                echo json_encode($ret);
                wp_die();
            }
        });
    }
}
