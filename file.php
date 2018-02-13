<?php
include 'conf';
include 'functions$offset_data';

function call_maillist_data($value1,$offset_data1,$limit1){
    
    $url = 'url';
    //------------This will set the offset and the Limit variables
        $limit='';
        $offset_data='';
        if($value1=='Getdata'){//------------------This setting is for count total record
            $limit=$limit1;
            $offset_data=$offset_data1;
        }else if($value1=='GetResultfile'){//------------------This seeting is for set the offset and limit
            $limit=$limit1;
            $offset_data=$offset_data1;
        }else{
            $limit=0;
            $offset_data=0;
            echo "Please Select Parameter To Set The Offset or Total Count";
            die;
        }
    //---------------------------End of comment    
        $params = array(
            'api_key'      => 'key',


            // this is the action that fetches a list info based on the ID you provide
            'api_action'   => '$apidata_paginator',
            'api_output'   => 'json',

            'somethingthatwillneverbeused' => '', // this variable is pushed right back in the response
            'sort' => '', // leave empty to use a default one; other values are 01, 01D, 02, 02D, etc (number is a column index, and D means 'order descending')
            'offset' => $offset_data, // start with this item (first page would be loaded using offset=0,limit=20, second page using offset=20,limit=20)
            'limit' => $limit, // items per page
            'filter' => 0, // which sectionfilter to use (0=no filter)
            'public' => 0, // is public (1=yes, 0=no)

        );

        // This section takes the input fields and converts them to the proper format
        $query = "";
        foreach( $params as $key => $value ) $query .= urlencode($key) . '=' . urlencode($value) . '&';
        $query = rtrim($query, '& ');

        // clean up the url
        $url = rtrim($url, '/ ');

        // This sample code uses the CURL library for php to establish a connection,
        // submit your request, and show (print out) the response.
        if ( !function_exists('curl_init') ) die('CURL not supported. (introduced in PHP 4.0.2)');

        // If JSON is used, check if json_decode is present (PHP 5.2.0+)
        if ( $params['api_output'] == 'json' && !function_exists('json_decode') ) {
            die('JSON not supported. (introduced in PHP 5.2.0)');
        }

        // define a final API request - GET
        $api = $url . '/admin/?' . $query;

        $request = curl_init($api); // initiate curl object
        curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        //curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

        $response = (string)curl_exec($request); // execute curl fetch and store results in $response

        // additional options may be required depending upon your server configuration
        // you can find documentation on curl options at http://www$offset_data.net/curl_setopt
        curl_close($request); // close curl object
        if ( !$response ) {
            die('Nothing was returned. Do you have a connection?');
        }
        // This line takes the response and breaks it into an array using:
        // JSON decoder
        //$result = json_decode($response);
        // unserializer
        $x=json_decode($response);
        //---------------------This is to fetch the count initially to get the loop count of API
        if($value1=='Getdata'){
            $response[]='';
            if($x->result_code==1){
                $count=count($x->total);
                if($count>=1){
                    $response[]="Success";
                    $response[] =$x->total;
                }else{
                    $response[]="Success_empty";
                    $response[]=0;//"There is no result set from active $apidata";
                }
            }else{
                $response[]="Error";
                $response[]=0;
            }
            return $response;
            
        }
        //------------------------------------------------------End of comment
        //------------------------------------This will update and insert the result
        else if($value1=='GetResultfile'){
         
            $countrows=count($x->rows);
            //--------------check table count
            $variables=mysql_query("select count(id) as cnt from table1");
            $res_query=mysql_fetch_array($variables);
            //---------------------------------------This is one time insertion
            if($res_query['cnt']<=0){
                $value_concat='';
                for($j=0;$j<$countrows;$j++){
                    if($x->rows[$j]->status==5){
                        $value_concat.="('".addslashes($x->rows[$j]->id)."','".addslashes($x->rows[$j]->name)."','".$x->rows[$j]->sdate."','".$x->rows[$j]->send_amt."','".$x->rows[$j]->total_amt
                            ."','".$x->rows[$j]->opens."','".$x->rows[$j]->uniqueopens."','".$x->rows[$j]->hardbounces."','".$x->rows[$j]->softbounces."','".$x->rows[$j]->unsubscribes
                            ."','".$x->rows[$j]->status."','".$x->rows[$j]->messageid."','".$x->rows[$j]->uniqueopens."',0),";
                    }else{
                        //------------------------------------Do not include the other status
                    }
                }
                $insert_value=rtrim($value_concat,',');
                $insert_all="INSERT INTO `table1`(columns) values ".$insert_value;
                
                    if(mysql_query($insert_all)){
                        echo "One time insertion successfully";
                    }else{
                        echo "There is a issue in one time insertion";
                    }
            }else{
                //-------------------------------------------------This is update and insert for existing query
                $query_field="select distinct id,status from table1";
                $result_fetch=mysql_query($query_field);
                $keyvalues = array();
                while($row=mysql_fetch_array($result_fetch)){
                    $keyvalues[$row['id']]=$row['status'];
                }
                for($j=0;$j<$countrows;$j++){
                    if($x->rows[$j]->status==5){
                        if(in_array($x->rows[$j]->id,array_keys($keyvalues))){//echo "update".$x->rows[$j]->id."<br/>";
                            //-------------------------------------------------------------Update the existing record
                            $update_query="UPDATE `table1` SET name='".addslashes($x->rows[$j]->name)."',sdate='".addslashes($x->rows[$j]->sdate)."',"
                               . "send_amt='".addslashes($x->rows[$j]->send_amt)."',total_amt='".addslashes($x->rows[$j]->total_amt)."',opens='".addslashes($x->rows[$j]->opens)."',"
                               . "uniqueopens='".addslashes($x->rows[$j]->uniqueopens)."',hardbounces='".addslashes($x->rows[$j]->hardbounces)."',softbounces='".addslashes($x->rows[$j]->softbounces)."',"
                               . "unsubscribes='".addslashes($x->rows[$j]->unsubscribes)."',status='".addslashes($x->rows[$j]->status)."',initiated='1' where id='".addslashes($x->rows[$j]->id)."'";
                            if(mysql_query($update_query)){
                                
                            }else{
                                echo "Issue with updating (query) record for $apidata id".$x->rows[$j]->id."<br/>";
                            }
                        }else{
                            //echo "insert".$x->rows[$j]->id."<br/>";
                            //------------------------------------------------------Insert the new record
                            $value_concat.="('".addslashes($x->rows[$j]->id)."','".addslashes($x->rows[$j]->name)."','".$x->rows[$j]->sdate."','".$x->rows[$j]->send_amt."','".$x->rows[$j]->total_amt
                            ."','".$x->rows[$j]->opens."','".$x->rows[$j]->uniqueopens."','".$x->rows[$j]->hardbounces."','".$x->rows[$j]->softbounces."','".$x->rows[$j]->unsubscribes
                            ."','".$x->rows[$j]->status."','".$x->rows[$j]->messageid."','".$x->rows[$j]->uniqueopens."',0),";
                            $insert_new=rtrim($value_concat,',');
                            $insert_new="INSERT INTO `table1`(`columns`) values ".$insert_new;

                            if(mysql_query($insert_new)){
                                
                            }else{
                                echo "There is a issue in New record insertion for $apidata id".$x->rows[$j]->id."<br/>";
                            }
                        }
                    }else{
                    //------------------------------Do not include the other status    
                    }
                }
            }
        }else{
            
        }
        
}
//---------------------------------Call this method to get the count of total $apidata
$maxlimit_val=100;

$array_response_count=call_maillist_data("Getdata",0,10);

if($array_response_count[1]=='Success'){
    if($array_response_count[2]<=$maxlimit_val){
        $offset_data=0;
        $limit=$maxlimit_val;
        //-----------------------------------This will call the method one time
         call_maillist_data("GetResultfile",$offset_data,$limit);
    }else{
        //---------------This is for if we get more than 100 records
        $getloopcount=  ceil($array_response_count[2]/$maxlimit_val);
        for($i=0;$i<$getloopcount;$i++){
            $offset_data=($maxlimit_val*$i)+1;
            $limit=$maxlimit_val;
            call_maillist_data("GetResultfile",$offset_data,$limit);
        }
    }
}
?>