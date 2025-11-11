<?php
// File: src/DH_Block_Builder.php
namespace DHead_WP;

// Assumes Trait is implemented
use DHead_WP\Traits\Field_Key_Generator;

class DH_Block_Builder
{
    use Field_Key_Generator; // Apply Trait

    protected string $name;
    protected string $title = '';
    protected string $description = '';
    protected string $icon = 'block-default';
    protected string $category = 'formatting';
    protected ?string $render_template = null;
    protected array $supports = [
        'align' => true,
        'anchor' => true,
        'jsx' => false,
    ];
    protected array $fields = [];

    /**
     * Static constructor to start the Block definition chain.
     * @param string $name The unique name/slug for the block.
     * @return self
     */
    public static function make(string $name): self
    {
        $instance = new self();
        $instance->name = $name;

        // Automatically add the instance to the Manager upon creation
        DH_Block_Manager::add($instance);

        return $instance;
    }

    // --- Fluent Interface methods ---

    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function description(string $desc): self
    {
        $this->description = $desc;
        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function category(string $cat): self
    {
        $this->category = $cat;
        return $this;
    }

    public function supports(array $supports): self
    {
        $this->supports = array_merge($this->supports, $supports);
        return $this;
    }

    /**
     * Sets the path to the template file used for rendering the block.
     * @param string|null $path The absolute path to the render file.
     * @return self
     */
    public function render(?string $path = null): self
    {
        // If a path is provided, use it.
        if ($path) {
            $this->render_template = $path;
        } else {
            // Note: When using Block_Auto_Registrar, the template path should be set
            // in the block.php file explicitly, or handled by the Registrar.
            // Keeping this simple for the Builder pattern.
            $this->render_template = null;
        }
        return $this;
    }

    public function fields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Executes the block and field registration logic. Called by the Manager.
     */
    public function init(): void
    {
        $this->register_block();
        $this->register_fields();
    }

    /**
     * Registers the Block Type using ACF/SCF functions.
     */
    protected function register_block(): void
    {
        if (!function_exists('acf_register_block_type')) {
            return;
        }

        acf_register_block_type([
            'name'            => $this->name,
            'title'           => $this->title ?: ucfirst($this->name),
            'description'     => $this->description,
            'render_template' => $this->render_template,
            'category'        => $this->category,
            'icon'            => $this->icon,
            'supports'        => $this->supports,
            'mode'            => 'edit',
        ]);
    }

    /**
     * Registers the field group and links it to the block.
     */
    protected function register_fields(): void
    {
        if (empty($this->fields) || !function_exists('acf_add_local_field_group')) {
            return;
        }

        $group_key = 'group_' . sanitize_key($this->name);

        // Uses the Field_Key_Generator Trait
        $field_list = $this->generate_field_keys($this->fields, 'field_' . $this->name);

        acf_add_local_field_group([
            'key' => $group_key,
            'title' => $this->title ?: ucfirst($this->name),
            'fields' => $field_list,
            'location' => [
                [
                    [
                        'param' => 'block',
                        'operator' => '==',
                        'value' => 'acf/' . $this->name,
                    ],
                ],
            ],
        ]);
    }
}
