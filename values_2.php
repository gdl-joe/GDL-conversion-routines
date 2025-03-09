<?php
// Set UTF-8 header
header('Content-Type: text/html; charset=utf-8');

function generateGDL() {
	if (!isset($_POST["param_name"]) || !isset($_POST["array_prefix"]) || !isset($_POST["array_values"])) {
		return "Missing inputs!";
	}

	// ** Secure user input **
	$paramName = htmlspecialchars(trim($_POST["param_name"]), ENT_QUOTES, 'UTF-8');
	$arrayPrefix = htmlspecialchars(trim($_POST["array_prefix"]), ENT_QUOTES, 'UTF-8');
	$values = explode("\n", trim($_POST["array_values"]));

	// ** Remove potentially dangerous characters **
	$paramName = preg_replace("/[^a-zA-Z0-9_]/", "", $paramName);
	$arrayPrefix = preg_replace("/[^a-zA-Z0-9_]/", "", $arrayPrefix);

	// Generate GDL scripts
	$masterScript = "! MASTER SCRIPT\n";
	$masterScript .= "DIM {$arrayPrefix}_text[], {$arrayPrefix}_pic[], {$arrayPrefix}_value[]\n";
	$masterScript .= "i = 1\n";

	foreach ($values as $value) {
		$value = trim(strip_tags($value)); // Remove HTML and PHP tags
		if (!empty($value)) {
			$masterScript .= "{$arrayPrefix}_text[i] = \"$value\" : {$arrayPrefix}_pic[i] = \"\" : {$arrayPrefix}_value[i] = i : i = i + 1\n";
		}
	}

	$parameterScript = "! PARAMETER SCRIPT\n";
	$parameterScript .= "VALUES{2} \"$paramName\", {$arrayPrefix}_value, {$arrayPrefix}_text\n\n";

	$interfaceScript = "! INTERFACE SCRIPT\n";
	$interfaceScript .= "UI_OUTFIELD \"\", dx, dy + dyO, outL, outH, 1\n";
	$interfaceScript .= "UI_INFIELD{3} \"$paramName\", dx2, dy, inL, inH,\n";
	$interfaceScript .= "8, \"\",\n";
	$interfaceScript .= "0, 0, 0, 0, 0, 0,\n";
	$interfaceScript .= "{$arrayPrefix}_pic, {$arrayPrefix}_text, {$arrayPrefix}_value\n";
	$interfaceScript .= "dy = dy + led\n";

	return $masterScript . "\n! ----------------------------------\n\n" . $parameterScript . "\n" . $interfaceScript;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="content-type" content="text/html">
	<title>VALUES{2} Generator</title>
	<style>
		textarea, input { width: 100%; padding: 10px; margin-top: 5px; font-family: Courier New; }
		button { padding: 10px 15px; background: #000066; color: white; border: none; cursor: pointer; margin-top: 10px; }
		.code-container { position: relative; background: #f8f8f8; border-radius: 8px; padding: 0px; margin-top: 20px; border: 1px solid #ddd; }
		.code-header { display: flex; justify-content: space-between; align-items: center; background: #f1f1f1; padding: 8px 12px; border-top-left-radius: 8px; border-top-right-radius: 8px; font-size: 12px; font-weight: bold; color: #000066; }
		.copy-btn { background: none; border: none; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 6px; color: #000066; }
		.copy-btn img { width: 16px; height: 16px; opacity: 0.7; }
		pre { margin: 0; padding: 10px; white-space: pre-wrap; word-wrap: break-word; font-family: Courier New; font-size: 12px; background: #fff; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; }
	</style>
	<script>
		function copyToClipboard() {
			let text = document.getElementById("codeBlock").innerText;
			navigator.clipboard.writeText(text).then(() => {
				let copyBtn = document.getElementById("copyBtn");
				let copyIcon = document.getElementById("copyIcon");
				let copyText = document.getElementById("copyText");
				copyIcon.src = "https://cdn-icons-png.flaticon.com/512/786/786205.png";
				copyText.textContent = "Copied";
				setTimeout(() => {
					copyIcon.src = "https://cdn-icons-png.flaticon.com/512/1827/1827933.png";
					copyText.textContent = "Copy";
				}, 2000);
			});
		}
	</script>
</head>
<body>
	<h2>Generate a complete VALUES{2} command</h2>
	<p>Use this tool to generate a full VALUES{2} command for the Master Script, Parameter Script, and Interface Script.</p>
	<p>Instructions: Enter the parameter name, e.g. int_myParam, then enter the name for the array values to feed the VALUES{2} command, e.g. _myParam, and finally, enter the string values to appear in the selection list, one per line. Click 'Generate GDL Code' to proceed.</p>

	<form method="post">
		<label>Parameter Name:</label>
		<input type="text" name="param_name" required>

		<label>Array Prefix:</label>
		<input type="text" name="array_prefix" required>

		<label>Array Values (one per line):</label>
		<textarea name="array_values" rows="12" required></textarea>

		<button type="submit">Generate GDL Code</button>
	</form>

	<?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
		<div class="code-container">
			<div class="code-header">
				<span>Generated GDL Code</span>
				<button class="copy-btn" id="copyBtn" onclick="copyToClipboard()">
					<img id="copyIcon" src="https://cdn-icons-png.flaticon.com/512/1827/1827933.png">
					<span id="copyText">Copy</span>
				</button>
			</div>
			<pre id="codeBlock"><?php echo generateGDL(); ?></pre>
		</div>
	<?php endif; ?>
</body>
</html>
