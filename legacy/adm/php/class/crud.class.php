<?php

//Class of CRUD that apply in INSERT, UPDATE, DELETE, DISPLAY
//NOT SUITABLE FOR JOINING TABLE
//crud stand for Create Read Update Delete

class crud
{

    //*****************************************************************
    // $tbname: => table name
    // The table of database that involve CRUD
    // set the value by using constructor: __construct(0th position)
    // get the value by : get_tbname()
    //*****************************************************************
	private $tbname;

    //*****************************************************************
    // $dbconn: => database connection
    // Create connection to database
    // set the value by using constructor: __construct(1th position)
    // get the value by : get_dbconn()
    //*****************************************************************
    private $dbconn;

    //*****************************************************************
    // $row: => data info fetch from database
    // Use to display the data
    // It is don't have setter
    // get the value by : get_row()
    //*****************************************************************
    private $row;


    public function __construct($tbname,$dbconn)
    {
    	$this->dbconn = $dbconn;
        $this->tbname = $tbname;
    }

    //GET get_dbconn
    function get_dbconn()
    {
        return $this->dbconn;
    }

    //get table name
    function get_tbname()
    {
        return $this->tbname;
    }

    //GET ALL DATA
    function get_row()
    {
        return $this->row;
    }
    

    //FETCH THE DATA BUT STORE IN ARRAY RETURN THE TOTAL OF DATA
    function totaldata($condition) 
    {
        $sql = "SELECT * FROM `$this->tbname` WHERE 1 $condition AND deleted='0' ";

        $query = mysqli_query($this->dbconn,$sql) or die(mysqli_error($this->dbconn));

        $total_row = mysqli_num_rows($query);

        if($total_row>0)
        {
            while($this->row[] = mysqli_fetch_assoc($query))
            {
                $this->row[] = mysqli_fetch_assoc($query);
            }
        }

        return $total_row;
    }

    //FETCH ONE DATA STORE IN ARRAY RETURN THE TOTAL OF DATA
    function totaleachdata($condition)
    {
        $sql = "SELECT * FROM `$this->tbname` WHERE 1 $condition AND deleted='0' ORDER BY ID DESC LIMIT 1";

        $query = mysqli_query($this->dbconn,$sql) or die(mysqli_error($this->dbconn));

        $total_row = mysqli_num_rows($query);

        if($total_row>0)
        {
            $this->row = mysqli_fetch_assoc($query);
        }

        return $total_row;

    }

    //ADD DATA AND LINK US TO OTHER PAGE
	public function adddata($togo,$data)
	{

        $sql = "INSERT INTO `".$this->tbname."` (";            
        $sql .= implode(",", array_keys($data)) . ') VALUES (';            
        $sql .= "'" . implode("','", array_values($data)) . "')"; 

        $query = mysqli_query($this->dbconn,$sql) or die (mysqli_error($this->dbconn));


        $togo="$togo?add=succ";
       	header(sprintf("Location: %s",$togo)); 

	}

    //UPDATE DATA AND LINK US TO OTHER PAGE
    public function update($togo,$data,$pk,$pkvalue)
    {
        //Length of array
        $len=count($data);

        //array column
        $column=array_keys($data);

        //array value
        $value=array_values($data);

        $sql="Update `$this->tbname` SET ";

        for($i=0; $i<$len; $i++)
        {

            $sql.=" $column[$i] = '$value[$i]'";

            if($i!= ($len-1))
            {
                //If array is not at the end no need display comma
                $sql.=",";
            }
        }
        $sql.=" WHERE $pk = '$pkvalue'";

        $query = mysqli_query($this->dbconn,$sql) or die (mysqli_error($this->dbconn));
        
        $togo="$togo?edit=succ";
       	header(sprintf("Location: %s",$togo)); 
    }

    //ADD THE DATA WITHOUT GO TO OTHER PAGE
    public function adddataonly($data)
	{

        $sql = "INSERT INTO `".$this->tbname."` (";            
        $sql .= implode(",", array_keys($data)) . ') VALUES (';            
        $sql .= "'" . implode("','", array_values($data)) . "')"; 

        $query = mysqli_query($this->dbconn,$sql) or die (mysqli_error($this->dbconn));
	}

    //UPDATE THE DATA WITHOUT GO TO OTHER PAGE
    public function updateonly($data,$pk,$pkvalue)
    {
        //Length of array
        $len=count($data);

        //array column
        $column=array_keys($data);

        //array value
        $value=array_values($data);

        $sql="Update `$this->tbname` SET ";

        for($i=0; $i<$len; $i++)
        {

            $sql.=" $column[$i] = '$value[$i]'";

            if($i!= ($len-1))
            {
                //If array is not at the end no need display comma
                $sql.=",";
            }
        }
        $sql.=" WHERE $pk = '$pkvalue'";

        $query = mysqli_query($this->dbconn,$sql) or die (mysqli_error($this->dbconn));
        
    }

    //UPDATE THE DATA WITHOUT GO TO OTHER PAGE WITH CONDITION
    public function updateonlyc($data,$condition)
    {
        //Length of array
        $len=count($data);

        //array column
        $column=array_keys($data);

        //array value
        $value=array_values($data);

        $sql="Update `$this->tbname` SET ";

        for($i=0; $i<$len; $i++)
        {

            $sql.=" $column[$i] = '$value[$i]'";

            if($i!= ($len-1))
            {
                //If array is not at the end no need display comma
                $sql.=",";
            }
        }
        $sql.=" WHERE $condition";

        $query = mysqli_query($this->dbconn,$sql) or die (mysqli_error($this->dbconn));
        
    }

    //VERIFY DATA WITH ONE CONDITION
    public function verifydata($column,$data)
    {
        $sql = "SELECT * FROM `$this->tbname` WHERE $column ='$data' AND deleted='0' ";
        $query = mysqli_query($this->dbconn,$sql) or die (mysqli_error($this->dbconn));
        $total_row = mysqli_num_rows($query);

        if($total_row>0)
        {
            return 1;//True
        }
        else
        {
            return 0;//False
        }
    }

    //Hard Delete
    public function deletedata($condition)
    {
        $sql = "DELETE FROM `$this->tbname` WHERE $condition ";
        $query = mysqli_query($this->dbconn,$sql) or die (mysqli_error($this->dbconn));
    }

    //VERIFY DATA WITH MUTIPLE CONDITION
    public function verifydatac($condition)
    {
        $sql = "SELECT * FROM `$this->tbname` WHERE 1 $condition AND deleted='0' ";
        $query = mysqli_query($this->dbconn,$sql) or die (mysqli_error($this->dbconn));
        $total_row = mysqli_num_rows($query);

        if($total_row>0)
        {
            return 1;//True
        }
        else
        {
            return 0;//False
        }
    }


    //SET PASSWORD
    public  function setPassword($default)
    {
        $password = password_hash($default,PASSWORD_DEFAULT);
        return $password;
    }
}

?>


    
