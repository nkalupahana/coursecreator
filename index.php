<html>
<head>
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    
    
</head>
<body>
    <div class="containter" style="height: 100%;">    
        <div style="width: 100%; height: 10%;"><p></p></div>
        
        
        <h3 class="center-align">Welcome! Enter your ID below.</h3>
        
        <form id="loginform" action="loginprocess.php" method="post">        
            <div class="row">
              <div class="input-field col s6 offset-s3">
                <input name="idnum" id="idnum" type="text" class="validate">
                <label for="idnum">Enter your ID here</label>
              </div>
            </div>
        
            <br>
            <div style="text-align: center;">
                <a onclick="submitForm()" class="center-align waves-effect waves-light btn">Login</a>
            </div>
        </form>
    </div>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script>
    
        function submitForm() {
            document.getElementById('loginform').submit();
        }    
        
    </script>
    
</body>
</html>