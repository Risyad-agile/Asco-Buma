<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <link rel="stylesheet" href="https://ahmadproject.org/public/css/bootstrap.min.css" type="text/css">
    
    <style>
        body{
            /* background-color: #fcc604; */
        }
        h1{
            font-weight: bold,
        }
        h1, h3{
            text-align: center;
            font-family: Poppins;
        }
        p {
            font-family: Poppins;
            font-size: 14px;
            font-weight: normal;
        }
        span {
            font-family: Poppins;
            font-size: 14px;
            font-weight: normal;
            color: red;
        }
    </style>
</head>
<body>
   <div class="container" >
       <p>
       <div class="text-center">         
           <img src="https://bromo.agile.co.id/images/agile_logo.png"
                srcset="https://bromo.agile.co.id/images/agile_logo@2x.png 2x,
                https://bromo.agile.co.id/images/agile_logo@3x.png 3x">
       </p>
    </div>
    <h3>Hallo {!!$task_checker_name!!},</h3>
    <div >
        <p>
            There Email has been generated automaticaly, because New Task has been created : {!!$task_name!!} make  by {!!$task_maker_name!!} on {!!$task_maker_time!!} 
        </p>
    </div>
    <div >
        <p>
            Message for this task as follows <span> {!!$task_msg!!} </span> please continue to Check the task
        </p>
    </div>
    <br><br>
    <p>
        <a href="https://agile.co.id">An Agile Kharisma Utama</a>
    </p>
</div>
</body>
</html>


