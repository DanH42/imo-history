<?php
ini_set('memory_limit', '1G'); // TODO: Reduce memory usage by a LOT

function badUpload($reason, $location){
	if($location)
		unlink($location);
	exit('<p>ERROR: Please upload the .zip file you <a href="http://blog.imo.im/2013/05/download-skype-chat-history-through-imo.html">downloaded from imo.im</a></p>Problem: <tt>' . $reason . '</tt></p><p><a href="/history">&lt; Go back</a></p>');
}
?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>imo.im Chat History Converter</title>
	<link rel="stylesheet" type="text/css" href="style.css" />
<body>
	<?php
	if(count($_FILES) > 0){
		if($_FILES["file"]["error"] > 0){
			if($_FILES["file"]["error"] == 1 || $_FILES["file"]["error"] == 2)
				exit("ERROR: File too large.");
			if($_FILES["file"]["error"] == 3 || $_FILES["file"]["error"] == 4)
				exit("ERROR: File did not upload completely.");
			exit("ERROR: " . $_FILES["file"]["error"]);
		}

		// Make sure the file looks like a zip archive
		if(strtolower(substr($_FILES["file"]["name"], - 4)) != ".zip")
			badUpload('The file name should end in ".zip"', $_FILES["file"]["tmp_name"]);
		if($_FILES["file"]["type"] != "application/zip" && $_FILES["file"]["type"] != "application/octet-stream")
			badUpload("The file doesn't appear to be a zip archive", $_FILES["file"]["tmp_name"]);

		// Start unzipping the uploaded file
		$zip = new ZipArchive;
		$res = $zip->open($_FILES["file"]["tmp_name"]);
		if($res === TRUE){
			// Assuming the file uploaded was correct, it should contain only history.txt
			$text = $zip->getFromName("history.txt"); // Read the text file into memory
			// Some newer exports seem to use another name, so try that too
			if(!$text)
				$text = $zip->getFromName("imo_history.txt");
			$zip->close();
			unlink($_FILES["file"]["tmp_name"]); // We're done with the zip file, so delete it
			if($text){ // We've successfully extracted their history, so it's time to start parsing!
				$history = json_decode($text, true);
				foreach($history as $id => $conv){
					$msgs = "";
					$members = array();
					$last = "";
					foreach($conv as $message){
						$from = $message['from'];
						if(strlen($from) == 32){ // This is to avoid group IDs like b130b6e981aeb5205925b58889bd55c5
							$index = strpos($message['message'], ' ');
							$from = substr($message['message'], 0, $index);
							$message['message'] = substr($message['message'], $index + 1);
						}

						$isme = "";
						if($from == "me"){
							$isme = " me";
							$from = "You";
						}elseif(!in_array($from, $members))
							$members[] = $from;

						if($from != $last){
							$msgs .= "<div class='name$isme'>" . htmlspecialchars($from) . "</div>";
							$last = $from;
						}$msgs .= "<div class='message$isme'>";
						$msgs .= "<span class='time'>" . $message['timestamp'] . "</span> ";
						if($message['message'] == "")
							$message['message'] = "(Message removed)";
						$msgs .= nl2br(htmlspecialchars($message['message'])) . "</div>";
					}

					if(count($members) == 0)
						$members[] = "Just You";
					echo "<h3 class='members' id='members_$id'>" . htmlspecialchars(implode(', ', $members)) . "</h3>";
					echo "<div class='messages' id='messages_$id'>$msgs</div>";
				}echo '<script type="text/javascript" src="script.js"></script>';
			}else
				badUpload("The archive didn't seem to contain a text file with an expected name.", false);
		}else{
			// This usually indicates a misconfigured server
			unlink($_FILES["file"]["tmp_name"]);
			echo "Server error: unable to read file";
		}
	}else{
	?>
	<h2>Convert your chat history from imo.im to something you can read!</h2>
	<p>Upload your <tt><a href="http://blog.imo.im/2013/05/download-skype-chat-history-through-imo.html">history.zip</a></tt> here:</p>
	<form action="index.php" method="post" enctype="multipart/form-data">
		<input type="file" accept="application/zip" name="file" />
		<input type="submit" value="Convert" />
	</form>
	<h3 style="margin-bottom:0">Privacy note:</h3>
	<p>This tool was not written by and is not in any way affiliated with or endorsed by imo.im or PageBites, Inc. Your chat history is saved on this server only during the time it is being converted. As soon as conversion is complete, the file is removed and no copies&mdash;whole or partial&mdash;are kept. If you'd prefer to run the conversion yourself, feel free to download the <a href="index.phps">PHP source</a> or browse its <a href="https://github.com/DanH42/imo-history">GitHub</a> repository.</p>
	<?php } ?>
</body>
</html>
