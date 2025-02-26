<?php
namespace WPDevAssist\Setting;

use Exception;
use WP_Error;
use WPDevAssist\ActionQuery;
use WPDevAssist\Asset;
use WPDevAssist\Model\Link;
use WPDevAssist\Notice;
use const WPDevAssist\KEY;

class SupportUser extends Page {
	public const KEY = KEY . '_support_user';

	public const ENABLE_KEY                = KEY . '_enable_support_user';
	public const ENABLE_DEFAULT            = 'yes';
	public const DELETE_AFTER_DAYS_KEY     = KEY . '_delete_support_user_after_days';
	public const DELETE_AFTER_DAYS_DEFAULT = 3;
	public const ID_KEY                    = KEY . '_support_user_id';
	public const ID_DEFAULT                = 0;
	public const LOGIN_KEY                 = KEY . '_support_user_login';
	public const LOGIN_DEFAULT             = '';
	public const PASSWORD_KEY              = KEY . '_support_user_password';
	public const PASSWORD_DEFAULT          = '';
	public const PASSWORD_SHOWN_VALUE      = '************';
	public const CREATED_AT_KEY            = KEY . '_support_user_created_at';
	public const CREATED_AT_DEFAULT        = 0;
	public const EMAIL_KEY                 = KEY . '_support_user_email';
	public const EMAIL_DEFAULT             = '';

	protected const SETTING_KEYS = array(
		self::ENABLE_KEY,
		self::DELETE_AFTER_DAYS_KEY,
	);

	public const CREATE_QUERY_KEY           = KEY . '_create_support_user';
	public const DELETE_QUERY_KEY           = KEY . '_delete_support_user';
	public const RECREATE_QUERY_KEY         = KEY . '_recreate_support_user';
	public const SHARE_EMAIL_QUERY_KEY      = KEY . '_share_support_user_email';
	public const UPDATE_CREATE_AT_QUERY_KEY = KEY . '_update_create_support_user';

	protected const SHARE_PASSWORD_QUERY_KEY = KEY . '_share_support_user_password';
	protected const SHARE_MESSAGE_QUERY_KEY  = KEY . '_share_support_user_message';

	public const ENABLE_HOOK = KEY . '_enable_support_user';
	public const EMAIL_HOOK  = self::KEY . '_email';

	public function __construct() {
		add_action( 'deleted_user', array( $this, 'delete_data_when_user_deleted' ) );

		if ( ! apply_filters( static::ENABLE_HOOK, true ) ) {
			return;
		}

		parent::__construct();
		ActionQuery::add( static::CREATE_QUERY_KEY, array( $this, 'handle_create_user' ) );
		ActionQuery::add( static::DELETE_QUERY_KEY, array( $this, 'handle_delete_user' ) );
		ActionQuery::add( static::RECREATE_QUERY_KEY, array( $this, 'handle_recreate_user' ) );
		ActionQuery::add( static::SHARE_EMAIL_QUERY_KEY, array( $this, 'handle_share_to_email' ), false );
		ActionQuery::add( static::UPDATE_CREATE_AT_QUERY_KEY, array( $this, 'handle_update_create_at' ) );
		add_action( 'update_option_' . static::ENABLE_KEY, array( $this, 'delete_user_if_disabled' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'delete_user_after_days' ) );
	}

	public function add_page(): void {
		$page_title = __( 'Support User', 'development-assistant' );

		add_submenu_page(
			KEY,
			$page_title,
			$page_title,
			'administrator',
			static::KEY,
			array( $this, 'render_page' )
		);
	}

	public function add_sections(): void {
		$this->add_general_section( static::KEY . '_general' );
		$this->add_user_data_section( static::KEY . '_user_data' );
	}

	protected function add_general_section( string $section_key ): void {
		$this->add_section(
			$section_key,
			'',
			function(): void {
				?>
				<div class="da-setting-section__description">
					<?php echo wp_kses( __( 'This section allows you to quickly create a user with administrator role that can be used by the support team to access the website for debugging purposes.', 'development-assistant' ), array( 'b' => array() ) ); ?>
				</div>
				<?php
			}
		);
		$this->add_setting(
			$section_key,
			static::ENABLE_KEY,
			__( 'Enable support user', 'development-assistant' ),
			Control\Checkbox::class,
			static::ENABLE_DEFAULT
		);
		$this->add_setting(
			$section_key,
			static::DELETE_AFTER_DAYS_KEY,
			__( 'Delete user after days', 'development-assistant' ),
			Control\Text::class,
			static::DELETE_AFTER_DAYS_DEFAULT,
			array(
				'type'        => 'number',
				'min'         => 0,
				'step'        => 1,
				'description' => __( 'Set to 0 to disable automatic deletion.', 'development-assistant' ),
			)
		);
	}

	protected function add_user_data_section( string $section_key ): void {
		if ( 'yes' !== get_option( static::ENABLE_KEY, self::ENABLE_DEFAULT ) ) {
			return;
		}

		$this->add_section(
			$section_key,
			__( 'User Credentials', 'development-assistant' ),
			array( $this, 'render_user_data_section' )
		);
	}

	public function render_user_data_section(): void {
		?>
		<div class="da-support-user">
			<?php
			if ( 0 === get_option( static::ID_KEY, self::ID_DEFAULT ) ) {
				$this->render_user_data_section_empty();

				return;
			}

			$password          = $this->get_password_once_and_mask_it();
			$is_password_shown = static::PASSWORD_SHOWN_VALUE === $password;
			?>
			<ul class="da-support-user__credentials" id="da-support-user-credentials">
				<li style="display: none;"><?php echo esc_html( wp_login_url() ); ?></li>
				<li>
					<b><?php echo esc_html__( 'Username', 'development-assistant' ); ?>:</b> <span><?php echo esc_html( get_option( static::LOGIN_KEY, static::LOGIN_DEFAULT ) ); ?></span>
				</li>
				<li>
					<b><?php echo esc_html__( 'Password', 'development-assistant' ); ?>:</b> <span id="da-support-user-password"><?php echo esc_html( $password ); ?></span>
				</li>
			</ul>
			<?php
			echo wp_kses_post( static::get_details( 'da-support-user__details' ) );

			if ( ! $is_password_shown ) {
				?>
				<div class="da-support-user__description">
					<b><?php echo esc_html__( 'Note!', 'development-assistant' ); ?></b> <?php echo esc_html__( 'The password is displayed only once, then it will be encrypted and you will not be able to see or share it.', 'development-assistant' ); ?>
					<br>
					<b><?php echo esc_html__( 'Please, be careful who you share access with, this user will have access to all administrative capabilities.', 'development-assistant' ); ?></b>
				</div>
			<?php } else { ?>
				<div class="da-support-user__description">
					<b><?php echo esc_html__( 'Note!', 'development-assistant' ); ?></b> <?php echo esc_html__( 'You cannot share credentials that have already been displayed. Recreate the user if necessary.', 'development-assistant' ); ?>
				</div>
			<?php } ?>
			<ul class="da-support-user__controls">
				<?php if ( ! $is_password_shown ) { ?>
					<li>
						<button  type="button" class="button da-support-user__copy" id="da-copy-support-user-credentials">
							<span class="da-support-user__copy-text">
								<?php echo esc_html__( 'Copy to Clipboard', 'development-assistant' ); ?>
							</span>
							<span class="da-support-user__copied-text">
								<span class="dashicons dashicons-saved"></span>
								<span><?php echo esc_html__( 'Copied!', 'development-assistant' ); ?></span>
							</span>
						</button>
					</li>
				<?php } elseif ( static::is_allowed_continue_existence() ) { ?>
					<li>
						<a
							href="<?php echo esc_url( ActionQuery::get_url( static::UPDATE_CREATE_AT_QUERY_KEY ) ); ?>"
						>
							<?php echo esc_html__( 'Continue existence', 'development-assistant' ); ?>
						</a>
					</li>
				<?php } ?>
				<li>
					<?php
					( new Link(
						__( 'Recreate user', 'development-assistant' ),
						ActionQuery::get_url( static::RECREATE_QUERY_KEY ),
						static::get_recreation_confirmation_massage()
					) )->render();
					?>
				</li>
				<li>
					<?php
					( new Link(
						__( 'Delete user', 'development-assistant' ),
						ActionQuery::get_url( static::DELETE_QUERY_KEY ),
						static::get_deletion_confirmation_massage(),
						false,
						'da-support-user__link-danger'
					) )->render();
					?>
				</li>
			</ul>
			<?php if ( ! $is_password_shown ) { ?>
				<div class="da-support-user__share-form" id="da-share-support-user-form">
					<div class="da-support-user__share-header">
						<span><?php echo esc_html__( 'Or you can', 'development-assistant' ); ?></span>
						<h2><?php echo esc_html__( 'Share to Email', 'development-assistant' ); ?></h2>
					</div>
					<div class="da-support-user__share-field">
						<input
							id="da-share-support-user-email"
							type="email"
							placeholder="<?php echo esc_attr__( 'Email you want to share with', 'development-assistant' ); ?>"
							aria-label="<?php echo esc_attr__( 'Email', 'development-assistant' ); ?>"
							value="<?php echo esc_attr( apply_filters( static::EMAIL_HOOK, '' ) ); ?>"
						>
					</div>
					<div class="da-support-user__share-field">
						<textarea
							id="da-share-support-user-message"
							placeholder="<?php echo esc_attr__( 'Your message (optional)', 'development-assistant' ); ?>"
							aria-label="<?php echo esc_attr__( 'Optional message', 'development-assistant' ); ?>"
						></textarea>
					</div>
					<button type="button" class="button" id="da-share-support-user">
						<?php echo esc_html__( 'Share Credentials', 'development-assistant' ); ?>
					</button>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	public static function get_details( string $classname, bool $display_email = true ): string {
		ob_start();

		$email          = get_option( static::EMAIL_KEY, static::EMAIL_DEFAULT );
		$is_auto_delete = 0 < intval( get_option( static::DELETE_AFTER_DAYS_KEY, static::DELETE_AFTER_DAYS_DEFAULT ) );

		if ( $email || $is_auto_delete ) {
			?>
			<ul class="<?php echo esc_attr( $classname ); ?>">
				<?php if ( $email && $display_email ) { ?>
					<li>
						<b><?php echo esc_html__( 'Shared with email', 'development-assistant' ); ?>:</b> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</li>
					<?php
				}

				if ( $is_auto_delete ) {
					?>
					<li>
						<b><?php echo esc_html__( 'Will be auto-deleted', 'development-assistant' ); ?>:</b>
						<?php
						echo sprintf(
							esc_html__( 'after less than %s days', 'development-assistant' ),
							esc_html( static::get_days_for_auto_delete() )
						);
						?>
					</li>
				<?php } ?>
			</ul>
			<?php
		}

		return ob_get_clean();
	}

	public static function get_recreation_confirmation_massage(): string {
		return __( 'Are you sure to recreate the support user? This will cause loss of access for those providing support, you will need to reshare this to keep their access.', 'development-assistant' );
	}

	public static function get_deletion_confirmation_massage(): string {
		return __( 'Are you sure to delete the support user? This will cause loss of access for those providing support.', 'development-assistant' );
	}

	protected function render_user_data_section_empty(): void {
		?>
		<div class="da-support-user__description">
			<?php echo esc_html__( 'Support user not yet created.', 'development-assistant' ); ?>
		</div>
		<ul class="da-support-user__controls">
			<li>
				<a href="<?php echo esc_url( ActionQuery::get_url( static::CREATE_QUERY_KEY ) ); ?>">
					<?php echo esc_html__( 'Create user in one click', 'development-assistant' ); ?>
				</a>
			</li>
		</ul>
		<?php
	}

	protected function get_password_once_and_mask_it(): string {
		$password = get_option( static::PASSWORD_KEY, static::PASSWORD_DEFAULT );

		update_option( static::PASSWORD_KEY, static::PASSWORD_SHOWN_VALUE );

		return $password;
	}

	public static function is_allowed_continue_existence(): bool {
		return 1 <= intval( get_option( static::DELETE_AFTER_DAYS_KEY, static::DELETE_AFTER_DAYS_DEFAULT ) ) &&
			1 === static::get_days_for_auto_delete();
	}

	protected static function get_days_for_auto_delete(): int {
		$created_at   = intval( get_option( static::CREATED_AT_KEY, static::CREATED_AT_DEFAULT ) );
		$delete_after = intval( get_option( static::DELETE_AFTER_DAYS_KEY, static::DELETE_AFTER_DAYS_DEFAULT ) );
		$result       = ceil( ( $created_at + $delete_after * DAY_IN_SECONDS - time() ) / DAY_IN_SECONDS );

		return 0 < $result ? $result : 1;
	}

	public function handle_create_user(): void {
		static::create_user();
		Notice::add_transient( __( 'Support user created.', 'development-assistant' ), 'success' );
	}

	public function handle_delete_user(): void {
		static::delete_user();
		Notice::add_transient( __( 'Support user deleted.', 'development-assistant' ), 'success' );
	}

	public function handle_recreate_user(): void {
		static::delete_user();
		static::create_user();
		Notice::add_transient( __( 'Support user recreated.', 'development-assistant' ), 'success' );
	}

	public function handle_share_to_email( array $data, array $post_data ): void {
		$email       = filter_var( wp_unslash( $data[ static::SHARE_EMAIL_QUERY_KEY ] ), FILTER_VALIDATE_EMAIL );
		$password    = sanitize_text_field( wp_unslash( $post_data[ static::SHARE_PASSWORD_QUERY_KEY ] ) );
		$redirect_to = remove_query_arg( array( static::SHARE_EMAIL_QUERY_KEY, '_wpnonce' ) );

		if ( ! $email ) {
			Notice::add_transient( __( 'Invalid email. Please recreate the user and try sharing again.', 'development-assistant' ), 'error' );
			wp_safe_redirect( $redirect_to );

			exit;
		}

		if ( empty( $password ) || static::PASSWORD_SHOWN_VALUE === $password ) {
			Notice::add_transient( __( 'An error occurred while trying to send the email.', 'development-assistant' ), 'error' );
			wp_safe_redirect( $redirect_to );

			exit;
		}

		$subject = sprintf(
			__( 'Support access to %s', 'development-assistant' ),
			str_replace( array( 'http://', 'https://' ), '', home_url() )
		);
		$message = sanitize_text_field( wp_unslash( $post_data[ static::SHARE_MESSAGE_QUERY_KEY ] ) );
		$content = $this->get_share_to_email_content( $password, $message );
		$user    = wp_get_current_user();
		$from    = $user->user_email ?
			'From: ' . $user->display_name . ' <' . $user->user_email . '>' :
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>';
		$headers = array( 'Content-Type: text/html; charset=UTF-8', $from );

		if ( ! wp_mail( $email, $subject, $content, $headers ) ) {
			Notice::add_transient( __( 'An error occurred while trying to send the email.', 'development-assistant' ), 'error' );
			wp_safe_redirect( $redirect_to );

			exit;
		}

		wp_update_user(
			array(
				'ID'         => get_option( static::ID_KEY, static::ID_DEFAULT ),
				'user_email' => $email,
			)
		);
		update_option( static::EMAIL_KEY, $email );
		Notice::add_transient(
			sprintf(
				__( 'Credentials successfully shared this %s.', 'development-assistant' ),
				$email
			),
			'success'
		);
		wp_safe_redirect( $redirect_to );

		exit;
	}

	protected function get_share_to_email_content( string $password, string $message ): string {
		$login     = get_option( static::LOGIN_KEY, static::LOGIN_DEFAULT );
		$home_url  = home_url();
		$login_url = wp_login_url();

		ob_start();

		echo sprintf(
			esc_html__( 'You have been requested for support and granted administrative access to %s.', 'development-assistant' ),
			'<a href="' . esc_url( $home_url ) . '">' . esc_html( $home_url ) . '</a>'
		);

		if ( 0 < intval( get_option( static::DELETE_AFTER_DAYS_KEY, static::DELETE_AFTER_DAYS_DEFAULT ) ) ) {
			?>
			<br><br>
			<b><?php echo esc_html__( 'Note!', 'development-assistant' ); ?></b> <?php echo sprintf( esc_html__( 'User will be auto-deleted after less than %s days.', 'development-assistant' ), esc_html( static::get_days_for_auto_delete() ) ); ?>
		<?php } ?>
		<br><br>
		<a href="<?php echo esc_url( $login_url ); ?>"><?php echo esc_html( $login_url ); ?></a>
		<br>
		<b><?php echo esc_html__( 'Username', 'development-assistant' ); ?>:</b> <?php echo esc_html( $login ); ?>
		<br>
		<b><?php echo esc_html__( 'Password', 'development-assistant' ); ?>:</b> <?php echo esc_html( $password ); ?>
		<?php if ( $message ) { ?>
			<br><br>
			<b><?php echo esc_html__( 'Message from customer', 'development-assistant' ); ?>:</b>
			<br>
			<?php
			echo esc_html( $message );
		}
		return ob_get_clean();
	}

	protected static function create_user(): void {
		$time     = time();
		$login    = 'support_' . $time;
		$password = wp_generate_password();

		$user_id = wp_insert_user(
			array(
				'user_login' => $login,
				'user_pass'  => $password,
				'role'       => 'administrator',
			)
		);

		if ( $user_id instanceof WP_Error ) {
			Notice::add_transient( $user_id->get_error_message(), 'error' );

			return;
		}

		update_option( static::ID_KEY, $user_id );
		update_option( static::LOGIN_KEY, $login );
		update_option( static::PASSWORD_KEY, $password );
		update_option( static::CREATED_AT_KEY, $time );
	}

	public function handle_update_create_at(): void {
		update_option( static::CREATED_AT_KEY, time() );
		Notice::add_transient( __( 'Support user existence extended.', 'development-assistant' ), 'success' );
	}

	protected static function delete_user(): void {
		$user_id = get_option( static::ID_KEY, static::ID_DEFAULT );

		if ( 0 === $user_id ) {
			return;
		}

		wp_delete_user( $user_id );
	}

	public function delete_user_if_disabled( string $old_value, string $value ): void {
		if ( 'yes' === $value ) {
			return;
		}

		static::delete_user();
	}

	public function delete_user_after_days(): void {
		$created_at   = intval( get_option( static::CREATED_AT_KEY, static::CREATED_AT_DEFAULT ) );
		$delete_after = intval( get_option( static::DELETE_AFTER_DAYS_KEY, static::DELETE_AFTER_DAYS_DEFAULT ) );

		if ( 0 === $created_at || 0 === $delete_after ) {
			return;
		}

		$delete_time = $created_at + $delete_after * DAY_IN_SECONDS;

		if ( $delete_time < time() ) {
			static::delete_user();
		}
	}

	public function delete_data_when_user_deleted( int $user_id ): void {
		if ( intval( get_option( static::ID_KEY, static::ID_DEFAULT ) ) !== $user_id ) {
			return;
		}

		static::delete_user_data();
	}

	protected static function delete_user_data(): void {
		delete_option( static::ID_KEY );
		delete_option( static::LOGIN_KEY );
		delete_option( static::PASSWORD_KEY );
		delete_option( static::CREATED_AT_KEY );
		delete_option( static::EMAIL_KEY );
	}

	public static function is_current_user(): bool {
		return get_current_user_id() === intval( get_option( static::ID_KEY, static::ID_DEFAULT ) );
	}

	/**
	 * @throws Exception
	 */
	public function enqueue_assets(): void {
		if ( ! static::is_current() ) {
			return;
		}

		parent::enqueue_assets();
		Asset::enqueue_style( 'support-user' );
		Asset::enqueue_script(
			'support-user',
			array( 'jquery' ),
			array(
				'page_url'         => static::get_page_url(),
				'share_nonce'      => wp_create_nonce( static::SHARE_EMAIL_QUERY_KEY ),
				'share_query_keys' => array(
					'email'    => static::SHARE_EMAIL_QUERY_KEY,
					'password' => static::SHARE_PASSWORD_QUERY_KEY,
					'message'  => static::SHARE_MESSAGE_QUERY_KEY,
				),
			)
		);
	}

	public static function add_default_options(): void {
		if ( ! apply_filters( static::ENABLE_HOOK, true ) ) {
			return;
		}

		if ( ! in_array( get_option( static::ENABLE_KEY ), array( 'yes', 'no' ), true ) ) {
			update_option(
				static::ENABLE_KEY,
				DevEnv::is_detected_dev_env() ? 'no' : 'yes'
			);
		}
	}

	public static function reset(): void {
		static::delete_user();
		parent::reset();
	}
}
