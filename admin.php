<?php
function adminer_object() {

	class KyseloAdmin extends Adminer {
		protected $_kyseloConfig = [];

		function __construct($config)
		{
			$this->_kyseloConfig = $config;
		}

		function name() {
			// custom name in title and heading
			return 'Kyselo admin';
		}

		function permanentLogin($i = false) {
			// key used for permanent login
			return md5($this->_kyseloConfig['secret']);
		}

		function credentials() {
			// server, username and password for connecting to database
			return array('', '', '');
		}

		function databases($Jc = true) {
			return array($this->_kyseloConfig['database']);
		}

		function login($login, $password) {
			// validate user submitted credentials
			return ($login == 'admin' && md5($password) == $this->_kyseloConfig['adminer_password']);
		}

		function loginForm() {
			?>
			<table cellspacing="0">
				<tr><th><?php echo lang('Username'); ?><td><input type="hidden" name="auth[driver]" value="server"><input name="auth[username]" id="username" value="<?php echo h($_GET["username"]); ?>" autocapitalize="off">
				<tr><th><?php echo lang('Password'); ?><td><input type="password" name="auth[password]">
			</table>
			<input type="hidden" name="auth[driver]" value="sqlite">
			<input type="hidden" name="auth[server]">
			<input type="hidden" name="auth[db]" value="<?php h($this->_kyseloConfig['database']); ?>">
			<script type="text/javascript">
				focus(document.getElementById('username'));
			</script>
			<?php
			echo "<p><input type='submit' value='" . lang('Login') . "'>\n";
			echo checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang('Permanent login')) . "\n";
		}
	}
	
	
	if (!file_exists(__DIR__ . '/config.php')) {
		die("ERROR: Kyselo not installed.");
	}

	$config = require 'config.php';

	return new KyseloAdmin($config);
}

include "./adminer.php";