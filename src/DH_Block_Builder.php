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
    protected ?string $view = null;
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
     * Sets the Blade view name used for rendering the block.
     * @param string|null $view_name The Blade view name (e.g., 'blocks.hero').
     * @return self
     */
    public function render_blade(?string $view_name = null): self // Tên này đã quen thuộc
    {
        $this->view = $view_name;
        $this->render_template = null; // Đảm bảo cái còn lại là null
        return $this;
    }

    /**
     * Sets the path to the traditional PHP template file used for rendering the block.
     * @param string|null $path The absolute path to the render file.
     * @return self
     */
    public function render(?string $path = null): self
    {
        $this->render_template = $path;
        $this->view = null; // Đảm bảo cái còn lại là null
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

        $args = [
            'name'            => $this->name,
            'title'           => $this->title ?: ucfirst($this->name),
            'description'     => $this->description,
            'category'        => $this->category,
            'icon'            => $this->icon,
            'supports'        => $this->supports,
            'mode'            => 'edit',
        ];

        // THÊM render_callback NẾU CÓ VIEW BLADE
        if ($this->view) {
             $args['render_callback'] = [$this, 'render_blade_view']; // Trỏ đến phương thức render_blade()
        } else if ($this->render_template) {
             $args['render_template'] = $this->render_template; // Giữ lại cho trường hợp dùng PHP template
        }


        acf_register_block_type($args);
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

    /**
     * Renders the Blade view for the block.
     *
     * @param array $block The block settings and attributes.
     * @param string $content The block content.
     * @param bool $is_preview True during editor preview.
     * @param int $post_id The current post ID.
     * @param WP_Block $wp_block The WP_Block object.
     */
    public function render_blade_view(array $block, string $content = '', bool $is_preview = false, int $post_id = 0, $wp_block = null): void
    {
        // Kiểm tra xem hàm view() của Sage có tồn tại không.
        if (!function_exists('\Roots\view')) {
            // Log lỗi hoặc render fallback nếu Sage chưa sẵn sàng.
            echo 'Blade rendering is not available.';
            return;
        }

        // Lấy dữ liệu trường ACF (fields)
        $fields = get_fields();
        $context = is_array($fields) ? $fields : [];

        // Thêm các biến context tiêu chuẩn của block
        $context['block'] = $block;
        $context['content'] = $content;
        $context['is_preview'] = $is_preview;
        $context['post_id'] = $post_id;
        $context['wp_block'] = $wp_block;

        // Render Blade view (Sử dụng tên view đã lưu trong $this->view)
        echo \Roots\view($this->view, $context);
    }
}
