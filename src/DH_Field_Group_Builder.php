<?php
// File: src/DH_Field_Group_Builder.php
namespace DHead_WP;

use DHead_WP\Traits\Field_Key_Generator;

class DH_Field_Group_Builder
{
    use Field_Key_Generator;

    protected string $group_title;
    protected string $group_key_slug;
    protected array $fields = [];
    protected array $location_config = [];
    protected array $settings = []; // Dùng để chứa các cài đặt khác của ACF/SCF (style, position, label_placement, v.v.)

    /**
     * Static constructor (Factory Method) to start the Field Group definition chain.
     * * @param string $group_title The title for the Field Group.
     * @param string $group_key_slug The unique slug used to generate the group key (e.g., 'global_settings').
     * @return self
     */
    public static function make(string $group_title, string $group_key_slug): self
    {
        $instance = new self();
        $instance->group_title = $group_title;
        // Sử dụng slug để tạo key duy nhất
        $instance->group_key_slug = sanitize_key($group_key_slug);

        // Không cần Manager, vì đây là một Builder độc lập, người dùng sẽ tự gọi ->register()

        return $instance;
    }

    // --- Fluent Interface methods ---

    /**
     * Sets the array of field configurations.
     * @param array $fields Array of field configurations (ACF/SCF format).
     * @return self
     */
    public function fields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Sets the location array where the field group should appear.
     * This allows for free configuration of any ACF/SCF location rule.
     *
     * @param array $location_config The full location array (as required by acf_add_local_field_group).
     * @return self
     */
    public function location(array $location_config): self
    {
        $this->location_config = $location_config;
        return $this;
    }

    /**
     * Sets other general settings for the Field Group (style, position, hide_on_screen, etc.).
     * @param array $settings
     * @return self
     */
    public function settings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Executes the registration of the local field group.
     */
    public function register(): void
    {
        add_action('acf/init', function() {
            $this->register_field_group();
        });
    }

    /**
     * Registers the local field group using the collected configuration.
     */
    protected function register_field_group(): void
    {
        if (empty($this->fields) || !function_exists('acf_add_local_field_group')) {
            return;
        }

        // Tạo key duy nhất cho nhóm
        $group_key = 'group_groupbuilder_' . $this->group_key_slug;

        // Tạo key đệ quy cho từng trường
        $field_list = $this->generate_field_keys($this->fields, 'field_groupbuilder_' . $this->group_key_slug);

        // Xây dựng các đối số (args)
        $args = array_merge($this->settings, [
            'key'      => $group_key,
            'title'    => $this->group_title,
            'fields'   => $field_list,
            'location' => $this->location_config, // Sử dụng location tự do
        ]);

        acf_add_local_field_group($args);
    }
}
