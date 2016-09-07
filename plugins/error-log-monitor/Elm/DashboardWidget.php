<?php
class Elm_DashboardWidget {
	private $widgetId = 'ws_php_error_log';
	private $requiredCapability = 'manage_options';

	/**
	 * @var scbOptions $settings Plugin settings.
	 */
	private $settings;
	/**
	 * @var Elm_plugin $plugin A reference to the main plugin object.
	 */
	private $plugin;

	private function __construct($settings, $plugin) {
		$this->settings = $settings;
		$this->plugin = $plugin;
		add_action('wp_dashboard_setup', array($this, 'registerWidget'));
		add_action('admin_init', array($this, 'handleLogClearing'));
	}

	public function registerWidget() {
		if ( $this->userCanSeeWidget() ) {
			wp_add_dashboard_widget(
				$this->widgetId,
				/* translators: Dashboard widget name */
				__('PHP Error Log', 'error-log-monitor'),
				array($this, 'displayWidgetContents'),
				array($this, 'handleSettingsForm')
			);
		}
	}

	private function userCanSeeWidget() {
		return apply_filters('elm_show_dashboard_widget', current_user_can($this->requiredCapability));
	}

	public function displayWidgetContents() {
		$log = Elm_PhpErrorLog::autodetect();

		if ( is_wp_error($log) ) {
			$this->displayConfigurationHelp($log->get_error_message());
			return;
		}

		if ( isset($_GET['elm-log-cleared']) && !empty($_GET['elm-log-cleared']) ) {
			printf('<p><strong>%s</strong></p>', __('Log cleared.', 'error-log-monitor'));
		}

		$lines = $log->readLastLines($this->settings->get('widget_line_count'), true);
		if ( is_wp_error($lines) ) {
			printf('<p>%s</p>', $lines->get_error_message());
		} else if ( empty($lines) ) {
			printf('<p>%s</p>', __('The log file is empty.', 'error-log-monitor'));
		} else {
			if ($this->settings->get('sort_order') === 'reverse-chronological') {
				$lines = array_reverse($lines);
			}
			echo '<table class="widefat" style="table-layout: fixed; overflow: hidden; box-sizing: border-box;">',
			     '<colgroup><col style="width: 9em;"><col></colgroup>',
			     '<tbody>';
			$isOddRow = false;
			foreach ($lines as $line) {
				$isOddRow = !$isOddRow;
				if ( $this->settings->get('strip_wordpress_path') ) {
					$line['message'] = $this->plugin->stripWpPath($line['message']);
				}

				printf(
					'<tr%s><td style="white-space:nowrap;">%s</td><td>%s</td></tr>',
					$isOddRow ? ' class="alternate"' : '',
					!empty($line['timestamp']) ? $this->plugin->formatTimestamp($line['timestamp']) : '',
					esc_html($line['message'])
				);
			}
			echo '</tbody></table>';

			echo '<p>';
			printf(
				/* translators: 1: Log file name, 2: Log file size */
				__('Log file: %1$s (%2$s)', 'error-log-monitor') . ' ',
				esc_html($log->getFilename()),
				Elm_Plugin::formatByteCount($log->getFileSize(), 2)
			);
			printf(
				'<a href="%s" class="button" onclick="return confirm(\'%s\');">%s</a>',
				wp_nonce_url(admin_url('/index.php?elm-action=clear-log&noheader=1'), 'clear-log'),
				esc_js(__('Are you sure you want to clear the error log?', 'error-log-monitor')),
				__('Clear Log', 'error-log-monitor')
			);

			echo '</p>';
		}
	}

	private function displayConfigurationHelp($problem) {

		$exampleCode = "ini_set('log_errors', 'On');\n" . "ini_set('error_log', '/full/path/to/php-errors.log');";
		printf('<p><strong>%s</strong></p>', $problem);

		echo '<p>';
		_e(
			'To enable error logging, create an empty file named "php-errors.log".
			Place it in a directory that is not publicly accessible (preferably outside
			your web root) and ensure it is writable by the web server.
			Then add the following code to <code>wp-config.php</code>:',
			'error-log-monitor'
		);
		echo '</p>';
		echo '<pre>', $exampleCode, '</pre>';

		echo '<p>';
		printf(
			__('For reference, the full path of the WordPress directory is:<br>%s', 'error-log-monitor'),
			'<code>' . htmlentities(ABSPATH) . '</code>'
		);
		echo '</p>';

		echo '<p>';
		printf(
			/* translators: Links to English-language articles about configuring error logging. */
			__('See also: %s', 'error-log-monitor'),
			'<a href="http://codex.wordpress.org/Editing_wp-config.php#Configure_Error_Log">Editing wp-config.php</a>,
			 <a href="http://digwp.com/2009/07/monitor-php-errors-wordpress/">3 Ways To Monitor PHP Errors</a>'
		);
		echo '</p>';
	}

	public function handleSettingsForm() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['widget_id']) && is_array($_POST[$this->widgetId]) ) {
			$formInputs = $_POST[$this->widgetId];

			$this->settings->set('widget_line_count', intval($formInputs['widget_line_count']));
			if ( $this->settings->get('widget_line_count') <= 0 ) {
				$this->settings->set('widget_line_count', $this->settings->get_defaults('widget_line_count'));
			}

			$this->settings->set('strip_wordpress_path', isset($formInputs['strip_wordpress_path']));
			$this->settings->set('send_errors_to_email', trim(strval($formInputs['send_errors_to_email'])));

			$this->settings->set('email_interval', intval($formInputs['email_interval']));
			if ( $this->settings->get('email_interval') <= 60 ) {
				$this->settings->set('email_interval', $this->settings->get_defaults('email_interval'));
			}

			$this->settings->set('sort_order', strval($formInputs['sort_order']));
			if ( !in_array($this->settings->get('sort_order'), array('chronological', 'reverse-chronological')) ) {
				$this->settings->set('sort_order', $this->settings->get_defaults('sort_order'));
			}

			$enableLogSizeNotification = isset($formInputs['enable_log_size_notification']);
			//Reset the "notification sent" flag when the user turns notifications on/off.
			if ( $enableLogSizeNotification != $this->settings->get('enable_log_size_notification') ) {
				//This is useful for testing and situations where the flag was set and then notifications got
				//temporarily turned off, with the log file size changing in the meantime.
				$this->settings->set('log_size_notification_sent', false);
			}
			$this->settings->set('enable_log_size_notification', $enableLogSizeNotification);

			if (
				$this->settings->get('enable_log_size_notification')
				&& isset($formInputs['log_size_notification_threshold'])
			) {
				$this->settings->set(
					'log_size_notification_threshold',
					floatval($formInputs['log_size_notification_threshold']) * Elm_Plugin::MB_IN_BYTES
				);
			}

			do_action('elm_settings_changed', $this->settings);
		}

		printf(
			'<p><label>%s <input type="text" name="%s[widget_line_count]" value="%s" size="5"></label></p>',
			__('Number of lines to show:', 'error-log-monitor'),
			esc_attr($this->widgetId),
			esc_attr($this->settings->get('widget_line_count'))
		);

		printf(
			'<p><label><input type="checkbox" name="%s[strip_wordpress_path]"%s> %s</label></p>',
			esc_attr($this->widgetId),
			$this->settings->get('strip_wordpress_path') ? ' checked="checked"' : '',
			__('Strip WordPress root directory from log messages', 'error-log-monitor')
		);

		printf(
			'<p><label><input type="checkbox" name="%s[sort_order]" value="reverse-chronological" %s> %s</label></p>',
			esc_attr($this->widgetId),
			$this->settings->get('sort_order') === 'reverse-chronological' ? ' checked="checked"' : '',
			__('Reverse line order (most recent on top)', 'error-log-monitor')
		);

		printf(
			'<p>
				<label for="%1$s-send_errors_to_email">%2$s</label>
				<input type="text" class="widefat" name="%1$s[send_errors_to_email]" id="%1$s-send_errors_to_email" value="%3$s">
			</p>',
			esc_attr($this->widgetId),
			__('Periodically email logged errors to:', 'error-log-monitor'),
			$this->settings->get('send_errors_to_email')
		);

		printf(
			'<p><label>%s <select name="%s[email_interval]">',
			__('How often to send email (max):', 'error-log-monitor'),
			esc_attr($this->widgetId)
		);
		$intervals = array(
			__('Every 10 minutes', 'error-log-monitor') => 10*60,
			__('Every 15 minutes', 'error-log-monitor') => 15*60,
			__('Every 30 minutes', 'error-log-monitor') => 30*60,
			__('Hourly', 'error-log-monitor')           => 60*60,
			__('Daily', 'error-log-monitor')            => 24*60*60,
			__('Weekly', 'error-log-monitor')           => 7*24*60*60,
		);
		foreach($intervals as $name => $interval) {
			printf(
				'<option value="%d"%s>%s</option>',
				$interval,
				($interval == $this->settings->get('email_interval')) ? ' selected="selected"' : '',
				$name
			);
		}
		echo '</select></label></p>';

		printf(
			'<p><label><input type="checkbox" name="%s[enable_log_size_notification]" 
		                      id="elm_enable_log_size_notification"%s> %s</label><br>',
			esc_attr($this->widgetId),
			$this->settings->get('enable_log_size_notification') ? ' checked="checked"' : '',
			__('Send an email notification when the log file size exceeds this limit:', 'error-log-monitor')
		);
		printf(
			'<input type="number" name="%s[log_size_notification_threshold]" value="%s" 
			        size="1" min="1" max="10240" style="max-width: 80px;" 
			        id="elm_log_size_notification_threshold" %s> MiB',
			esc_attr($this->widgetId),
			$this->settings->get('log_size_notification_threshold') / Elm_Plugin::MB_IN_BYTES,
			$this->settings->get('enable_log_size_notification') ? '' : ' disabled="disabled"'
		);
		echo '</p>';

		//This script is too short to be worth placing in a separate file. Lets just inline it.
		?>
		<script type="text/javascript">
			jQuery(function($) {
				var sizeNotificationEnabled = $('#elm_enable_log_size_notification');
				sizeNotificationEnabled.change(function() {
					$('#elm_log_size_notification_threshold').prop('disabled', !sizeNotificationEnabled.is(':checked'));
				});
			});
		</script>
		<?php
	}

	public function handleLogClearing() {
		$doClearLog =  isset($_GET['elm-action']) && ($_GET['elm-action'] === 'clear-log')
			&& check_admin_referer('clear-log') && $this->userCanSeeWidget();

		if ( $doClearLog ) {
			$log = Elm_PhpErrorLog::autodetect();
			if ( is_wp_error($log) ) {
				return;
			}

			$log->clear();

			//Since the log is empty now, we can reset the file size notification.
			$this->settings->set('log_size_notification_sent', false);

			wp_redirect(admin_url('index.php?elm-log-cleared=1'));
			exit();
		}
	}

	public static function getInstance($settings, $plugin) {
		static $instance = null;
		if ( $instance === null ) {
			$instance = new self($settings, $plugin);
		}
		return $instance;
	}
}