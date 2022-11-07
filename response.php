<?php

             
    require_once("Database.php");
    $db=new Database();
    $data2 = $db->listele("contacts","first_name, last_name, phone, email",1,"0");
    /* echo "<pre>";
    print_r($data); */
            
  /*while($row = $result->fetch_array(MYSQLI_ASSOC)){
    $results["data"][] = $row ;
  }*/
  $results2 = array(
    "sEcho" => 1,
      "iTotalRecords" => count($data2),
      "iTotalDisplayRecords" => count($data2),
        "aaData"=>$data2);

  
  echo json_encode($results2);

  
?>