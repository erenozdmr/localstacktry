<?php

	//namespace image;
	require(dirname(dirname(__FILE__)).'/composer/vendor/autoload.php');
	
	use Aws\S3\S3Client;
	use Aws\S3\Exception\S3Exception;

	class S3FileOperations{

		private $s3;
		private $bucket;
		 
		function __construct(){	

			$this->s3 = S3Client::factory([
			    'credentials' => false,
			    'version' => 'latest',
			    'region'  => 'eu-central-1',
				'endpoint' => 'http://localhost:4572',
				'force_path_style'  => true
			]);
		
			$this->bucket = 'demo-bucket';
		}

		function __destruct(){}

		public function upload_to_s3($file_path, $destination_path){
			
			$pathinfo = pathinfo($file_path);
			$filename = $pathinfo['filename'];
			$ext = @$pathinfo['extension'];  
			$result = new \stdClass();

			try {
			    $result = $this->s3->putObject([
			        'Bucket' => $this->bucket,
			        'Key'    => $destination_path, //DESTINATION
			        'Body'   => fopen($file_path,'rb'), //SOURCE
			        'ContentDisposition' => 'attachment; filename="'.$filename.'"', //REAL FILE NAME
			        'Metadata' => ['filename'=>rawurlencode($filename)] //REAL FILE NAME
			    ]);

				$result->status = true;
				echo "başarılı";
			    return $result;

			} catch (S3Exception $e) {
			    $result->status = false;
			    $result->error = $e->getMessage();
			    return $result;
			}
		}

		public function get_from_s3($file_path){


			try {
			    $result = $this->s3->getObject([
			        'Bucket' => $this->bucket, //BUCKET NAME
			        'Key'    => $file_path //DESTINATION
			    ]);

			    ob_clean();

			    header("Content-Type: {$result['ContentType']}");
			    // comment if you want user to display the file instead of dowloading
			    // header("Content-Disposition: attachment; filename=".$result['Metadata']['filename']);
			    
			    echo $result['Body'];
			    return;
			} catch (S3Exception $e) {
				echo  $e;
			    return $e->getAwsErrorMessage();
			}

		}


		public function get_from_s3_as_string($file_path){

			//$filename
			 $x = new \stdClass();

			try {
			    $result = $this->s3->getObject([
			        'Bucket' => $this->bucket, //BUCKET NAME
			        'Key'    => $file_path //DESTINATION
			    ]);

			    $x->image_string = $result['Body'];
			    $x->status = true;
			    return $x;
			    
			} catch (S3Exception $e) {

				$x->status = false;
				$x->error = $e->getAwsErrorMessage();
			    return $x;
			}

		}

		public function file_exists_in_s3($file_path){

			try {
			    $result = $this->s3->doesObjectExist(
			    	$this->bucket, //BUCKET NAME
			        $file_path //DESTINATION
			    );

				return $result;
			    
			} catch (S3Exception $e) {

				return false;
			    
			}

		}

		public function get_folder_in_s3($file_path){

			try {
			    $result = $this->s3->getIterator('ListObjects', [
			        'Bucket' => $this->bucket, //BUCKET NAME
			        'Prefix' => $file_path //DESTINATION
			    ]);
				return $result;
			    
			} catch (S3Exception $e) {

				return false;
			    
			}

		}

		public function md5_from_s3($file_path){

			try {
			    $result = $this->s3->getObject([
			        'Bucket' => $this->bucket, //BUCKET NAME
			        'Key'    => $file_path //DESTINATION
			    ]);

			    return substr($result['ETag'], 1, -1);

			} catch (S3Exception $e) {
			    return $e->getAwsErrorMessage();
			}

		}

		public function get_bucket_name(){
			return $this->bucket;
		}

		public function get_big_folder_in_s3($file_path){

			try {
			   	
			   $pages = $this->s3->getPaginator('ListObjects', [
			        'Bucket' => $this->bucket,
			       	'Prefix' => $file_path, //DESTINATION
			    ]);

			    foreach ($pages as $page) {
			        foreach ($page['Contents'] as $object) {
			          //  echo $object['Key'] . PHP_EOL;
			            $result[] = $object['Key'];
			        }
			    }

			    return $result;
			    
			} catch (S3Exception $e) {
				echo $e;
				return false;
			    
			}
		}
	}

