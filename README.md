# Datatable Server
======================

Datatable server .

  - Easy to use
  - Codeignator plug-in available.

Datatable server is php code that you can create json easily for [ajax datatable].

> The overriding design goal for Datatable Server PHP function is to create json easily with less configuration for datatable. 

### Version
1.0.1

### Codeigniter Usage

### Installation
You need to add [`codeigniter/datatable_server_helper.php`] in Codeigniter helpers folder:
(`yourproject/application/helpers/`)

### Usage
Call the function in your controller 

```sh
//your custom function to call inside json render
function your_function($mobile){
    return $mobile;
}

$this->load->helper('datatable_server');
$config=array(
    // Coloums you need to select form database. [Array()]
    'aColumns' => array( 'id','name','email','mobile'),
	 
    //Index coloum of the table.	[String]
    'sIndexColumn' => 'id', 
	 
    // Table name 	[String]
 	// You can also gave join statement
 	// ex : 'sTable' =>"order left join invoice on invoiceid=orderid" ,
    'sTable' =>"address-book" , 	
	
    //If you have any conditional statement you can add like this. [String][optional]
    //'sCondition'=>'name like "%a"',	
		
	//Output needed in each coloum. [Array(Array('type'=>value))]
	// Usage
	// text	=>	Normal Text values
	// var =>  Variable Name.The variable name will replaced with the variable value.
	// The variable that you are selecting should be added in aColoums.
	// html =>  You can directly gave html. If you want to use variable in between you can use {{variable name}}.
	 // eval =>  You can call a function that you have written.You can use the arguments as your variable that you selected in aColoums.
     'aColumns_out' => array( 
         array('var'=>'id'),
         array('var'=>'name',
         array('html'=>'<a href="mailto:{{email}}">{{email}}</a>'),
         array('eval'=>'your_function($mobile)'),
        ),
     );	
echo dt_get_json($config);
```
License
----
MIT
[`codeigniter/datatable_server_helper.php`]:https://github.com/shabeer-ali-m/datatable_server/blob/master/codeigniter/datatable_server_helper.php
[ajax datatable]:http://www.datatables.net/examples/data_sources/server_side.html
