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
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
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
    <h3>Hi there's {!!$org_name!!}</h3>
    <div >
        <p>
            This Email Warning has been generated automaticaly because something wrong with  {!!$task_name!!} 
        </p>
    </div>

    <div >
        <p>
            Error message as follows <span> {!!$task_msg!!} </span> it hapen on {!!$task_time!!}, please check detail below...
        </p>
    </div>
    <div class="row">
        <div class="col">
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr><th>Account Style Caption</th></tr>
                        @foreach ($accstyles as $accstyle)
                        <tr>
                            <td>{!!$accstyle->acc_style_caption!!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p>
        You can fix it from the data source or edit manualy on <a href="https://asri.agile.co.id"> ASRI-CONNECT</a> with User Previlliage.
    </p>
    <br><br>
    <p>
        <a href="https://agile.co.id">An Agile Kharisma Utama</a>
    </p>
</div>
</body>
</html>


