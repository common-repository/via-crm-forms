<?php
namespace ViaGF;

class Settings {
    const OPTION_NAME = 'viagf_name_field';

    public function __construct() {
        if (empty($this->keyFieldExists())) {
            $this->setFieldName($this->generateKeyField());
        }
    }

    public function getOption($option) {
        $options = get_option($this->getFieldName());

        return $options[$option];
    }

    public function setOption($option, $value) {
        $options = get_option($this->getFieldName());

        $options[$option] = $value;

        update_option($this->getFieldName(), $options);
    }

    public function removeOption($option) {
        $options = get_option($this->getFieldName());

        unset($options[$option]);

        update_option($this->getFieldName(), $options);
    }

    private function keyFieldExists() {
        return !empty($this->getFieldName());
    }

    public function getFieldName() {
        return get_option(Settings::OPTION_NAME);
    }

    private function setFieldName($name) {
        update_option(Settings::OPTION_NAME, $name);
    }

    private function generateKeyField() {
        return uniqid('', true);
    }
}
