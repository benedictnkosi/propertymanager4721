<?php
      $command = "rsync -av /propertymanager4721/images/1.jpeg /tmp/";
	  
	  
	  exec($command.' 2>&1', $tmp, $return_code); // Execute the command
	// Output the result
	printf('
<span class="prompt">$</span> <span class="command">%s</span>
<div class="output">%s</div>
'
		, htmlentities(trim($command))
		, htmlentities(trim(implode("\n", $tmp)))
	);
	$output .= ob_get_contents();
	ob_flush(); 
	
?>
