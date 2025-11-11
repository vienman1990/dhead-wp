<?php
// File: DH_CPT_Builder.php
namespace DHead_WP;

use DHead_WP\Traits\Field_Key_Generator;

class DH_CPT_Builder
{
    use Field_Key_Generator;

    protected string $slug;
    protected array $post_type_args = [];
    protected array $fields_config = [];
    protected array $taxonomies_config = [];
    protected string $meta_box_title = '';

    /**
     * Static Constructor (or Factory Method) to begin the CPT definition chain.
     * * @param string $slug The unique post type slug.
     * @return self
     */
    public static function make(string $slug): self
    {
        $instance = new self();
        $instance->slug = $slug;

        // Automatically add the instance to the Manager upon creation
        DH_CPT_Manager::add($instance);

        return $instance;
    }

    // --- Fluent Interface for Configuration ---

    /**
     * Sets the standard WordPress arguments for the post type registration.
     * * @param array $args Array of arguments (labels, public, supports, etc.).
     * @return self
     */
    public function args(array $args): self
    {
        $this->post_type_args = $args;
        return $this;
    }

    /**
     * Sets the field configuration and the title for the meta box.
     * * @param array $fields Array of field configurations (ACF/SCF format).
     * @param string $meta_box_title The title for the field group meta box.
     * @return self
     */
    public function fields(array $fields, string $meta_box_title): self
    {
        $this->fields_config = $fields;
        $this->meta_box_title = $meta_box_title;
        return $this;
    }

    /**
     * Sets the taxonomies associated with this post type.
     * * @param array $taxonomies Array of taxonomy slugs and their arguments.
     * @return self
     */
    public function taxonomies(array $taxonomies): self
    {
        $this->taxonomies_config = $taxonomies;
        return $this;
    }

    // --- Getters (used by Manager) ---

    public function get_slug(): string
    {
        return $this->slug;
    }
    public function get_post_type_args(): array
    {
        return $this->post_type_args;
    }
    public function get_taxonomies_config(): array
    {
        return $this->taxonomies_config;
    }
    public function get_fields_config(): array
    {
        return $this->fields_config;
    }
    public function get_meta_box_title(): string
    {
        return $this->meta_box_title;
    }

    // --- Registration Logic ---

    /**
     * Executes the combined registration logic. Called by the CPT Manager.
     */
    public function init_registration(): void
    {
        $this->register_post_type_and_taxonomies();
        $this->register_meta_fields();
        // Keep the rewrite flush hook for when the theme switches
        add_action('after_switch_theme', [$this, 'rewrite_flush']);
    }

    /**
     * Registers the Custom Post Type and its associated Taxonomies.
     */
    protected function register_post_type_and_taxonomies(): void
    {
        // 1. Register CPT
        register_post_type($this->slug, $this->post_type_args);

        // 2. Register Taxonomies
        if (! empty($this->taxonomies_config)) {
            foreach ($this->taxonomies_config as $taxonomy_slug => $args) {
                register_taxonomy($taxonomy_slug, $this->slug, $args);
            }
        }
    }

    /**
     * Registers the custom meta fields using ACF/SCF functions.
     */
    public function register_meta_fields(): void
    {
        $fields = $this->fields_config;
        $slug = $this->slug;

        if (empty($fields) || ! function_exists('acf_add_local_field_group')) {
            return;
        }

        // Automatically generate key for the field group
        $group_key = 'group_' . sanitize_key($slug);

        // Automatically generate keys for fields and sub-fields (using the Trait)
        $processed_fields = $this->generate_field_keys($fields, 'field_' . $slug);

        acf_add_local_field_group([
            'key'      => $group_key,
            'title'    => $this->meta_box_title, // Use the configured property
            'fields'   => $processed_fields,
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => $slug,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Flushes rewrite rules. Called on theme switch to ensure CPTs are registered correctly.
     */
    public function rewrite_flush()
    {
        // Call the combined registration function to ensure both CPT and taxonomy are recognized
        $this->register_post_type_and_taxonomies();
        flush_rewrite_rules();
    }
}
