<?php
require_once 'api.class.php';
require_once('models.class.php');

class MyAPI extends API
{
//private $db = new stdClass;
protected $dbdb  = "DEVDB";
protected $dbusr = "db2inst1";
protected $dbpsw = "19Mtgartw86";
protected $dbsch = "DEVDB";
public 	  $dbh;

    public function __construct($request, $origin) {
        parent::__construct($request);

        if ($this->apiKeyRequired) {
			$APIKey = new APIKey();
			
			if (!array_key_exists('apiKey', $this->request)) {
				throw new Exception('No API Key provided');
			} else if (!$APIKey->verifyKey($this->request['apiKey'], $origin)) {
				throw new Exception('Invalid API Key');
			} else if (array_key_exists('token', $this->request) &&
				 !$User->get('token', $this->request['token'])) {

				throw new Exception('Invalid User Token');
			}
		}
		
		//make delay for debug and testing purpose TODO: remove this in production!
		if (array_key_exists('debug', $this->request)) {
			sleep(rand(0,4));
		}
		
		try {
			$dbh = new PDO('odbc:host=127.0.0.1;port=50000;DATABASE=DEVDB;DRIVER=DB2;', $this->dbusr, $this->dbpsw, array(
				PDO::ATTR_PERSISTENT => TRUE, 
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)
			);
			$dbh->exec("SET SCHEMA DEVDB");
			
			$this->dbh = $dbh;
		} catch (PDOException $e) {
			throw new Exception("DB error: " . $e->getMessage());
			die();
		}
    }

/* --------------------------
#  GETLIST 
	verb:
		category
----------------------------- */
     protected function categories() {
        if ($this->method == 'GET') {
	
			if (!empty($this->verb)) {
				switch($this->verb) {
					case 'list':
						$sql = "WITH failed AS ("
								."		SELECT COUNT(*) as c, CATEGORY_NAME FROM TEST_RESULT WHERE TEST_RESULT=0 GROUP BY CATEGORY_NAME"
								."), success AS ("
								."		SELECT COUNT(*) as c, CATEGORY_NAME FROM TEST_RESULT WHERE TEST_RESULT=1 GROUP BY CATEGORY_NAME"
								."), categories AS ("
								."		SELECT MAX(TEST_DATE) as lastdate, CATEGORY_NAME FROM DEVICE GROUP BY CATEGORY_NAME"
								.")"
								." SELECT c1.CATEGORY_NAME, c1.lastdate, "
								."	   cast(COALESCE(c2.c,0) as int) as failed,"
								."	   cast(COALESCE(c3.c,0) as int) as success,"
								."	   cast(COALESCE(c2.c,0) as int) + cast(COALESCE(c3.c,0) as int) as total"
								." FROM categories c1"
								." LEFT JOIN (SELECT * FROM failed) c2 ON c1.CATEGORY_NAME=c2.CATEGORY_NAME"
								." LEFT JOIN (SELECT * FROM success) c3 ON c1.CATEGORY_NAME=c3.CATEGORY_NAME";
						break;
					case 'events':
						if (!empty($this->args[0])) {
							$sql = "SELECT "
									." TEST_DATE, DEVICE_NAME, TEST_RESULT, TEST_INTERVAL, CATEGORY_NAME, ENABLED"
									//." , MANUAL_TEST_RESULT, FAILING_STEP, DEVICE_ADDRESS"
									." FROM TEST_RESULT "
									." WHERE "
									." CATEGORY_NAME = ?"
									." ORDER BY TEST_DATE DESC";
						} else {
						}
						break;
					default:
						$sql = "WITH failed AS ("
								."		SELECT COUNT(*) as c, CATEGORY_NAME FROM TEST_RESULT WHERE TEST_RESULT=0 GROUP BY CATEGORY_NAME"
								."), success AS ("
								."		SELECT COUNT(*) as c, CATEGORY_NAME FROM TEST_RESULT WHERE TEST_RESULT=1 GROUP BY CATEGORY_NAME"
								."), categories AS ("
								."		SELECT MAX(TEST_DATE) as lastdate, CATEGORY_NAME FROM DEVICE GROUP BY CATEGORY_NAME"
								.")"
								." SELECT c1.CATEGORY_NAME, c1.lastdate, "
								."	   cast(COALESCE(c2.c,0) as int) as failed,"
								."	   cast(COALESCE(c3.c,0) as int) as success,"
								."	   cast(COALESCE(c2.c,0) as int) + cast(COALESCE(c3.c,0) as int) as total"
								." FROM categories c1"
								." LEFT JOIN (SELECT * FROM failed) c2 ON c1.CATEGORY_NAME=c2.CATEGORY_NAME"
								." LEFT JOIN (SELECT * FROM success) c3 ON c1.CATEGORY_NAME=c3.CATEGORY_NAME";
				}	
				
				$query = $this->dbh->prepare($sql);			
				$query->execute($this->args);
				$rows = $query->fetchAll(PDO::FETCH_OBJ);
				
				$response->response = "success";
				$response->message = "Records found!";	

				$response->result = $rows; //array_map('utf8_encode', $rows);				
			} else {
				$response->response = "error";
			}
			
            return $response;
        } else {
            return "Endpoint ".strtoupper($this->endpoint)." accepts GET requests";
        }
     }
	 
	 protected function test() {
        if ($this->method == 'GET') {
			$response = new stdClass;
			$response->status = "GET";
			$response->verb = $this->verb;
			$response->dane = $this->args;
            return $response;
        } else {
            return "Only accepts DELETE requests";
        }
     }
}
 ?>
