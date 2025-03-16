<?php
namespace WPDevAssist\Model;

defined( 'ABSPATH' ) || exit;

class ActionLink {
	protected string $title       = '';
	protected string $url         = '';
	protected string $confirm     = '';
	protected bool $target_blank  = false;
	protected string $class_names = '';
	protected bool $is_disabled   = false;

	public function __construct(
		string $title,
		string $url,
		string $confirm = '',
		bool $target_blank = false,
		string $class_names = '',
		bool $is_disabled = false
	) {
		$this->title        = $title;
		$this->url          = $url;
		$this->confirm      = $confirm;
		$this->target_blank = $target_blank;
		$this->class_names  = $class_names;
		$this->is_disabled  = $is_disabled;
	}

	public function render(): void {
		?>
		<a
			<?php echo ! $this->is_disabled ? 'href="' . esc_url( $this->url ) . '"' : ''; ?>
			<?php echo $this->confirm && ! $this->is_disabled ? 'onclick="return confirm(\'' . esc_js( $this->confirm ) . '\')"' : ''; ?>
			<?php echo $this->target_blank ? 'target="_blank"' : ''; ?>
			<?php echo $this->class_names ? 'class="' . esc_attr( $this->class_names ) . '"' : ''; ?>
		>
			<?php echo wp_kses( $this->title, array( 'code' => array() ) ); ?>
		</a>
		<?php
	}
}
