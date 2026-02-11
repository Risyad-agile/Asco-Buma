<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
        <link rel="stylesheet" href="https://ahmadproject.org/public/css/bootstrap.min.css" type="text/css">
        <style>
        h1{
            text-align: center;
            font-family: Poppins;
        }
        h3{
            text-align: left;
            font-family: Poppins;
        }
        .teks {
            width: 100%;
            height: 60px; 
            font-family: Poppins;
            font-size: 12px;
            font-weight: normal;
            font-stretch: normal;
            font-style: normal;
            line-height: 1.67;
            letter-spacing: normal;
            text-align: left;
            color: var(--black);
        }
        </style>
</head>
<body>
<h1 style="height: 48px;text-align: center;margin: 7px;font-size: 24px;font-weight: bold;">API REQUEST APPROVAL</h1>
 <h3>Hello... {!!$user->name!!},</h3>
 <div>
    <p class="teks">
    Your API Request has been approved, here with the credential and document you will need to access ASRI-CONNECT, the middleware 
    API Server.  For accesing API your can use Bearer Token as Authorization on Headers, and your Access Token Is...
    </p>
    <span style="font-size: 12px;font-style: italic;">[token start]</span>
    <p>
        {!!$user->user_token!!}
    </p>
    <span style="font-size: 12px;font-style: italic;">[token end]</span>
    <p class="teks">
        For further information, please find in this <a href="https://drive.google.com/drive/folders/16UCS_XjQHAshcGbPsRJhW7T-w9ztEowP?usp=sharing">API Documentation</a>
    </p>
</div>
 <div class="container">
    <div class="row">
        <div class="col"><span style="font-size: 12px;font-style: italic;">Generated on : {{date('d-m-Y H:i:s')}}</span></div>
        <div class="col"><span style="font-size: 12px;font-style: italic;">These email generate automatically, don't need to reply</span></div>
    </div>
</div> 
<br><br>
<p>
<a href="https://agile.co.id">An Agile Kharisma Utama</a>
</body>
</html>

