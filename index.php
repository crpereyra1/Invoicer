<?php if(!isset($_SESSION)){
    session_start();
}

if(!isset($_SESSION['user'])){
	
header("Location: ../../Login");


	
}


?>

<!DOCTYPE html>
<head>


<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice Management</title>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:300' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="http://hayageek.github.io/jQuery-Upload-File/jquery.uploadfile.min.js"></script>
<script src="Scripts/scripts.js"></script>
<script src="Scripts/jquery.paulund_modal_box.js"></script>
<link rel="stylesheet" type="text/css" href="CSS/style.css" media="screen" />
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">


</head>
<div id="wrapper">
	
<div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li class="sidebar-brand">

                        <span style="color:white">Invoicer</span>
             
                </li>
                <li>
                    <div class="homebtn link" ><img src="images/home152.png" /><span class=" customanchor">Home</span></div>
                </li>
                <li>
                    <div class="createbtn link" ><img src="images/edit46.png" /><span class="link customanchor">Create</span></div>
                </li>
                <li>
                    <div class="transactionsbtn link" href="#"><img src="images/statistical.png" /><span class=" customanchor">Transactions</span></div>
                </li>
				<li>
                    <div class="customersbtn link" href="#"><img src="images/customer.png" /><span class=" customanchor">Customers</span></div>
                </li>
                <li>
                    <div class="categoriesbtn link" href="#"><img src="images/list91.png" /><span class=" customanchor">Products</span></div>
                </li>
                <li>
                    <div class="settingsbtn link" href="#"><img src="images/settings51.png" /><span class=" customanchor">Settings</span></div>
                </li>
     
            </ul>
        </div>



<div id="page-content-wrapper">
	
<div class="clear-both"></div>


<form name="settingsform">
<div class="settings">

<h3>COMPANY SETTINGS</h3>
<input type="text" class="companyname" class="companyname" name="companyname" placeholder="Company Name" />
<input type="text" name="streetaddress"class="streetaddress" placeholder="Street Address" />
<input type="text" name="citystate" class="citystate" placeholder="City, State" />
<input type="text" name="zipcode" class="zipcode" placeholder="Zip Code" />

<hr/>
<input type="text" class="taxrate" name="taxrate" placeholder="Tax Rate"/>
<hr/>
<span style="color:#65A0D2">Auto Send Client Invoices</span>
<br/>
<input class="sendclientautodummy" type="checkbox"/>
<input type="hidden" class="sendclientauto" name="sendclientauto"/>
<br/>
<span style="color:#65A0D2">Auto Send Custom Invoices</span>
<br/>
<div class="customsenddiv">
<input class="sendcustomcheck" type="checkbox"/>
<br/>
<input id="sendcompanyauto" class="sendcompanyauto" name="sendcompanyauto" placeholder="Forward Invoice to Email" type="text" />
</div>
<hr/>
<h3>Logo</h3>
<img style="max-width:200px; width:100%;" src="images/logo.png" />
<div style="clear:both;margin-bottom:20px"></div>

<span class="link postbuttons savesettings">Save</span>

</div>
</form>


<div class="categories">

<h3>PRODUCTS</h3>
<br/>
<div class="items-container">
<div class="items-containerinner"></div>
</div>
<div class="clear-both" style="margin-bottom:20px;"></div>
<span class="link addinvoiceitem">Add Item</span>
<br/>
<div class="invoiceitemadd">
<span class="link closeadditem">X</span>
<input class="producttoadd" placeholder="Product Name" type="text" />
<input class="itemamount" onkeypress="return isDecimal(event)" type="text" placeholder="Amount"/>
<div style="display:inline-block">
<span>Tax</span>
<input class="taxableproduct" type="checkbox" /></div>

<div style="display:inline-block"><span>Expense</span>
<input class="expenseproduct" type="checkbox"/></div>
<span class="link savelink postbuttons">Save</span></div>
</div>

<form name="invoiceform" class="invoice">
<div>

<h3>CREATE DOCUMENT</h3>
<input type="hidden" name="invoiceid" />
<input type="hidden" class="clientid" name="customerid" />
<div class="invoiceidlabel" style="display:none">Document # - <span class="invoideIDlabelval"></span></div>
<div class="wrapper-dropdown">

<input type="text" class="clientname" name="clientname" placeholder="Client Name"/>

<div class="customers">

		<ul class="customerlist" style="padding:0">
		</ul>

</div>

</div>
<input type="text" name="clienttitle" placeholder="Title"/>
<input type="text" name="clientemail" placeholder="Client Email"/>
<input type="text" name="clientstreetaddress" placeholder="Client Street Address"/>
<input type="text" name="clientcitystate" placeholder="Client City, State"/>
<input type="text" name="clientzipcode" onkeypress="return isNumber(event)" maxlength="10" placeholder="Client Zip Code"/>
<input type="text" name="expirationdays" onkeypress="return isNumber(event)" placeholder="Expires in x days"/>
<div class="createcopydiv" style="display:none"><span>Create Copy</span><input type="checkbox" class="createinvoicecopy" /></div>
<input type="hidden" name="taxrate" />
<hr/>

<div class="itemscontainer">
<div class="itemrow">

<span class="itemlabel">Item</span>
<select class="itemlist">
</select>


<input type="hidden" class="itemname" value="" name="itemname[]" />
<input type="hidden" class="itemid" value="" name="itemid[]" />
<input type="text" name="description[]" class="description" maxlength="200" placeholder="Description"/>
<input type="text" name="quantity[]" onkeypress="return isNumber(event)" value="1" class="quantity" placeholder="Quantity"/>
<input type="text" name="price[]" onkeypress="return isDecimal(event)" class="price" placeholder="Price"/>

<div style="display:inline-block">
<span>Tax</span>
<input type="checkbox" class="tax" />
<input type="hidden" class="hiddentax" name="tax[]" value="0" />
</div>

<div style="display:inline-block">
<span>Expense</span>
<input type="checkbox" class="expense" />
<input type="hidden" class="hiddenexpense" name="expense[]" value="0" />
</div>

<div style="display:inline-block">
<img src="images/trash.png" width="25px" class="link removerow"/>
</div>
<hr/>
</div>
</div>
<span class="link additem">Add Item</span>
<h4 style="text-align:right;margin-right:10px">Subtotal&nbsp;-&nbsp;<span class="subtotalsum">$0.00</span></h4>
<h4 style="text-align:right;margin-right:10px">Tax&nbsp;-&nbsp;<span class="totaltaxes">$0.00</span></h4>
<h4 style="text-align:right;margin-right:10px">Total&nbsp;-&nbsp;<span class="totalsum">$0.00</span></h4>


<div style="margin:20px">
<span style="margin-right:10px" class="link saveinvoicebtn postbuttons">Save Estimate</span>
<span style="margin-right:10px" class="link generateinvoicebtn postbuttons">Generate Invoice</span>
<span class="link  postbuttons" onclick="clearForm()">Clear</span>
</div>

</div>
</form>

<div class="Search">
<div class="invoicesearch">
<input type="text" placeholder="Invoice Search" class="searchbox" />
</div>

<div style="background-color: #FDFDFD;">
<select style='margin:10px' class='monthdropdowntransactions'></select>
<div class="invoicescontainer">
</div>
</div>
</div>


<div class="customerspage">

<div style="text-align:center"><h3>CUSTOMERS</h3></div>

<br/>

</div>

<div class="homepage">
	<h2>EF Contractors LLC.</h2>



<h3>Income</h3>
<hr/>
<h4>Pending Income - <span class="pendingincometotal"></span></h4>

<div class="Colorbar">

<div class="estimatebar"><span class="estimatetotal"></span><br/>Estimates - <span class="estimatecount"></span></div>
<div class="invoicebar"><span class="invoicetotal"></span><div>Invoices - <span class="invoicecount"></span></div></div>
<div class="expiredbar"><span class="expiredtotal"></span><br/>Expired - <span class="expiredcount"></span></div>



</div>

<div style="clear:both"></div>


<hr/>


<span style="font-size: 24px;margin-right:10px">Income (This Month)</span><select class="monthdropdown"></select>



<div class="paiddiv"><span class="receivedincome"></span><br/>Paid</div>
<div style="clear:both"></div>
<br/>
<div style="overflow:auto;background-color:#D3D3D3">
	
<h3>Expenses</h3>
<span>This Month</span>

<h2><span class="totalexpenses"></span></h2>
</div>

<div>
<h3>Net Income</h3>

<span>This Month</span>
<h2><span class="netincome"></span></h2>
</div>

</div>

<span class="saved_modal"></span>
<span class="saved_modal_settings"></span>
<span class="save_modal_category"></span>
<span class="inputerror_modal"></span>
<span class="invoice_resent_modal"></span>
<span class="sent_saved_modal"></span>
<span class="payInvoice_modal"></span>
<span class="confirmation"></span>
<span class="invoice_paid_modal"></span>
<span class="customer_saved_modal"></span>
<span class="delete_estimate_modal"></span>
<span class="delete_complete"></span>





</div>
</div>
<div id="customloader" class="loading">Loading...</div>

<div class="MobileMenu">

	<div class="link homebtn"><img src="images/home152.png" /><span class="customanchor">Home</span></div>
	<div class="link createbtn"><img src="images/edit46.png" /><span class="customanchor">Create</span></div>
	<div class="link transactionsbtn"><img src="images/statistical.png" /><span class="customanchor">Transactions</span></div>
	<div class="link customersbtn"><img src="images/customer.png" /><span class="customanchor">Customers</span></div>
	<div class="link categoriesbtn"><img src="images/list91.png" /><span class="customanchor">Products</span></div>
	<div class="link settingsbtn"><img src="images/settings51.png" /><span class="customanchor">Settings</span></div>

		
	
	</div>
	
<div class="MobileNavigation">
	
	<span class="NavImage">
	&#9776;
	</span>
	</div>

<div class="BackShadow" style="display: none;"></div>
