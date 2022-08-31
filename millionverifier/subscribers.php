<?php include('../_connect.php');?>
<?php include('../../includes/helpers/short.php');?>
<?php 
	//-------------------------- ERRORS -------------------------//
	$error_core = array('API key not passed', 'Invalid API key');
    $error_passed = array('List ID not passed', 'List does not exist');
	//-----------------------------------------------------------//
	
	//--------------------------- GET --------------------------//
	//api_key
	if(isset($_GET['api_key'])) $api_key = mysqli_real_escape_string($mysqli, $_GET['api_key']);
	else $api_key = null;

    //list_id
	if(isset($_GET['list_id'])) $list_id = decrypt_int($_GET['list_id']);
	else $list_id = null;

	// limit
	if(isset($_GET['limit'])) $limit = $_GET['limit'];
	else $limit = 100;
	if ($limit > 1000)  $limit = 1000;

	//offset
	if(isset($_GET['offset'])) $offset = $_GET['offset'];
	else $offset = 0;
	//-----------------------------------------------------------//
	
	//----------------------- VERIFICATION ----------------------//
    //Core data
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

    if($list_id==null)
    {
		echo json_encode(array('success' => false, 'error' => $error_passed[0]));
		exit;
	}
    else 
    {
        $q = 'SELECT id FROM lists WHERE id = '.$list_id;
		$r = mysqli_query($mysqli, $q);
		if (mysqli_num_rows($r) == 0) 
		{
			echo json_encode(array('success' => false, 'error' => $error_passed[1]));
			exit;
		}
    }
	//-----------------------------------------------------------//

	//-------------------------- QUERY --------------------------//
	$q = <<<QUERY_STR
	SELECT
		s.id,
		s.email
	FROM
		subscribers AS s
	WHERE
		s.unsubscribed = 0 AND
		s.list = {$list_id}
	LIMIT {$offset}, {$limit};
	QUERY_STR;

	$q_count = <<<QUERY_STR
	SELECT
		COUNT(s.id) AS count
	FROM
		subscribers AS s
	WHERE
		s.unsubscribed = 0 AND
		s.list = {$list_id};
	QUERY_STR;
	
	$r_count = mysqli_query($mysqli, $q_count);

	$output = array(
		"success" => true,
		"data" => array(
			"total" => 0,
			"subscribers" => array()
		)
	);

	if($r_count && mysqli_num_rows($r_count) > 0) 
	{
		while($row = mysqli_fetch_array($r_count))
		{
			$total_count = $row["count"];
			$output["data"]["total"] = $total_count;
			break;
		}
	}

	if($output["data"]["total"] == 0) {
		echo json_encode($output);
		exit;
	}

	$r = mysqli_query($mysqli, $q);

	if($r && mysqli_num_rows($r) > 0) 
	{
		while($row = mysqli_fetch_array($r))
		{
			$id = $row["id"];
			$email = $row["email"];
			$output["data"]["subscribers"][] = array("id" => $id, "email" => $email);
		}
	}

	echo json_encode($output);
	//-----------------------------------------------------------//
?>