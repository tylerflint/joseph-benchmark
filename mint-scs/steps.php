<?php

// now returns false on succcess
function gateway($action, $post = '')
{
	$host	 	= 'www.haveamint.com';
	$gateway	= '/gateway/';
	$useCURL 	= in_array('curl', get_loaded_extensions());
	$mintPing 	= "X-mint-ping: $action";
	$response	= '';
	$method		= (empty($post))?'GET':'POST';
		
	// There's a bug in the OS X Server/cURL combination that results in 
	// memory allocation problems so don't use cURL even if it's available
	if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Darwin') !== false)
	{
		$useCURL = false;
	}
	
	if ($useCURL)
	{
		$handle		= curl_init("http://{$host}{$gateway}");
		curl_setopt($handle, CURLOPT_HTTPHEADER, array($mintPing));
		curl_setopt($handle,CURLOPT_CONNECTTIMEOUT,5);
		curl_setopt($handle,CURLOPT_RETURNTRANSFER,1);
	}
	else
	{
		$headers	 = "{$method} {$gateway} HTTP/1.0\r\n";
		$headers	.= "Host: $host\r\n";
		$headers	.= "{$mintPing}\r\n";
	}
	
	if (!empty($post)) // This is a POST request
	{
		if ($useCURL)
		{
			curl_setopt($handle,CURLOPT_POST,true);
			curl_setopt($handle,CURLOPT_POSTFIELDS,$post);
		}
		else
		{
			$headers	.= "Content-type: application/x-www-form-urlencoded\r\n";
			$headers	.= "Content-length: ".strlen($post)."\r\n";
		}
	}
	
	$error = '';
	if ($useCURL)
	{
		$response = curl_exec($handle);
		if (curl_errno($handle))
		{
			$error = 'Could not connect to Gateway (using cURL): '.curl_error($handle);
		}
		curl_close($handle);
	}
	else
	{
		$headers	.= "\r\n";
		$socket		 = @fsockopen($host, 80, $errno, $errstr, 5);
		if ($socket)
		{
			fwrite($socket, $headers.$post);
			while (!feof($socket)) 
			{
				$response .= fgets ($socket, 1024);
			}
			$response = explode("\r\n\r\n", $response, 2);
			$response = trim($response[1]);
		}
		else
		{
			$error = 'Could not connect to Gateway (using fsockopen): '.$errstr.' ('.$errno.')';
			$response = 'FAILED';
		}
	}
	
	return ($response != 'CONNECTED') ? $error : false;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title>Mint: Server Compatibility Suite</title>
<link rel="stylesheet" href="styles.css" type="text/css" />
</head>
<body>
<div id="container">
	<h1>Mint Server Compatibility Suite</h1>
	
	<?php
	include_once('direct-access.php');
	
	$requiredPHP	= '4.2.3';
	$requiredMySQL	= '3.23';
			
	$html 		= '';
	$heading	= '';
	$results 	= array();
	$tail		= array();
	$flag 		= 0;
	
	$step = (!isset($_GET['step']))?'1':$_GET['step'];
	
	switch ($step)
	{
		case '1':
			
			$heading = 'Step One: Server Software, PHP Version, and MySQL Version';
			
			// Check server software
			if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'],'IIS')!==false)
			{
				$flag = 1;
				$results[] = array
				(
					'Mint does not currently support IIS.',
					0
				);
			}
			
			// Check PHP version
			if (!version_compare(PHP_VERSION, $requiredPHP, 'ge'))
			{
				$flag = 1;
				$results[] = array
				(
					'PHP '.PHP_VERSION.' is not compatible with Mint. PHP '.$requiredPHP.' or higher is required.',
					0
				);
			}
			else 
			{
				$results[] = array
				(
					'PHP '.PHP_VERSION.' is compatible with Mint.',
					1
				);
			}
			
			// Check MySQL version
			$extensions = get_loaded_extensions();
			if (!in_array('mysql',$extensions))
			{
				$flag = 1;
				$results[] = array
				(
					'Mint requires MySQL. PHP does not appear to be compiled with support for MySQL',
					0
				);
			}
			else
			{
				$mysqlVersion = mysql_get_client_info();
				$mysqlVersion = preg_replace('#(^\D*)([0-9.]+).*$#', '\2', $mysqlVersion); // strip extra-version cruft
				if (!version_compare($mysqlVersion, $requiredMySQL, 'ge'))
				{
					$flag = 1;
					$results[] = array
					(
						'MySQL '.$mysqlVersion.' is not compatible with Mint. MySQL '.$requiredMySQL.' or higher is required.',
						0
					);
				}
				else
				{
					$results[] = array
					(
						'MySQL '.$mysqlVersion.' is compatible with Mint.',
						1
					);
				}
			}
			
			$tail = array
			(
				'I\'m sorry but your server is not compatible with the current version of Mint.',
				'Looking good so far. <a href="?step=2&gettest">Proceed to Step Two.</a>'
			);
		break;
		
		case '2':
			$heading = 'Step Two: Server Navigability or "Will Mint be Able to Find Its Way Around Your Server?"';
			
			// Check presence of undefined $_GET indexes
			if (strpos($_SERVER['QUERY_STRING'],'gettest') && !isset($_GET['gettest']))
			{
				$flag = 1;
				$results[] = array
				(
					'Mint will not be able to gather visit data because your server does not populate the PHP global $_GET array as expected.',
					0
				);
			}
			else
			{
				$results[] = array
				(
					'Your server populates the PHP global $_GET array as expected.',
					1
				);
			}
			
			/** /
			// Check for document root
			if (!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT']))
			{
				$results[] = array
				(
					'Your server does not identify the document root. You will need to get this information from your host and manually configure Mint.',
					2
				);
			}
			else
			{
				// Check for wrong document root
				if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/mint-scs/'))
				{
				$results[] = array
				(
					'Your server does not correctly identify the document root. You will need to get this information from your host and manually configure Mint.',
					2
				);
				}
				else
				{
					$results[] = array
					(
						'Mint was able to locate your document root.',
						1
					);
					
					// Check for install directory detection
					$install_dir = preg_replace("/[^\/]*$/","",((!empty($_SERVER['PHP_SELF']))?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_URL']));
					if (!is_dir($_SERVER['DOCUMENT_ROOT'].$install_dir))
					{
						$flag = 1;
						$results[] = array
						(
							'Mint is unable to determine its position in your server document hierarchy.',
							0
						);
					}
					else
					{
						$results[] = array
						(
							'Mint should have no trouble finding Pepper and related files on your server.',
							1
						);
					}
				}
			}
			/**/
			$dbform = <<<HERE
In order to complete the next step which checks that you have the correct database permissions, you need to enter your database connection info below. <strong>This info will not be transmitted back to haveamint.com.</strong> The next step will attempt to connect to your database using the credentials provided below. It will then create a table named "mint_scs", alter that table by adding an additional column and then delete the table, simulating the actions Mint makes during normal use.</p>

<form method="post" action="?step=3">
	<table border="0">
		<tr>
			<td>Database Server</td>
			<td><input type="text" name="db[server]" value="localhost" /></td>
		</tr>
		<tr>
			<td>Database Username</td>
			<td><input type="text" name="db[username]" /></td>
		</tr>
		<tr>
			<td>Database Password</td>
			<td><input type="password" name="db[password]" /></td>
		</tr>
		<tr>
			<td>Database Name</td>
			<td><input type="text" name="db[database]" /></td>
		</tr>
	</table>
	<input type="submit" value="Proceed to Step Three" />
</form>

<p>Disclaimer: I am not responsible for any data-loss that occurs as a result of this test.

HERE;
			
			$tail = array
			(
				'I\'m sorry but your server is not compatible with the current version of Mint.',
				$dbform
			);
			
		break;
		
		case '3':
			$heading = 'Step Three: Database Connectivity and Permissions';
			
			$db = $_POST['db'];
			
			// Check for database server connection
			if (!@mysql_pconnect($db['server'],$db['username'],$db['password']))
			{
				$flag = 1;
				$results[] = array
				(
					'Could not connect to the database server. Error: '.mysql_error(),
					0
				);
			}
			else
			{
				$results[] = array
				(
					'Connected to the database server successfully.',
					1
				);
			
				// Check database selection
				if (!@mysql_select_db($db['database']))
				{
					$flag = 1;
					$results[] = array
					(
						'Could not select the database. Error: '.mysql_error(),
						0
					);
				}
				else
				{
					$results[] = array
					(
						'Selected the database successfully.',
						1
					);
					
					$mysqlVersion = mysql_get_client_info();
					$mysqlVersion = preg_replace('#(^\D*)([0-9.]+).*$#', '\2', $mysqlVersion); // strip extra-version cruft
					$type_engine = ($mysqlVersion > 4) ? 'ENGINE' : 'TYPE';
					$query = "CREATE TABLE `mint_scs` (id int(10) unsigned NOT NULL auto_increment, PRIMARY KEY  (id)) {$type_engine}=MyISAM;";
					if (!$result = mysql_query($query))
					{
						$flag = 1;
						$results[] = array
						(
							'Could not create the table "mint_scs". Error: '.mysql_error(),
							0
						);
					}
					else
					{
						$results[] = array
						(
							'Created the table "mint_scs" successfully.',
							1
						);
						
						// Check ALTER privileges
						$query = "ALTER TABLE `mint_scs` ADD tmp VARCHAR(15) NOT NULL";
						if (!$result = mysql_query($query))
						{
							$flag = 1;
							$results[] = array
							(
								'Could not alter the table "mint_scs". Error: '.mysql_error(),
								0
							);
						}
						else
						{
							$results[] = array
							(
								'Altered the table "mint_scs" successfully.',
								1
							);
						}
						
						// Check DROP privileges
						$query = "DROP TABLE `mint_scs`";
						if (!$result = mysql_query($query))
						{
							$flag = 1;
							$results[] = array
							(
								'Could not delete the table "mint_scs". Error: '.mysql_error(),
								0
							);
						}
						else
						{
							$results[] = array
							(
								'Deleted the table "mint_scs" successfully.',
								1
							);
						}
					}
				}
			
			}
			
			$tail = array
			(
				'I\'m sorry but you will not be able to install or use Mint without being able to connect to and modify your MySQL database. Please contact your host to make sure that you have the correct database login and permissions.',
				'Not too shabby, you\'ve done this before, haven\'t you? <a href="?step=4">Proceed to Step Four.</a>'
			);
			
		break;
		
		case '4':
			$heading = 'Step Four: Mint Gateway Connectivity';
			
			// Check connectivity, returns false on success, error string on failure
			$response = gateway('Compatibility Suite Ping','dt='.time());
			
			if ($response !== false)
			{
				$flag = 1;
				$results[] = array
				(
					$response,
					0
				);
			}
			else
			{
				$results[] = array
				(
					'Connected to the gateway successfully.',
					1
				);
			}
			
			$tail = array
			(
				'I\'m sorry but your server does not appear to be able to connect to the Mint Gateway. Please see <a href="http://www.haveamint.com/faqs/troubleshooting/gateway_connectivity">this FAQ</a> for possible workarounds.',
				'<strong>Green means go!</strong> Mint is totally crushing on your server&mdash;be a pal and <a href="http://www.haveamint.com/purchase">hook them up!</a>'
			);
			
		break;
		
		default:
			$heading = 'Misstep';
			$flag = 1;
			$tail = array
			(
				'Oops, looks like you may have taken a wrong turn somewhere back there. <a href="/mint-scs/">Start over.</a>',
				''
			);
		break;
	}
	
	
	// Format results
	$html .= '<h2>'.$heading.'</h2>';
	if (!empty($results))
	{
		$html .= '<ol>';
		foreach ($results as $item)
		{
			
			switch ($item[1])
			{
				case 2:
					$html .= '<li class="caution"><span>&Delta;</span>';
				break;
				
				case 1:
					$html .= '<li class="pass"><span>&radic;</span>';
				break;
				
				case 0:
					$html .= '<li><span>&times;</span>';
				break;
			}
			
			$html .= $item[0].'</li>';
		}
		$html .= '</ol>';
	}
	
	switch ($flag)
	{
		
		case 1:
			$html .= '<h2 class="fail">'.$tail[0].'</h2>';
		break;
		
		default:
			$html .= '<p>'.$tail[1].'</p>';
		break;
	}
	
	echo $html;
	?>
	
	<div class="disclaimer">
		<p>The Mint Server Compatibility Suite helps you determine whether or not your server has the necessary technology and your database user has the necessary permissions required to install and use <a href="http://www.haveamint.com/">Mint</a>.</p>
		<p><em>(Please note that passing the Mint Server Compatibility Suite does not guarantee that Mint will run on your server. It simply confirms that your server offers the base features Mint requires.)</em></p>
	</div>	
</div>
</body>
</html>
