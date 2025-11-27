<?php
	$password_to_hash = '12345';
	$hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);

		echo "<h2>Password Hashing Result</h2>";
		echo "<p><strong>Original Password:</strong> " . htmlspecialchars($password_to_hash) . "</p>";
		echo "<p><strong>Generated Hash:</strong></p>";
		echo "<pre style='background-color: #f4f4f4; padding: 15px; border: 1px solid #ddd; word-break: break-all;'>" . htmlspecialchars($hashed_password) . "</pre>";
		echo "<p>Copy the hash above and paste it into the <code>password_hash</code> column in your <code>Users</code> table.</p>";

		if (password_verify($password_to_hash, $hashed_password)) {
			echo "<p style='color: green; font-weight: bold;'>Verification successful: The hash works!</p>";
		} else {
			echo "<p style='color: red; font-weight: bold;'>Verification FAILED: Something went wrong with the hashing.</p>";
		}
?>