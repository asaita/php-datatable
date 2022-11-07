<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
 



    <title>Php datatable</title>
</head>
<body>


    <table id="example2" class="display" style="width:100%">
        <thead>
            <tr>
                <th>First name</th>
                <th>Last name</th>
                <th>Phone</th>
                <th>Email</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>First name</th>
                <th>Last name</th>
                <th>Phone</th>
                <th>Email</th>
                
            </tr>
        </tfoot>
    </table>
    <!-- jQuery -->
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
 
 <!-- DataTables -->
 <script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
</body>
</html>

<script>
$( document ).ready(function() {
      $('#example2').dataTable({
         "bProcessing": true,
                 "sAjaxSource": "response.php",
         "aoColumns": [
            { mData: 'first_name' } ,
                        { mData: 'last_name' },
                        { mData: 'phone' },
                        { mData: 'email' }
                ]
        });   
});

</script>