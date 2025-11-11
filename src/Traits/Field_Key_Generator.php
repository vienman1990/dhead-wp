<?php
// File: src/Traits/Field_Key_Generator.php
namespace DHead_WP\Traits;

trait Field_Key_Generator
{
    /**
     * Recursively generates a unique key for all fields and sub-fields (for ACF/SCF).
     *
     * @param array  $fields Array of fields to process.
     * @param string $prefix Prefix for the key to ensure uniqueness.
     * @return array Array of fields with generated keys.
     */
    private function generate_field_keys(array $fields, string $prefix): array
    {
        $processed_fields = [];

        foreach ($fields as $index => $field) {
            // Generate the main key for the current field if none exists
            if (empty($field['key'])) {
                $key_suffix = !empty($field['name']) ? sanitize_key($field['name']) : $index;
                $field['key'] = $prefix . '_' . $key_suffix;
            }

            // Recurse for sub-fields (Repeater, Group)
            if (!empty($field['sub_fields'])) {
                $field['sub_fields'] = $this->generate_field_keys($field['sub_fields'], $field['key']);
            }

            // Recurse for layouts (Flexible Content)
            if (!empty($field['layouts'])) {
                foreach ($field['layouts'] as $layout_index => &$layout) {
                    // Generate key for the layout
                    if (empty($layout['key'])) {
                        $layout_key_suffix = !empty($layout['name']) ? sanitize_key($layout['name']) : $layout_index;
                        $layout['key'] = $field['key'] . '_' . $layout_key_suffix;
                    }
                    // Recurse for sub_fields within the layout
                    if (!empty($layout['sub_fields'])) {
                        $layout['sub_fields'] = $this->generate_field_keys($layout['sub_fields'], $layout['key']);
                    }
                }
            }
            $processed_fields[] = $field;
        }

        return $processed_fields;
    }
}
