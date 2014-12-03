<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Datatable Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Shabeer Ali M
 * @link		https://github.com/shabeer-ali-m/datatable_server
 */

// ------------------------------------------------------------------------
if ( ! function_exists('dt_get_json'))
{
	/**
	 * Datatable server is php code that you can create json easily for ajax datatable. 
	 * git : https://github.com/shabeer-ali-m/datatable_server
	 * 
	 *
	 *  
     * $config=array(
     *
     * Coloums you need to select form database. [Array()]
     * 'aColumns' => array( 'id','name','email','mobile'),
	 *	
     * Index coloum of the table.	[String]
     * 'sIndexColumn' => 'id', 
	 *
     * Table name 	[String]
 	 * You can also gave join statment
 	 * ex : 'sTable' =>"order left join invoice on invoiceid=orderid" ,
     * 'sTable' =>"address-book" , 	
	 *
     * If you have any conditional statement you can add this. [String][optional]
     * 'sCondition'=>'name like "%a"',	
	 *	
	 * Output needed in each coloum. [Array(Array('type'=>value))]
	 * Usage
	 * text 	=>	Normal Text values
	 * var     =>  Variable Name.The variable name will replaced with the variable value.
	 * 				The variable that you are selecting should be added in aColoums.
	 * html    =>  You can directly gave html. If you want to use variable in between you can use {{variable_name}}.
	 * eval    =>  You can call a function that you have written.You can use the arguments as your variable that you selected in aColoums.
     * 'aColumns_out' => array( 
     *    array('var'=>'id'),
     *    array('var'=>'name',
     *    array('html'=>'<a href="mailto:{{email}}">{{email}}</a>'),
     *    array('eval'=>'your_function($mobile)'),
     *    ),
     *  );	
     *
     * @access	public
	 * @param	array()
	 * @return	json 	JSON data for datatable
	 */	
	function dt_get_json($config)
	{
		/* creating instance */
		$CI =& get_instance();

		/* loading database */
		$CI->load->database();

		/* Array of database columns which should be read and sent back to DataTables. Use a space where
		 * you want to insert a non-database field (for example a counter or static image)
		*/
		$aColumns = $config['aColumns'];

		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = $config['sIndexColumn'];

		/* DB table to use */
		$sTable = $config['sTable'];

		/* Query condition  */
		$sCondition=isset($config['sCondition'])?$config['sCondition']:'';

		/* 
		* Paging
		*/
		$sLimit = "";
		if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
		{
			$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
				intval( $_GET['iDisplayLength'] );
		}


		/*
		* Ordering
		*/
		$sOrder = "";
		if ( isset( $_GET['iSortCol_0'] ) )
		{
			$sOrder = "ORDER BY  ";
			for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
			{
				if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder .= "`".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."` ".
						($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
				}
			}
			
			$sOrder = substr_replace( $sOrder, "", -2 );
			if ( $sOrder == "ORDER BY" )
			{
				$sOrder = "";
			}
		}

		/* 
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		*/
		$sWhere = "";
		if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
		{
			$sWhere = "WHERE (";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "`".$aColumns[$i]."` LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}

		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
			{
				if ( $sWhere == "" )
				{
					$sWhere = "WHERE ";
				}
				else
				{
					$sWhere .= " AND ";
				}
				$sWhere .= "`".$aColumns[$i]."` LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
			}
		}
		
		if($sWhere==""&&$sCondition!="")
			$sWhere.='where ('.$sCondition.') ';
		else if($sCondition!="")
			$sWhere.='and ('.$sCondition.') ';

		/*
		 * SQL queries
		 * Get data to display
		*/
		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
			FROM   $sTable
			$sWhere
			$sOrder
			$sLimit
			";

		/* fetching result from database */
		$rResult = $CI->db->query($sQuery);
		$sQuery = "
			SELECT COUNT(`".$sIndexColumn."`) as 'count' 
			FROM $sTable $sWhere
		";
		$rResultTotal = $CI->db->query($sQuery);
		$rResultTotal = $rResultTotal->row_array();
		$iTotal = $rResultTotal["count"];
		$iFilteredTotal=$iTotal;
		
		/*
		 * Output
		 */
		$output = array(
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);	

		foreach ($rResult->result_array() as  $aRow )
		{
				$row = array();
				extract($aRow);

				foreach($config['aColumns_out'] as $aColumnsrow)
				{
					$col="";	
					foreach($aColumnsrow as $key=>$value)
					{	
						if($key=='text')
							$col.=$value;
						else if($key=='var')
							$col.=$aRow[$value];
						else if($key=='html')
						{
							if(preg_match_all('/\{{(.*?)\}}/',$value,$match)){
								foreach($match[1] as $v=>$k) 
									$match[1][$v]=$aRow[$k];
										$col.=str_replace($match[0],$match[1],$value);
							}
							else
								$col.$value;
						}
						else if($key=='eval')
							eval("\$col = ".$value.";");		
						else
							$col.='Invalid type!';
					}
					$row[] = $col;
				}
				
				$output['aaData'][] = $row;
		}
						
			
			return json_encode( $output );

	}
}

// ------------------------------------------------------------------------



/* End of file datatable_helper.php */
/* Location: ./application/helpers/datatable_helper.php */