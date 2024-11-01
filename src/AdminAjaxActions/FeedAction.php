<?php
namespace ViaGF\AdminAjaxActions;

class FeedAction {
    public static function init() {
        //Load the meta data for a Feed
        add_action('wp_ajax_via_get_sf_field_map', function () {
            $ret = ['status' => 'err', 'msg' => ''];

            try {
                $formId = sanitize_number($_GET['form_id']);
                $feedId = sanitize_number($_GET['feed_id']);
                $sobjectName = sanitize_text_field($_GET['sobject']);

                $ret['data'] = [
                    'feed' => viagf_get_form_meta($formId, "{$feedId}_feed"),
                    'update_dupes' => viagf_get_form_meta($formId, "{$feedId}_update_dupes"),
                ];

                $ret['status'] = 'ok';
            } catch (\Exception $e) {
                $ret['msg'] = "error: " . $e->getMessage();
            } finally {
                echo json_encode($ret);
                wp_die();
            }
        });

        //Save the meta data for a Feed
        add_action('wp_ajax_via_save_sf_field_map', function () {
            $ret = ['status' => 'err', 'msg' => ''];

            try {
                $formId = sanitize_number($_POST['meta']['form']['id']);
                $feedId = sanitize_number($_POST['meta']['feed_id']);
                $sobjectName = sanitize_text_field($_POST['meta']['sobject']['name']);
                $feeds = sanitize_text_field($_POST['feeds']['feeds']);
                $metaVal = sanitize_text_field($_POST['meta']['update_dupes']);

                viagf_update_form_meta($formId, "{$feedId}_feed", json_encode($feeds));
                viagf_update_form_meta($formId, "{$feedId}_update_dupes", json_encode($metaVal));

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
