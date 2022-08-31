<?php include('../_connect.php');?>
<?php include('../../includes/helpers/short.php');?>
<?php

    //-------------------------- ERRORS -------------------------//
	$error_core = array('API key not passed', 'Invalid API key');
	$error_passed = array('List ID not passed', 'No IDs passed', "No action passed", "Invalid action");
	//-----------------------------------------------------------//
    
    //------------------------- ACTIONS -------------------------//
    $action_unsubscribe = "unsubscribe";
    $action_delete = "delete";
    $valid_actions = array($action_unsubscribe, $action_delete);
	//-----------------------------------------------------------//

    //--------------------------- GET --------------------------//
	//api_key
	if(isset($_GET['api_key'])) $api_key = mysqli_real_escape_string($mysqli, $_GET['api_key']);
	else $api_key = null;

    //list_id
	if(isset($_GET['list_id'])) $list_id = decrypt_int($_GET['list_id']);
	else $list_id = null;
	//-----------------------------------------------------------//
    
    //--------------------------- POST --------------------------//
    $json = file_get_contents('php://input');
    $post_data = json_decode($json, true);
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

    if($list_id==null)
    {
        echo json_encode(array('success' => false, 'error' => $error_passed[0]));
        exit;
    }

    if (json_last_error() != JSON_ERROR_NONE) 
    {
        echo json_encode(array('succes' => false, 'error' => json_last_error_msg()));
        exit;
    }

    if(!array_key_exists('ids', $post_data) || count($post_data["ids"]) == 0) 
    {
        echo json_encode(array('success' => false, 'error' => $error_passed[1]));
        exit;
    }

    if(!array_key_exists("action", $post_data))
    {
        echo json_encode(array('success' => false, 'error' => $error_passed[2]));
        exit;
    }

    if(!in_array($post_data["action"], $valid_actions)) 
    {
        echo json_encode(array('success' => false, 'error' => $error_passed[3]));
        exit;
    }
	//-----------------------------------------------------------//

    //-------------------------- QUERY --------------------------//
    $ids = implode(",", $post_data["ids"]);

    if($post_data["action"] == $action_unsubscribe) {
        $q = <<<QUERY_STR
        UPDATE subscribers
        SET unsubscribed = 1
        WHERE
            list = {$list_id} AND
            id IN ({$ids});
        QUERY_STR;

    } else {
        $q = <<<QUERY_STR
        DELETE FROM subscribers
        WHERE
            list = {$list_id} AND
            id IN ({$ids});
        QUERY_STR;
    }

    $r = mysqli_query($mysqli, $q);
    if ($r) {
        echo json_encode(array('success' => true ));
        exit;
    } else {
        echo json_encode(array('success' => false));
        exit;
    }
    //-----------------------------------------------------------//