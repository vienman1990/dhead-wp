<?php
// File: DH_Options_Page_Builder.php
namespace DHead_WP;

use DHead_WP\Traits\Field_Key_Generator;

class DH_Options_Page_Builder
{
    use Field_Key_Generator;

    protected string $page_title;
    protected string $menu_title = '';
    protected string $menu_slug;
    protected string $capability = 'edit_posts';
    protected ?string $parent_slug = null;
    protected string $icon_url = '';
    protected ?int $position = null;
    protected array $fields = [];

    public static function make(string $page_title, string $menu_slug = ''): self
    {
        $instance = new self();
        $instance->page_title = $page_title;
        // Automatically generate slug from title if not provided
        $instance->menu_slug = $menu_slug ?: sanitize_title($page_title);

        DH_Options_Page_Manager::add($instance);

        return $instance;
    }

    public function menu_title(string $title): self
    {
        $this->menu_title = $title;
        return $this;
    }

    public function parent(string $parent_slug): self
    {
        $this->parent_slug = $parent_slug;
        return $this;
    }

    public function capability(string $capability): self
    {
        $this->capability = $capability;
        return $this;
    }

    public function icon(string $icon_url): self
    {
        $this->icon_url = $icon_url;
        return $this;
    }

    public function position(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function fields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Executes the registration logic. Called by the Manager class.
     */
    public function init_registration(): void
    {
        $this->register_page();
        $this->register_fields();
    }

    /**
     * Registers the Options Page using ACF/SCF functions.
     */
    protected function register_page(): void
    {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        $args = [
            'page_title' => $this->page_title,
            'menu_title' => $this->menu_title ?: $this->page_title,
            'menu_slug'  => $this->menu_slug,
            'capability' => $this->capability,
            'icon_url'   => $this->icon_url,
            'position'   => $this->position,
        ];

        // If it's a sub-page
        if ($this->parent_slug) {
            $args['parent_slug'] = $this->parent_slug;
            acf_add_options_sub_page($args);
        } else {
            acf_add_options_page($args);
        }
    }

    /**
     * Registers the fields group and links it to the Options Page.
     */
    protected function register_fields(): void
    {
        if (empty($this->fields) || !function_exists('acf_add_local_field_group')) {
            return;
        }

        $group_key = 'group_options_' . sanitize_key($this->menu_slug);

        // Uses the Field_Key_Generator Trait
        $field_list = $this->generate_field_keys($this->fields, 'field_options_' . $this->menu_slug);

        acf_add_local_field_group([
            'key' => $group_key,
            'title' => $this->page_title,
            'fields' => $field_list,
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => $this->menu_slug, // Crucial: points the field group to the options page
                    ],
                ],
            ],
        ]);
    }
}
