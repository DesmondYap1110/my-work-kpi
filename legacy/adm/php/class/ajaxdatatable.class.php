<?php

//Class of AJAX that apply in datatable
//ajaxd stand for ajax datatable
 class ajaxd
 {
     //AJAX DATATABLE PARAMETER:
     
     //*****************************************************************
     // Draw
     // Redraw the DataTables in the current context
     // Using $_POST['draw'] to get value of $draw
     // set the value by using constructor: __construct(0th position)
     //*****************************************************************
     private $draw;
     
     
     //*****************************************************************
     //
     // Row
     // Using $_POST['start'] to get value of $row
     // set the value by using constructor: __construct(1th position)
     //
     //*****************************************************************
     private $row;
     
     //*****************************************************************
     //
     // Row per page
     // Using $_POST['length'] to get value of $rowperpage
     // set the value by using constructor: __construct(2th position)
     //
     //*****************************************************************
     private $rowperpage;
     
     
     //*****************************************************************
     //
     // Column Index
     // Using $_POST['order'][0]['column'] to get value of columnIndex
     // set the value by using constructor: __construct(3th position)
     //
     //*****************************************************************
     private $columnIndex;
     
     //******************************************************************
     //
     // Column Name Based on Database
     //
     // Using: $_POST['columns'][$_POST['order'][0]['column']]['data']
     //                           OR 
     // $_POST['columns'][$columnIndex]['data'] to get the value
     //
     // set the value by using constructor: __construct(4th position)
     //******************************************************************
     private $columnName; 
     
     //******************************************************************
     //asc or desc
     //Using $_POST['order'][0]['dir'] to get the value of $columnSortOrder.
     //set the value by using constructor: __construct(5th position)
     //******************************************************************
     private $columnSortOrder;
     
     //*******************************************************************************************
     //Search Value in search bar of datatable. 
     //Using mysqli_real_escape_string($dbconn,$_POST['search']['value']) to get the searchValue
     //set the value by using constructor: __construct(6th position)
     //*******************************************************************************************
     private $searchValue;
     
     //*******************************************************************************************
     //Search Query need to set need to set in order to make the search bar of datatable work.
     //Set searchQuery by using the setSearch($sql) Methods
     //*******************************************************************************************
     private $searchQuery;
     
     //******************************************************************
     //Database : 
     //$dbconn = Database connection
     //set the value by using constructor: __construct(7th position)
     //******************************************************************
     private $dbconn;
     
     //******************************************************************
     // $tbname = Database table name
     // The database table that require using to select data
     // set the value by using constructor: __construct(8th position)
     //******************************************************************
     private $tbname;
     
     //******************************************************************
     //$condition = SQL condition (SQL Format)
     //            (eg: AND deleted=0 AND status!=999) 
     //             Syntax: Must start with 'AND'
     //******************************************************************
     private $condition;
     
     
     //******************************************************************
     //$data = Database Data Based on Row.
     //Syntax:
     //
     //$data[] = array(
     //       'row1' => $memberdata->getDataRow()[$i]['row1'],
     //       'row2' => $memberdata->getDataRow()[$i]['row2'])
     //     );
     //
     //Using $data to store the information array
     //
     //         $response=array
     //         (
     //             "aaData" => $data
     //         );
     //
     //Encode the value in form of JSON using : json_encode($response).
     //******************************************************************
     private $data;
     
     
     //******************************************************
     //$filter = Filter Function in Layout (SQL Format)
     //          (eg: AND rank='1')
     //          Syntax: Must start with 'AND'
     //******************************************************
     private $filter;
     
     //******************************************************
     //$LJtbname = The table that required to join
     //          
     //          
     //******************************************************
     
     
    public function __construct($draw,$row,$rowperpage,$columnIndex,$columnName,$columnSortOrder,$searchValue,$dbconn,$tbname)
    {
        $this->draw = $draw;
        $this->row = $row;
        $this->rowperpage = $rowperpage;
        $this->columnIndex = $columnIndex;
        $this->columnName = $columnName;
        $this->columnSortOrder = $columnSortOrder;
        $this->searchValue = $searchValue;
        
        $this->dbconn = $dbconn;
        $this->tbname = $tbname;
        
        $this->condition ="";
        $this->searchQuery ="";
        $this->filter="";
    }
        
    //Assign the condition of SQL 
     
    public function setCondition($CQ)
    {
        $this->condition=$CQ;
    }
    
     //Assign the search query of SQL used to integrated with datatable 
    public function setSearch($SQ)
    {
        if($this->searchValue != '')
        {
            $this->searchQuery = " $SQ";
        }
    
    }
    
    public function setFilter($FQ)
    {
        $this->filter=$FQ;   
    }
     
    
     public function getDataRow()
     {
         return $this->data;
     }


    //Total Number OF RECORD WITHOUT FILTERING
    public function recordWithoutfilter()
    {

        $sel = mysqli_query( $this->dbconn,"select count(*) AS allcount FROM `$this->tbname` WHERE 1 $this->searchQuery $this->condition $this->filter") or die(mysqli_error($this->dbconn));
        
        $records = mysqli_fetch_assoc($sel);
        
        $totalRecords = $records['allcount'];
        
        return $totalRecords;
    }
     
    //Get total number of row of table
    public function totalrow()
    {
        
        $sql = "SELECT * FROM `$this->tbname` WHERE 1 $this->searchQuery $this->condition $this->filter ORDER BY $this->columnName  $this->columnSortOrder LIMIT $this->row , $this->rowperpage";
        
        $query = mysqli_query( $this->dbconn,$sql) or die (mysqli_error( $this->dbconn));
        
        while($this->data[]= mysqli_fetch_assoc($query))
        {
            $this->data[] = mysqli_fetch_assoc($query);
        }
        
        return $totalrow = mysqli_num_rows($query);
    }
     
    //Check Generate SQL Statement if facing the error version 1
    public function getsql()
    {
        $sql = "SELECT * FROM `$this->tbname` WHERE 1 $this->searchQuery $this->condition $this->filter ORDER BY $this->columnName  $this->columnSortOrder LIMIT $this->row , $this->rowperpage";
        
        return $sql;
        
    }

    //Check Generate SQL Statement if facing the error version 2
    public function getsqlv2()
    {
        $sql = "SELECT * FROM `$this->tbname` WHERE 1 $this->searchQuery $this->condition $this->filter ";
        
        return $sql;

    }

 }
?>