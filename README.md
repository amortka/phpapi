phpapi
======
Simple PHP api built for AngularJS applications, few api methods already implemented.
Construction of url: 

```
http://hostname/api/{endpoint}/{verb}/{argument1}/{argument2}
```

API suports following methods
* GET
* POST
* PUT
* DELETE

they can be easily extended by adding new methods in switch bock:
```
  ...
  switch($this->method) {
  case 'DELETE':
```

all endpoints are functions which creates respond for particular verb
```
 protected function categories() {
  if ($this->method == 'GET') {
		if (!empty($this->verb)) {
			switch($this->verb) {
				case 'list':
				  $sql = ...
				  break;
				...
				
				$query = $this->dbh->prepare($sql);			
				$query->execute($this->args);
				$rows = $query->fetchAll(PDO::FETCH_OBJ);
				
				$response->response = "success";
				$response->result = $rows;	
```
