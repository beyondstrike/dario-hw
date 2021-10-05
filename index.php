<?php
    //including Message_log Class
    include_once('./Message_log.php');

    //init new Message_log class with initial values;
    $m = new Message_log('2021-10-01','2021-10-05');

    //Fetching log data
    $logs = $m->get_log();

    //Getting date range, country and user title
    $dates = $m->get_dates();
    $country = $m->get_country();
    $user = $m->get_user();

    //ID for each column
    $id = 0;
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

    <title>Message Logs</title>
    <style>
        body{
            padding: 50px;
        }
    </style>
  </head>
  <body>
    <h2>Message Logs</h2>
    
    <!-- Displaying the wanted data -->
    <div>Country: <?php echo $country?:'-'; ?></div>
    <div>User: <?php echo $user?:'-'; ?></div>
    <div>From: <?php echo $dates['from']; ?></div>
    <div>To: <?php echo $dates['to']; ?></div><br>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Date</th>
                <th scope="col">Successfully sent</th>
                <th scope="col">Failed</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $date => $value) { ?>
                <tr>
                    <th scope="row"><?php echo ++$id; ?></th>
                    <td><?php echo $date; ?></td>
                    <td><?php echo $value['success']; ?></td>
                    <td><?php echo $value['fail']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

  </body>
</html>