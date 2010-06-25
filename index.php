<?PHP

if (function_exists ('ini_set'))
{
   //Use cookies to store the session ID on the client side
   @ ini_set ('session.use_only_cookies', 1);
   //Disable transparent Session ID support
   @ ini_set ('session.use_trans_sid',    0);
}

session_name("servercomm");
session_start();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>

    <head>

        <meta http-equiv="content-type" content="text/html; charset=utf-8">

        <title>serverComm Plugin Demonstration</title>

        <style type="text/css">

            .pageWarning { font-size:75%; color:#d00; }

            .prompt { font-weight:bold; color:#009; margin:0 0 0.15em 0; }

            .container { margin-left:1em; line-height:1.5em; }

            input, label, button { vertical-align:middle; }
            button { margin:1em 0 0 0.5em; }

            #return, #data { margin:1em 0 0 2em; }
            #data { margin-top:0.25em; }
            #return span { background-color:#ccc }
            #data span { background-color:#ccf }

            /* style for the alert that appears if no radio button is selected before submission */
            .demo_warning { font-size:90%; font-weight:bold; color:white; background-color:#000; padding:2px 5px; border:solid 2px #555; position:absolute }

            /* style the serverComm plugin UI prompt */
            #sc_contactServerPrompt { font-size:100%; font-family:"Lucida Sans", "Lucida Sans Unicode", "Lucida Grande", Lucida, Verdana, Helvetica, Arial, sans-serif }

        </style>

    </head>


    <body>

        <div class="pageWarning">[ session cookies required ]</div>

        <h3>jQuery serverComm Plugin Demonstration</h3>

        <div class="prompt">Select a connection test type then press Submit:</div>

        <div class="container">

            <label><input type="radio" name="test_value" value="success" /> successful connection</label>
            <label><input type="radio" name="test_value" value="success with data" /> success with returned data</label><br>
            <label><input type="radio" name="test_value" value="failure-success" /> initial failure (timeout) then success (with data)</label><br>
            <label><input type="radio" name="test_value" value="failure" /> failed connection (timeout)</label><br>
            <button>Submit</button>

            <div id="return">Connection status: <span></span></div>

            <div id="data">Returned data: <span></span></div>

        </div>

        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js">
            // load jQuery from Google's CDN
        </script>

        <script type="text/javascript" src="jquery.servercomm-yui-min.js">
            // load the jquery.servercomm plugin
        </script>


        <script type="text/javascript">


            // preload the images we'll need for this page
            // [ http://engineeredweb.com/blog/09/12/preloading-images-jquery-and-javascript ]
            ( function() {
                
                var toLoad = [
                    // see [ http://www.ajaxload.info/ ] for public domain animated gifs
                    "images/clear1.gif",
                    "images/close.gif",
                    "images/busy666.gif",
                    "images/busy999.gif"],
                    cache = [],
                    cacheImage,
                    len,
                    i;

                len = toLoad.length;
                for (i = len; i--;) {
                    cacheImage = document.createElement('img');
                    cacheImage.src = toLoad[i];
                    cache.push(cacheImage);
                }

            }() );


            ( function($) {

                var returnDisplay = $("#return span"),
                    dataDisplay   = $("#data span");

                $(window).bind("load", function() {

                    $("button:contains(Submit)").bind("click", function() {

                        var dataToSend = $("input:radio[name=test_value]:checked").val(),
                            button,
                            warning;

                        if (!dataToSend) {

                            button = $(this);

                            warning = $("<div />", {
                                className : "demo_warning",
                                text: " ^^ Select a connection test type!",
                                css : {
                                    top  : button.offset().top - 1,
                                    left : button.offset().left + button.outerWidth() + 12
                                }
                            }).appendTo("body");

                            setTimeout(function() {

                                warning.fadeTo("slow", 0, function() {
                                    warning.remove();
                                });

                            }, 1500);

                            return false;
                        }

                        else if ($.serverComm.activeConnection()) {

                            $.serverComm.inprocessWarning();
                            return false;
                        }

                        else {

                            returnDisplay.empty();
                            returnDisplay.css( { padding:"0" } );
                            dataDisplay.empty();
                            dataDisplay.css( { padding:"0" } );

                            $.serverComm.contactServer( {

                                url        : "servercomm_demo.php",
                                dataObject : { test_value : dataToSend },

                                giveupCallback : function() {
                                    console.log("giveup")
                                },

                                errorCallback : function(error, request) {

                                    var returnText    = returnDisplay.text();

                                    returnDisplay.css( { padding:"0 0.5em" } );
                                    returnDisplay.text( returnText + request + ") " + error + " ");
                                },

                                successCallback : function(response, request) {

                                    var returnText    = returnDisplay.text(),
                                        responseStatus,
                                        dataString;

                                    responseStatus = (response.indexOf($.serverComm.options.responseSeparator) === -1) ? response : response.split("|")[0];
                                    dataString     = (response.indexOf($.serverComm.options.responseSeparator) === -1) ? "" : response.split("|")[1];

                                    returnDisplay.css( { padding:"0 0.5em" } );
                                    returnDisplay.text( returnText + request + ") " + responseStatus + " ");

                                    if (dataString) {
                                        dataDisplay.css({ padding:"0 0.5em" });
                                        dataDisplay.text($.trim(dataString));
                                    }
                                }
                            });

                            return true;
                        }

                    });

                });

            }(jQuery) );

        </script>

    </body>

</html>