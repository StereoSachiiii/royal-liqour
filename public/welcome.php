<?php 
require_once __DIR__. "/../public/session/session.php";

$session = new Session();





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging in progress</title>
    <style>
        *{
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body{
            display: flex ;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        #not-active{
            display:none;

        }

        #active{
            display: block;
        }

        #modal{
            box-shadow: 20px 20px 20px 20px rgba(0,0,0,0.2);
            padding:20px;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap:30px;
            font-family: Arial, Helvetica, sans-serif;
        
        }

        #modal:hover{
            
            animation: hoverEffect 0.4s ease-in  forwards;
        }

        @keyframes hoverEffect {
            0%{
                transform: translateY();
            }100%{
                transform: translateY(-20%);
            }

   
            
        }

             @keyframes loadingEffect {
            0%{
                width: 0px;
            }100%{
                width: 498px;
            }
            
        }

        #loader{
            width: 500px;
            height: 10px;
            border: 2px solid black;
            border-radius: 5px;
            position: relative;
        }

        #progress{
            
            position: absolute;
            top:0px;
            left:0;
            background: greenyellow;
            width: 100%;
            height: 100%;
            animation: loadingEffect 1.5s ease-in forwards;
        }


        
    </style>
</head>
<body>
    <div id="modal">
        Hold on we are Logging you in!

        <div id="loader">

            <div id="progress">

            </div>
            
        </div>
    </div>



<script>
    setTimeout(()=>{
        window.location.href = "index.php"
    },1500)

</script>

</body>
</html>