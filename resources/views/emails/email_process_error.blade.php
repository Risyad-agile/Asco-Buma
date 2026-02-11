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
    <h3>Hi there's {!!$orgname!!}</h3>
    <div >
        <p>
            There has been a problem with receiving the data you provided 
        </p>
    </div>

    <div >
        <p>
            error message as follows <span> {!!$pesan!!} </span> it hapen on {!!$waktu!!}
        </p>
    </div>
    <br><br>
    <p>
    <a href="https://agile.co.id">An Agile Kharisma Utama</a>
    </p>
</div>
</body>
</html>


