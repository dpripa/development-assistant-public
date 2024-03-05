<?php
namespace WPDevAssist\PluginsScreen;

use WPDevAssist\Plugin;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use const WPDevAssist\KEY;

defined( 'ABSPATH' ) || exit;

class Downloader {
	protected const DOWNLOAD_QUERY_KEY = KEY . '_download_plugin';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_download' ) );
	}

	public static function get_url( string $plugin_file ): string {
		return wp_nonce_url(
			add_query_arg(
				array( static::DOWNLOAD_QUERY_KEY => $plugin_file ),
				Plugin\Url::get_admin( 'plugins' )
			),
			static::DOWNLOAD_QUERY_KEY
		);
	}

	public function handle_download(): void {
		if (
			empty( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), static::DOWNLOAD_QUERY_KEY ) ||
			empty( $_GET[ static::DOWNLOAD_QUERY_KEY ] ) ||
			! current_user_can( 'edit_plugins' )
		) {
			return;
		}

		$plugin_file = sanitize_text_field( wp_unslash( $_GET[ static::DOWNLOAD_QUERY_KEY ] ) );
		$plugin_dir  = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
		$zip_file    = sys_get_temp_dir() . '/' . dirname( $plugin_file ) . '.zip';
		$zip         = new ZipArchive();

		if ( is_int( $zip->open( $zip_file, ZipArchive::CREATE ) ) ) {
			return;
		}

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $plugin_dir ),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				continue;
			}

			$file_path     = $file->getRealPath();
			$relative_path = substr( $file_path, strlen( $plugin_dir ) + 1 );

			$zip->addFile( $file_path, $relative_path );
		}

		$zip->close();

		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . basename( $zip_file ) . '"' );
		header( 'Content-Length: ' . filesize( $zip_file ) );
		flush();
		readfile( $zip_file ); // phpcs:ignore
		unlink( $zip_file );

		exit;
	}
}
