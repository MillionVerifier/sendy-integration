<?php include('../_connect.php'); ?>
<?php
    //-------------------------- ERRORS -------------------------//
    $error_core = array('API key not passed', 'Invalid API key');
    //-----------------------------------------------------------//
    
    //--------------------------- POST --------------------------//
	//api_key
	if(isset($_GET['api_key'])) $api_key = mysqli_real_escape_string($mysqli, $_GET['api_key']);
	else $api_key = null;
    
    //----------------------- VERIFICATION ----------------------//
    if($api_key==null)
	{  
        echo json_encode(array('success' => false, 'error' => $error_core[0]));
        echo $error_core[0];
		exit;
	}
	else if(!verify_api_key($api_key))
	{  
        echo json_encode(array('success' => false, 'error' => $error_core[1]));
        echo $error_core[1];
		exit;
	}
    //-----------------------------------------------------------//
    
    echo json_encode(array('success' => true));
?>