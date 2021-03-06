<?php

require_once("sendmail.php");

$hostname = "localhost";
$username = "invoicemanager";
$password = "Maxipereyra!2";
$dbname = "invoicemanagement";


if (isset($_GET["GetCategories"])) {

	GetCategories();

} else if (isset($_POST["DeleteItem"])) {

	DeleteCategory($_POST["DeleteItem"]);

} else if (isset($_POST["AddItem"])) {

	AddCategory($_POST["AddItem"],$_POST["Tax"],$_POST["Expense"],$_POST["Total"]);

} else if (isset($_POST["FormValues"])) {

	SaveInvoice($_POST["FormValues"], $_POST["SaveOnly"], $_POST["InvoiceID"]);

} else if (isset($_POST["SettingsFormValues"])) {

	SaveSettings($_POST["SettingsFormValues"]);
	
} else if (isset($_GET["GetSettings"])) {

	GetSettingsJSON();

} else if (isset($_GET["GetInvoices"]) && isset($_GET["Command"])) {



    $date = "";
	if(isset($_GET["Date"])){
		
		$date = $_GET["Date"];
		
	}
	GetInvoices($_GET["Command"],$date);

} else if (isset($_POST["ResendInvoice"]) && isset($_POST["InvoiceID"])) {

	ResendInvoice($_POST["InvoiceID"]);

} else if (isset($_GET["GetInvoice"]) && isset($_GET["InvoiceID"])) {

	GetInvoice($_GET["InvoiceID"], "true");

} else if (isset($_GET["GetInvoiceItems"]) && isset($_GET["InvoiceID"])) {

	GetLineItems($_GET["InvoiceID"], "true");

} else if (isset($_POST["PayInvoice"]) && isset($_POST["InvoiceID"])){
	
	PayInvoice($_POST["InvoiceID"]);
}
else if (isset($_GET["GetHomPageData"])){
	
	$date = "";
	if(isset($_GET["Date"])){
		
		$date = $_GET["Date"];
		
	}
	
	GetHomePageData($date);
	
}else if (isset($_GET["GetItemSettings"])){
	
	
	GetItemSettings($_GET["GetItemSettings"]);
	
}else if (isset($_GET["GetMonthsData"])){
		
				
	GetMonthsData();
		
	
}else if (isset($_GET["GetMonthsDataTransactions"])){
    
    GetMonthsDataTransactions();
    
}
 else if (isset($_GET["GetCustomer"])){
	
	
	GetCustomer($_GET["GetCustomer"]);
	
}else if (isset($_GET["GetCustomerList"])){
	
	GetCustomerList();

}else if (isset($_GET["GetCustomersFull"])){
    
    
    GetCustomerListFull();
    
}else if (isset($_POST["UpdateCustomer"])){
    
    UpdateCustomer($_POST["CustomerID"],$_POST["CustomerName"],$_POST["CustomerEmail"],$_POST["CustomerAddress"],$_POST["CustomerCityState"],$_POST["CustomerZip"]);
    
}else if (isset($_GET["GetCustomerInvoicesByID"])){
    
    
    GetCustomerInvoicesByID($_GET["CustomerID"]);
    
}else if (isset($_POST["DeleteInvoice"])){
    
  DeleteInvoice($_POST["InvoiceID"]);
    
    
}

function DeleteInvoice($invoiceID){
    
    mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
    
    $query = "DELETE from `invoiceitem` where InvoiceID=".$invoiceID;
    $result = mysql_query($query);
    
    $query = "DELETE from `invoices` where ID=".$invoiceID;
    $result = mysql_query($query);
    
    
}

function GetCustomerInvoicesByID($customerid){
    
    mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
    
    $query = "select SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount,Paid,PaidDate,EmailSent,i.InvoiceTitle as Title, i.ID, DateTime, ExpirationDate,CustomerID,case when ExpirationDate < DateTime and Paid=0 then 'Expired' when Paid = 1 then 'Paid' when EmailSent = 1 and Paid = 0 then 'Invoice Sent' when EmailSent=0 then 'Estimate Created' else 'OK' end as status from `invoices` i inner join `invoiceitem` it on it.InvoiceID = i.ID  where i.CustomerID=".$customerid." GROUP BY i.ID order by Datetime desc, i.id desc";

	$result = mysql_query($query);
    
    
    $stack = array();  
    
	while ($row = mysql_fetch_array($result)) { 
       
        $datecreated = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateTime']);
		$dateexpired = DateTime::createFromFormat('Y-m-d H:i:s', $row['ExpirationDate']);
        
        $datepaid="";
        if ($row["PaidDate"]!="0000-00-00 00:00:00"){
		
        $datepaid = DateTime::createFromFormat('Y-m-d H:i:s', $row["PaidDate"]);
        $datepaid = $datepaid->format('m/d/y');
        
        }
		$datetime1 = date_create($row["ExpirationDate"]);
		$datetime2 = date_create($row["DateTime"]);

		$interval = date_diff($datetime1, $datetime2);

		$arr = array(
		
		"paiddate"=> $datepaid,
		"paid" =>$row["Paid"],
	    "createddate" => $datecreated-> format('m/d/y'),
	    "EmailSent" => $row["EmailSent"], 
	    "expirationdate" => $dateexpired-> format('m/d/y'), 
	    "invoiceID" => $row["ID"],
	    "totalamount"=>$row["TotalAmount"],
        "status"=>$row["status"],
		"title"=>$row["Title"],
	    "expirationdatecount" => $interval -> format("%a"));
        
        array_push($stack, $arr);

	}

    echo(json_encode($stack));  

}

function UpdateCustomer($customerid, $customername, $customeremail, $customeraddress,$customercitystate,$customerzip){
    
    mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
    
    $query = "UPDATE `customer` set `Name`='".$customername."',`Email`='".$customeremail."',`StreetAddress`='".$customeraddress."',`CityState`='".$customercitystate."',`ZipCode`='".$customerzip."' where ID=".$customerid; 
    
    mysql_query($query);

    
}

function GetCustomerListFull(){
    
    
   	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
	$query = "select * from customer order by Name ASC";
	
	$result = mysql_query($query);
    
    $stack = array();    
    
    while ($row = mysql_fetch_array($result)) {
			
		
		$arr = array(
		
		"id"=>$row["ID"],
		"name"=>$row["Name"],
        "email"=>$row["Email"],
        "StreetAddress"=>$row["StreetAddress"],
        "citystate"=>$row["CityState"],
        "zipcode"=>$row["ZipCode"]
		);

		array_push($stack, $arr);
		
	}
	
	echo(json_encode($stack));            
    
}

function GetCustomerList(){
	
	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
	$query = "select ID,Name from customer order by name asc";
	
	$result = mysql_query($query);
	
	$stack = array();
	
	while ($row = mysql_fetch_array($result)) {
			
		
		$arr = array(
		
		"id"=>$row["ID"],
		"name"=>$row["Name"]
		);

		array_push($stack, $arr);
		
	}
	
	echo(json_encode($stack));
	
}

function GetCustomer($id){
	
	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
	$query = "select * from customer where ID=".$id;
	
	$result = mysql_query($query);
	
	$arr = array(
		
		"clientname" => mysql_result($result,0,"Name"),
		"clientemail" => mysql_result($result,0,"Email"),
		"clientstreetaddress" => mysql_result($result,0,"StreetAddress"),
		"clientcitystate" => mysql_result($result,0,"CityState"), 
		"clientzipcode" => mysql_result($result,0,"ZipCode"),
		"id" => mysql_result($result,0,"ID")
		
		);
	
	
	echo(json_encode($arr));
	
}

function GetHomePageData($date){
					
				
	if ($date!=""){
			
		$date = split(',', $date);
		
	}else{
			
		$date[0] = date("n");
		
		$date[1] = date("Y");
		
	}
		
	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
		
		
	$query = "select SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount from `invoiceitem` it inner join invoices i on i.ID = it.InvoiceID where Paid = 0 and Expense=0";
	
	$result = mysql_query($query);
	
	$totalunpaid = mysql_result($result,0);
	
	
	$query = "select SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount from `invoiceitem` it inner join invoices i on i.ID = it.InvoiceID where Paid = 1 AND $date[0] = MONTH(PaidDate) AND $date[1] = YEAR(PaidDate)";
	
	$result = mysql_query($query);
	
	$totalpaid = mysql_result($result,0);
	
	//estimates
	$query = "select COUNT(distinct i.ID) as count,SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount from `invoiceitem` it inner join invoices i on i.ID = it.InvoiceID where Paid = 0 AND Expense = 0 AND EmailSent=0 AND ExpirationDate > NOW()";
	
	$result = mysql_query($query);
	
	$totalestimates = mysql_result($result,0,"TotalAmount");

	$estimatecount = mysql_result($result,0,"count");
	
	//invoice
	$query = "select COUNT(distinct i.ID) as count,SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount from `invoiceitem` it inner join invoices i on i.ID = it.InvoiceID where Paid = 0 AND Expense = 0 AND EmailSent=1 AND ExpirationDate > NOW()";
	
	$result = mysql_query($query);
	
	$totalinvoice = mysql_result($result,0,"TotalAmount");
	$invoicecount = mysql_result($result,0,"count");
	
	//expired
	$query = "select COUNT(distinct i.ID) as count,SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount from `invoiceitem` it inner join invoices i on i.ID = it.InvoiceID where Paid = 0 AND Expense = 0 AND EmailSent=1 AND ExpirationDate < NOW() ";
	
	$result = mysql_query($query);
	
	$totalexpired = mysql_result($result,0,"TotalAmount");
	$expiredcount = mysql_result($result,0,"count");
	
	
	$query = "select SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount from `invoiceitem` it inner join invoices i on i.ID = it.InvoiceID where Paid = 1 AND Expense = 1 AND $date[0] = MONTH(PaidDate) AND $date[1] = YEAR(PaidDate)";
	
	$result = mysql_query($query);
	
	$totalexpense = mysql_result($result,0);
	
	
	
	
	$arr = array(
		
		"totalunpaid" => $totalunpaid==null?"0.00":$totalunpaid,
		"totalpaid" => $totalpaid==null?"0.00":$totalpaid,
		"totalestimates" => $totalestimates==null?"0.00":$totalestimates, 
		"invoicetotal" => $totalinvoice==null?"0.00":$totalinvoice, 
		"totalexpired" => $totalexpired==null?"0.00":$totalexpired,
		"totalexpense"=>$totalexpense==null?"0.00":$totalexpense,
		"estimatecount"=>$estimatecount,
		"invoicecount"=>$invoicecount,
		"expiredcount"=>$expiredcount
		);
	
	
	echo(json_encode($arr));
	
	
}

function GetMonthsData(){
	
	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
	
	
	$query = "select distinct MONTH(PaidDate) as val,MONTHNAME(STR_TO_DATE(MONTH(PaidDate), '%m')) as text, YEAR(PaidDate) as year from `invoices` where paiddate <> 0 order by paiddate desc";
	
	
	$result = mysql_query($query);
	$stack = array();
	while ($row = mysql_fetch_array($result)) {
			
		
		$arr = array(
		
		"monthvalue"=>$row["val"],
		"monthname"=>$row["text"],
		"year"=>$row["year"]
		);

		array_push($stack, $arr);
		
	}
	
	echo(json_encode($stack));
	
}

function GetMonthsDataTransactions(){
	
	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
	
	
	$query = "select distinct MONTH(DateTime) as val,MONTHNAME(STR_TO_DATE(MONTH(DateTime), '%m')) as text, YEAR(DateTime) as year from `invoices` order by DateTime desc";
	
	
	$result = mysql_query($query);
	$stack = array();
	while ($row = mysql_fetch_array($result)) {
			
		
		$arr = array(
		
		"monthvalue"=>$row["val"],
		"monthname"=>$row["text"],
		"year"=>$row["year"]
		);

		array_push($stack, $arr);
		
	}
	
	echo(json_encode($stack));
	
}

function GetInvoices($sentorsavedExpired,$date) {
    
    if ($date!=""){
			
		$date = split(',', $date);
		
	}else{
			
		$date[0] = date("n");
		
		$date[1] = date("Y");
		
	}
	
	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);

	if ($sentorsavedExpired == "sent") {

		$query = "select invoices.InvoiceTitle,invoices.Paid,invoices.PaidDate,invoices.ID,Name as ClientName, StreetAddress as ClientStreetAddress,CONCAT(CityState,' ',ZipCode) as ClientCityStateZip, SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount, Email, DateTime, ExpirationDate, EmailSent from `invoices` invoices inner join `invoiceitem` item on item.InvoiceID = invoices.ID inner join `customer` c on c.ID = invoices.CustomerID where EmailSent=1 AND ExpirationDate>NOW() and Paid=0 AND $date[0] = MONTH(DateTime) AND $date[1] = YEAR(DateTime) GROUP BY ID ORDER BY DateTime DESC, invoices.ID DESC";

	} else if ($sentorsavedExpired=="saved") {

		$query = "select invoices.InvoiceTitle,invoices.Paid,invoices.PaidDate,invoices.ID,Name as ClientName, StreetAddress as ClientStreetAddress,CONCAT(CityState,' ',ZipCode) as ClientCityStateZip, SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount, Email, DateTime, ExpirationDate, EmailSent from `invoices` invoices inner join `invoiceitem` item on item.InvoiceID = invoices.ID inner join `customer` c on c.ID = invoices.CustomerID where EmailSent=0 AND Paid=0 AND $date[0] = MONTH(DateTime) AND $date[1] = YEAR(DateTime) GROUP BY ID ORDER BY DateTime DESC, ID DESC";

	}else if ($sentorsavedExpired=="expired"){
			
		$query = "select invoices.InvoiceTitle,invoices.Paid,invoices.PaidDate,invoices.ID,Name as ClientName, StreetAddress as ClientStreetAddress,CONCAT(CityState,' ',ZipCode) as ClientCityStateZip, SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount, Email, DateTime, ExpirationDate, EmailSent from `invoices` invoices inner join `invoiceitem` item on item.InvoiceID = invoices.ID inner join `customer` c on c.ID = invoices.CustomerID where ExpirationDate <= NOW() and Paid=0 and EmailSent=1 AND $date[0] = MONTH(DateTime) AND $date[1] = YEAR(DateTime) GROUP BY ID ORDER BY DateTime DESC, invoices.ID DESC";
		
	}else if ($sentorsavedExpired=="paid"){
			
		$query = "select invoices.InvoiceTitle,invoices.Paid,invoices.PaidDate,invoices.ID,Name as ClientName, StreetAddress as ClientStreetAddress,CONCAT(CityState,' ',ZipCode) as ClientCityStateZip, SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount, Email, DateTime, ExpirationDate, EmailSent from `invoices` invoices inner join `invoiceitem` item on item.InvoiceID = invoices.ID inner join `customer` c on c.ID = invoices.CustomerID where Paid=1 AND $date[0] = MONTH(DateTime) AND $date[1] = YEAR(DateTime) GROUP BY ID ORDER BY DateTime DESC, invoices.ID DESC";
		
	}
		
	$result = mysql_query($query);
	$stack = array();
	while ($row = mysql_fetch_array($result)) {

		$datecreated = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateTime']);
		$dateexpired = DateTime::createFromFormat('Y-m-d H:i:s', $row['ExpirationDate']);
		$datepaid = DateTime::createFromFormat('Y-m-d H:i:s', $row["PaidDate"]);
		
		$arr = array(
		
		"clientname" => $row["ClientName"],
		"clientstreetaddress" => $row["ClientStreetAddress"],
		"clientcitystatezip" => $row["ClientCityStateZip"], 
		"invoicetotal" => $row["TotalAmount"], 
		"clientemail" => $row["Email"], 
		"createddate" => $datecreated -> format('m/d/y'), 
		"expirationdate" => $dateexpired -> format('m/d/y'), 
		"invoiceID" => $row["ID"], 
		"emailsent" => $row["EmailSent"], 
		"paiddate" => $datepaid -> format('m/d/y'),
		"paid"=>$row["Paid"],
		"title"=>$row["InvoiceTitle"]
		);

		array_push($stack, $arr);
	}

	echo(json_encode($stack));

}

function GetInvoice($invoiceID, $returnjason) {

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
	$query = "select SUM(ROUND(IF(Taxable = true, (ItemTotal*ItemQuantity)+((ItemTotal*ItemQuantity)*(Rate/100)), ItemTotal*ItemQuantity),2)) as TotalAmount,Paid,PaidDate,EmailSent, i.InvoiceTitle, i.ID,Name, StreetAddress,CityState as ClientCityState,ZipCode,Email, DateTime, ExpirationDate,CustomerID from `invoices` i inner join `invoiceitem` it on it.InvoiceID = i.ID inner join `customer` c on c.ID = i.CustomerID  where i.ID=".$invoiceID." GROUP BY i.ID";

	$result = mysql_query($query);
    
    
	while ($row = mysql_fetch_array($result)) {

		$datetime1 = date_create($row["ExpirationDate"]);
		$datetime2 = date_create($row["DateTime"]);

		$interval = date_diff($datetime1, $datetime2);

		$arr = array(
		
		"clientname" => $row["Name"],
		"paiddate"=> $row["PaidDate"],
		"paid" =>$row["Paid"],
	    "clientstreetaddress" => $row["StreetAddress"],
	    "clientcitystate" => $row["ClientCityState"],
	    "clientzip" => $row["ZipCode"],
	    "clientemail" => $row["Email"],
	    "createddate" => $row["DateTime"],
	    "EmailSent" => $row["EmailSent"], 
	    "expirationdate" => $row["ExpirationDate"], 
	    "invoiceID" => $row["ID"],
	    "totalamount"=>$row["TotalAmount"],
		"customerid"=>$row["CustomerID"],
	    "expirationdatecount" => $interval -> format("%a"),
		"invoicetitle" => $row["InvoiceTitle"]);

	}

	if ($returnjason == "true") {

		echo(json_encode($arr));

	} else {

		return $arr;
	}
}

function GetLineItems($invoiceID, $returnjason) {

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);

	$query = "select * from `invoiceitem` where InvoiceID=" . $invoiceID;

	$result = mysql_query($query);
	$stack = array();
	while ($row = mysql_fetch_array($result)) {

		$arr = array("itemname" => $row["ItemName"], "itemdescription" => $row["ItemDescription"], "itemquantity" => $row["ItemQuantity"], "itemtotal" => $row["ItemTotal"], "itemtaxable" => $row["Taxable"], "itemrate" => $row["Rate"], "itemid" => $row["ID"], "itemexpense"=>$row["Expense"]);

		array_push($stack, $arr);
	}

	if ($returnjason == "true") {

		echo(json_encode($stack));

	}

	return $stack;

}

function DeleteCategory($category) {

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
	$query = "Delete From `invoicecategories` where Category='" . $category . "'";
	mysql_query($query);

}

function AddCategory($category,$tax,$expense,$totalamount) {
	if ($category != "") {
		mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
		mysql_select_db($GLOBALS['dbname']);
		$query = "INSERT INTO `invoicecategories`(`Category`,`Taxable`,`Expense`,`Amount`) VALUES ('" . $category . "',".$tax.",".$expense.",$totalamount)";
		mysql_query($query);
	}

}

function GetItemSettings($category) {
	
	if ($category != "") {
		mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
		mysql_select_db($GLOBALS['dbname']);
		$query = "SELECT Amount,Taxable,Expense from `invoicecategories` where Category='" . $category . "'";
		$result = mysql_query($query);
		
		
		$taxable = mysql_result($result,0,"Taxable");
		$expense = mysql_result($result,0,"Expense");
		$total = mysql_result($result,0,"Amount");
		
		$arr = array(
		
		"taxable" => $taxable,
		"expense" => $expense,
		"total"=> $total
		);
	
	
		echo(json_encode($arr));
		
		
		
	}

}



function GetCategories() {

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);

	$query = "select Category from invoicecategories order by Category asc";
	$result = mysql_query($query);

	$categories = array();

	while ($row = mysql_fetch_array($result)) {

		array_push($categories, $row["Category"]);

	}

	echo(json_encode($categories));

}

function GetSettingsJSON() {

	echo(json_encode(GetSettingsArray()));

}

function GetSettingsArray() {

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);

	$query = "SELECT * FROM `invoiceconfig`";
	$result = mysql_query($query);

	while ($row = mysql_fetch_array($result)) {

		$arr = array("companyname" => $row["InvoiceCompanyName"], "streetaddress" => $row["InvoiceStreetAddress"], "citystate" => $row["InvoiceCityState"], "zipcode" => $row["InvoiceZipCode"], "taxrate" => $row["InvoiceTaxRate"], "sendclientauto" => $row["InvoiceSendClientEmail"], "sendcompanyemailauto"=> $row["InvoiceSendCompanyEmail"], "forwardemail" => $row["InvoiceSendToEmail"], "linkeddomain" => $row["LinkedDomain"], "sendemailfrom" => $row["SendEmailFrom"]);
	}

	return $arr;

}

function SaveInvoice($formvalues, $saveonly, $PassedInvoice) {

    //if save only it is creating an estimate, otherwise it is an invoice or reciept

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);

	$values = array();

	parse_str($formvalues, $values);

	$ExpirationDate = $values["expirationdays"];
	$ClientName = $values["clientname"];
	$InvoiceTitle = $values["clienttitle"];
	$ClientName = str_replace("'", "''", $ClientName);
	$ClientStreetAddress = $values["clientstreetaddress"];
	$ClientStreetAddress = str_replace("'", "''", $ClientStreetAddress);
	$ClientCityState = str_replace("'", "''", $values["clientcitystate"]);
	$ClientCityState = split(',', $values["clientcitystate"]);
	$ClientCity = trim($ClientCityState[0]);
	$ClientState = trim($ClientCityState[1]);
	$ClientEmail = $values["clientemail"];
	$ClientZip = $values["clientzipcode"];
	$ClientExprirationDays = $values["expirationdays"];
	$CustomerID = $values["customerid"];

    
		//check if id exist insert if it doesn't
		$query = "select ID from customer where ID=".$CustomerID;
		$result = mysql_query($query);
		
		if(mysql_num_rows($result)){
			
			
			//already exists don't insert
		
		}else{
			
		//doesn't exist insert new customer
		$query = 'INSERT INTO `customer` (`Name`,`Email`,`StreetAddress`,`CityState`,`ZipCode`) values ("'.$ClientName.'","'.$ClientEmail.'","'.$ClientStreetAddress.'","'.$ClientCity.", ".$ClientState.'","'.$ClientZip.'")';
		mysql_query($query);
		$CustomerID = mysql_insert_id();
			
		}
		
	

	$settings = GetSettingsArray();

	$query = "";
	
	$newinsert = false;

	if ($PassedInvoice == "") {
	
		//invoice doesn't exist
		if ($saveonly == "true") {
			$query = "INSERT INTO `invoices`(`DateTime`,`ExpirationDate`,`CustomerID`,`InvoiceTitle`)" . " VALUES (NOW(),DATE_ADD(NOW(),INTERVAL " . $ClientExprirationDays . " DAY),".$CustomerID.",'".$InvoiceTitle."')";

		} else if ($saveonly != "true") {

			$query = "INSERT INTO `invoices`(`DateTime`,`ExpirationDate`,`CustomerID`,`EmailSent`,`InvoiceTitle`)" . " VALUES (NOW(),DATE_ADD(NOW(),INTERVAL " . $ClientExprirationDays . " DAY),".$CustomerID.",1,'".$InvoiceTitle."')";

		}
        

        mysql_query($query);
		$invoiceid = mysql_insert_id();
		$newinsert = true;
		
	} else {

		//existing invoice

		if ($saveonly == "true") {

			$query = "UPDATE `invoices`" . " SET `DateTime`=NOW(),`ExpirationDate`=DATE_ADD(NOW(),INTERVAL " . $ClientExprirationDays . " DAY), `InvoiceTitle`='".$InvoiceTitle."' where `ID`=" . $PassedInvoice;

		} else if ($saveonly != "true") {

			$query = "UPDATE `invoices`" . " SET `DateTime`=NOW(),`ExpirationDate`=DATE_ADD(NOW(),INTERVAL " . $ClientExprirationDays . " DAY),`EmailSent`=1 , `InvoiceTitle`='".$InvoiceTitle."' where `ID`=" . $PassedInvoice;

		}
		
		mysql_query($query);
		$invoiceid = $PassedInvoice;
		$newinsert = false;

	}



	//find ids that need to be deleted
	$idstodelete = array_diff(GetItemsID($invoiceid), $values["itemid"]);

	if (!empty($idstodelete)) {

		foreach ($idstodelete as &$id) {

			$query = "DELETE FROM `invoiceitem` WHERE ID=" . $id;
			mysql_query($query);

		}

	}
	
	for ($i = 0; $i < count($values["itemname"]); $i++) {
		
		$taxable = 0;
		$expense = 0;

		if ($values["tax"][$i] == "1") {

			$taxable = 1;

		}
		
		if ($values["expense"][$i] == "1") {

			$expense = 1;

		}
		
		
		if ($values["itemid"][$i] == "" || $newinsert=="true") {
			
			$query = "INSERT INTO `invoiceitem` (`InvoiceID`, `ItemName`, `ItemDescription`, `ItemQuantity`, `ItemTotal`, `Taxable`,`Rate`,`Expense`) " . "VALUES (" . $invoiceid . ",'" . $values["itemname"][$i] . "','" . $values["description"][$i] . "','" . $values["quantity"][$i] . "','" . $values["price"][$i] . "'," . $taxable . "," . $values["taxrate"] . ",". $expense .")";

		} else {
		
			$query = "UPDATE `invoiceitem` " . "SET `Expense` = ".$expense." , `ItemName`='" . $values["itemname"][$i] . "',`ItemDescription`='" . $values["description"][$i] . "',`ItemQuantity`='" . $values["quantity"][$i] . "',`ItemTotal`='" . $values["price"][$i] . "',`Taxable`=" . $taxable . ",`Rate`=" . $values["taxrate"] . " where `InvoiceID`=" . $PassedInvoice . " AND `ID`=" . $values["itemid"][$i];

		}

		mysql_query($query);

	}
	
	
	$invoice = GetInvoice($invoiceid,false);



	if ($saveonly != "true") {

		if ($settings["sendclientauto"]) {
				
			
			if ($invoice["EmailSent"]=="1" && $invoice["paid"]=="1"){
					
			sendMail($ClientEmail, $settings["companyname"], $invoiceid, $settings["linkeddomain"], $settings["sendemailfrom"],"a receipt",$invoice["totalamount"]);
				
			}else if ($invoice["EmailSent"]=="1" && $invoice["paid"]=="0"){
				
			sendMail($ClientEmail, $settings["companyname"], $invoiceid, $settings["linkeddomain"], $settings["sendemailfrom"],"an invoice",$invoice["totalamount"]);
			
			}else{
						sendMail($ClientEmail, $settings["companyname"], $invoiceid, $settings["linkeddomain"], $settings["sendemailfrom"],"an estimate",$invoice["totalamount"]);
					
			}
		}

		if ($settings["forwardemail"] != "") {

			if ($invoice["EmailSent"]=="1" && $invoice["paid"]=="1"){
					
			sendMail($settings["forwardemail"], $settings["companyname"], $invoiceid, $settings["linkeddomain"], $settings["sendemailfrom"],"a receipt",$invoice["totalamount"]);
				
			}else if ($invoice["EmailSent"]=="1" && $invoice["paid"]=="0"){
				
			sendMail($settings["forwardemail"], $settings["companyname"], $invoiceid, $settings["linkeddomain"], $settings["sendemailfrom"],"an invoice",$invoice["totalamount"]);
			
			}else{
						sendMail($settings["forwardemail"], $settings["companyname"], $invoiceid, $settings["linkeddomain"], $settings["sendemailfrom"],"an estimate",$invoice["totalamount"]);
					
			}

		}
	}
}

function SaveSettings($formvalues) {

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);

	$values = array();

	parse_str($formvalues, $values);

	$CompanyName = ($values["companyname"] == " " ? "" : $values["companyname"]);

	$StreetAddress = ($values["streetaddress"] == "" ? "" : $values["streetaddress"]);
	$CityState = ($values["citystate"] == "" ? "" : $values["citystate"]);

	$ZipCode = ($values["zipcode"] == "" ? "" : $values["zipcode"]);

	$TaxRate = ($values["taxrate"] == "" ? 0.00 : $values["taxrate"]);
	$DocumentTitle = ($values["doctitle"] == "" ? "" : $values["doctitle"]);
	$AutoSendToClient = $values["sendclientauto"];

	if ($AutoSendToClient == 0) {

		$AutoSendToClient = 0;

	}else if ($AutoSendToClient == 1){
		
		$AutoSendToClient = 1;
	}

	$CustomEmail = $values["sendcompanyauto"];

	if ($CustomEmail != "") {

		$SendCompanyEmail = 1;

	} else {

		$SendCompanyEmail = 0;

	}

	$query = "UPDATE `invoiceconfig` SET `InvoiceSendCompanyEmail`=" . $SendCompanyEmail;
	mysql_query($query);
	$query = "UPDATE `invoiceconfig` SET `InvoiceTaxRate`=" . $TaxRate;
	mysql_query($query);
	$query = "UPDATE `invoiceconfig` SET `InvoiceCompanyName`='" . $CompanyName . "'";
	mysql_query($query);
	$query = "UPDATE `invoiceconfig` SET `InvoiceStreetAddress`='" . $StreetAddress . "'";
	mysql_query($query);
	$query = "UPDATE `invoiceconfig` SET `InvoiceCityState`='" . $CityState . "'";
	mysql_query($query);
	$query = "UPDATE `invoiceconfig` SET `InvoiceZipCode`='" . $ZipCode . "'";
	mysql_query($query);
	$query = "UPDATE `invoiceconfig` SET `InvoiceSendClientEmail`=" . $AutoSendToClient;
	mysql_query($query);
	$query = "UPDATE `invoiceconfig` SET `InvoiceSendToEmail`='" . $CustomEmail . "'";
	mysql_query($query);
}

function ResendInvoice($invoiceID) {

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);

	$settings = GetSettingsArray();

	$query = "select Email from `invoices` i inner join `customer` c on c.ID = i.CustomerID where i.ID =" . $invoiceID;

	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result)) {

		$clientemail = $row["Email"];

	}
		
		

$invoice = GetInvoice($invoiceID,false);
		
if ($invoice["EmailSent"]=="1" && $invoice["paid"]=="1"){
					
			sendMail($clientemail, $settings["companyname"], $invoiceID, $settings["linkeddomain"], $settings["sendemailfrom"],"a receipt",$invoice["totalamount"]);
				
			}else if ($invoice["EmailSent"]=="1" && $invoice["paid"]=="0"){
				
			sendMail($clientemail, $settings["companyname"], $invoiceID, $settings["linkeddomain"], $settings["sendemailfrom"],"an invoice",$invoice["totalamount"]);
			
			}else{
						sendMail($clientemail, $settings["companyname"], $invoiceID, $settings["linkeddomain"], $settings["sendemailfrom"],"an estimate",$invoice["totalamount"]);
					
			}
	

	
}

function PayInvoice($invoiceID){
		
	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);
	
	$query = "update `invoices` Set Paid=1, PaidDate=NOW() where ID =" . $invoiceID;

	mysql_query($query);
		
	
}

function GetItemsID($invoiceID) {

	mysql_connect($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password']) OR DIE("Unable to connect to database! Please try again later.");
	mysql_select_db($GLOBALS['dbname']);

	$query = "SELECT ID FROM `invoiceitem` where InvoiceID=" . $invoiceID;

	$result = mysql_query($query);

	$itemids = array();
	while ($row = mysql_fetch_array($result)) {

		array_push($itemids, $row["ID"]);

	}

	return $itemids;

}



?>