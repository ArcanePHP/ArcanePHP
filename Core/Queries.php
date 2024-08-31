<?php 
function getAll($db,$tablename){

    $sql = 'SELECT * FROM '.$tablename ;
    $stmt = $db->prepare($sql);
    return $sql ;
}
?> 