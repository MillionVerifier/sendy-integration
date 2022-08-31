<?php include('../_connect.php');?>
<?php include('../../includes/helpers/short.php');?>
<?php 
	//-------------------------- ERRORS -------------------------//
	$error_core = array('API key not passed', 'Invalid API key');
	//-----------------------------------------------------------//
	
	//--------------------------- GET --------------------------//
	//api_key
	if(isset($_GET['api_key'])) $api_key = mysqli_real_escape_string($mysqli, $_GET['api_key']);
	else $api_key = null;
	//-----------------------------------------------------------//
	
	//----------------------- VERIFICATION ----------------------//
	if($api_key==null)
	{
        echo json_encode(array('success' => false, 'error' => $error_core[0]));
		exit;
	}
	else if(!verify_api_key($api_key))
	{
        echo json_encode(array('success' => false, 'error' => $error_core[1]));
		exit;
	}
	//-----------------------------------------------------------//
	
	//-------------------------- QUERY --------------------------//
	
	//Get lists
	$q = <<<QUERY_STR
    SELECT 
        l.id AS id, 
        l.name AS name
    FROM lists AS l
    WHERE l.hide = 0 
    ORDER BY id DESC;
    QUERY_STR;

	$r = mysqli_query($mysqli, $q);
    $output = array(
		"success" => true,
		"data" => array()
	);
	
    if ($r && mysqli_num_rows($r) > 0)
	{		
		while($row = mysqli_fetch_array($r))
		{
			$id = $row['id'];
			$name = $row['name'];

			$q_subscribers_count = <<<QUERY_STR
			SELECT
				COUNT(s.id) AS subscribers_count
			FROM
				subscribers AS s
			WHERE
				s.list = {$id} AND
				s.unsubscribed = 0
			QUERY_STR;

			$r_subscribers_count = mysqli_query($mysqli, $q_subscribers_count);
			if($r_subscribers_count && mysqli_num_rows($r_subscribers_count) > 0) 
			{
				while($row2 = mysqli_fetch_array($r_subscribers_count)) 
				{
					$subscribers_count = $row2["subscribers_count"];
					$output["data"][] = array(
						"id" => encrypt_val($id),
						"name" => $name,
						"subscribers_count" => $subscribers_count
					);
					break;
				}
			}
		}
	}
	
    echo json_encode($output);
	//-----------------------------------------------------------//
?>