<?php
namespace WPDevAssist\Assistant;

defined( 'ABSPATH' ) || exit;

class Control {
	protected string $title      = '';
	protected string $url        = '';
	protected string $confirm    = '';
	protected bool $target_blank = false;

	public function __construct( string $title, string $url, string $confirm = '', bool $target_blank = false ) {
		$this->title        = $title;
		$this->url          = $url;
		$this->confirm      = $confirm;
		$this->target_blank = $target_blank;
	}

	public function render(): void {
		?>
		<a
			href="<?php echo esc_url( $this->url ); ?>"
			<?php echo $this->confirm ? 'onclick="return confirm(\'' . esc_js( $this->confirm ) . '\')"' : ''; ?>
			<?php echo $this->target_blank ? 'target="_blank"' : ''; ?>
		>
			<?php echo wp_kses( $this->title, array( 'code' => array() ) ); ?>
		</a>
		<?php
	}
}
