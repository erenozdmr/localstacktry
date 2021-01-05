<?php
	
	require_once(dirname(__FILE__).'/classes/S3FileOperations.php');
    
    $aws = new S3FileOperations;
    
    $result = $aws->get_from_s3('demo-bucket/example.jpg');
    print_r($result);


	$result = $aws->get_big_folder_in_s3('demo-bucket');
    print_r($result);
