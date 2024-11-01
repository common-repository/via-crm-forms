<?php
namespace ViaGF;

use GuzzleHttp\Exception\RequestException;

trait GravityFormHandler {
    protected $entry;
    protected $form;
    protected $fieldIndex;
    protected $fieldList;
    protected $fieldValues;

    public static function cleanLabel($label) {
        return str_replace('  ', ' ', trim(preg_replace("/[^aA-zZ0-9 ]/", ' ', $label)));
    }

    protected function processFormEntry($entry, $form) {
        $this->entry = $entry;
        $this->form = $form;

        $this->buildFieldIndex();
        $this->buildFieldList();
        $this->buildFieldValues();
    }

    /**
     * GravityForms builds a $_POST of numeric ids for each form field.
     * This creates an array using the field label for easier reference.
     */
    protected function buildFieldList() {
        $fieldList = [];

        foreach ($this->form['fields'] as $field) {
            if (!isset($field['inputs'])) {
                $label = GravityFormHandler::cleanLabel($field->label);

                $fieldList[$label] = $field->id;
            } else {
                foreach ($field['inputs'] as $input) {
                    $label = GravityFormHandler::cleanLabel($input['label']);

                    $fieldList[$label] = $input['id'];
                }
            }
        }

        $this->fieldList = $fieldList;
    }

    protected function buildFieldIndex() {
        $fieldIndex = [];

        foreach ($this->form['fields'] as $field) {
            if (!isset($field['inputs'])) {
                $label = GravityFormHandler::cleanLabel($field->label);

                $fieldIndex[$field->id] = $label;
            } else {
                foreach ($field['inputs'] as $input) {
                    $label = GravityFormHandler::cleanLabel($input['label']);

                    $fieldIndex[$input['id']] = $label;
                }
            }
        }

        $this->fieldIndex = $fieldIndex;
    }

    /**
     * Build a map of field label/value pairs. This is easier to use than numeric ids
     */
    protected function buildFieldValues() {
        $fieldValues = [];

        foreach ($this->fieldList as $label => $idx) {
            $fieldValues[$label] = $this->entry[$idx];
        }

        $this->fieldValues = $fieldValues;
    }
}
