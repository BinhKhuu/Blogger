<?php

?>
<?php
require_once 'lib/common.php';

if ($_POST)
{
	//if logout button press
	if($_POST["logout"]) 
	{
		logout();
	}
	else 
	{
		redirectAndExit('index.php');
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
        <h1>Log out</h1>
	</head>
	<body>
		are you sure?
        <form method="post">
            <input
                name="logout"
                type="submit"
                value="log out"
            />
            <input
                name="stay"
                type="submit"
                value="stay"
            />
        </form>
	</body>

</html>
