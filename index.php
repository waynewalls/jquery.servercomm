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
<!doctype html>
<html>

    <head>

        <meta http-equiv="content-type" content="text/html; charset=utf-8">

        <title>serverComm Plugin Demonstration &amp; Documentation</title>
        
        <!-- CSS : implied media="all" from html5boilerplate [ http://html5boilerplate.com/ ] -->
        <link rel="stylesheet" href="css/style.css">

        <style type="text/css">

            body { background-color: #f6f8f6; line-height:1.3em; }
            
            .pageTitle { font-size:150%; color:#654; font-weight:bold; margin:25px 0; text-align:center; }

            .contentContainer {
                visibility:visible; background-color:#fff; padding:20px; margin:0 auto 25px auto; width:750px; border:1px solid #987; position:relative; overflow:hidden;
                border-radius:5px;
            }   
            
            .contentContainer p { margin:2em 0 0 0; }

            .bold { font-weight:bold; color:#000; }

            .pHeader { font-weight:bold; color:#009; }

            .prompt { font-weight:bold; color:#009; margin:1em 0 0.15em 0; }

            .container { margin-left:1em; line-height:1.75em; }

            .help { cursor:pointer; }

            #pageWarning { display:none; color:#d00; }

            #asterick { display:none; font-weight:bold; color:red; }

            button { margin:1em 0 0 0.5em; }

            #return, #data { margin:0.75em 0 0 2em; }

            #data { margin-top:0.25em; }
            #return span { background-color:#ccc }
            #data span { background-color:#ccf }

            .demo_warning { font-size:90%; font-weight:bold; color:white; background-color:#00a; padding:4px 5px 6px 5px; border:solid 2px #005; position:absolute }

            .optionsPrompt { font-weight:bold; font-size:110%; color:#009; margin:2em 0 0 0; }
            .options { margin:1em 0 0 25px;}
            .options pre { padding:0; white-space:pre; }

            /* style the serverComm plugin UI prompt */
            #sc_contactServerPrompt { font-size:110%; }

        </style>

        <!--[if IE]>

            <style type="text/css">

                pre { font-size:100%; }
                .bold { font-weight:bold; font-size:110%; color:#009; }

            </style>

        <![endif]-->

    </head>


    <body>

        <h1 class="pageTitle">jQuery serverComm Plugin Demonstration &amp; Documentation</h1>
        
        <div class="contentContainer">

            <p>
        
                Version: 0.95<br>
                Date: 26 May 2013<br>
                License: MIT License or GNU General Public License (GPL) Version 2<br>
                <a href="http://github.com/waynewalls/jquery.servercomm">http://github.com/waynewalls/jquery.servercomm</a><br><br>                
                
                Tested with Internet Explorer 6 - 10, Firefox, Chrome, and Safari
                
            </p>
            
            <p>
    
                <span class="pHeader">Background. </span>This plugin provides a user interface (UI) and simple API for
                $.ajax().  It was developed for a training applications whose primary user group was in West Africa.
                This region of the world&mdash;at the time&mdash;was served by a single Internet backbone traveling
                up the west side of the continent. Internet use involved long latency and frequent dropped connections.
                Because of this, we wanted a UI that would keep the user informed about connection status and also retry
                automatically in case of a dropped connection.
    
            </p>
    
            <p>
    
                <span class="pHeader">Limitations. </span>At present, the serverComm plugin only supports the
                $.ajax() text data type for receiving responses from the server.  To keep the UI simple, the plugin
                currently handles only one AJAX request at a time.
    
            </p>
    
            <p>
    
                <span class="pHeader">How to use this demonstration. </span>Select one of the connection test types
                shown below and press the Submit button.  Rollover the ? to get a description of each test.  You can view
                what is returned by the server-side script just below the Submit button. <span id="pageWarning">(Your
                browser appears to have cookies disabled.  Session cookies required for the &quot;initial failure&quot;
                test type*)</span>
    
            </p>
    
            <div class="prompt">Select a connection test type then press Submit:</div>
    
            <div class="container">
    
                <label><input type="radio" name="test_value" value="success"> successful connection</label><br>
                <label><input type="radio" name="test_value" value="success_with_data"> success with returned data</label><br>
                <label><input type="radio" name="test_value" value="failure_success"> initial failure (timeout) then success<span id="asterick">*</span></label><br>
                <label><input type="radio" name="test_value" value="failure"> failed connection (timeout)</label><br>
                <button>Submit</button>
    
                <div id="return">Connection status: <span></span></div>
    
                <div id="data">Returned data: <span></span></div>
    
            </div>
    
            <div class="optionsPrompt">serverComm dependencies:</div>
    
            <div class="options" style="margin-top:0.75em;">
    
                <pre>$.serverComm</pre>
                requires jQuery (tested with 1.4.2 and 1.10.0);  there are no other dependencies.
    
            </div>
    
            <div class="optionsPrompt">serverComm usage:</div>
    
            <div class="options" style="margin-top:0.75em;">
    
                <pre>$.serverComm.contactServer( config )</pre> where config is an optional object containing serverComm options.<br><br>
    
                Example:<br>
                <pre><code>$.serverComm.contactServer( {
        url:&quot;serverComm.php&quot;,
        dataObject:{ key1:value1, key2,value2 },
        successCallback:onSuccess
    } );</code></pre>
    
            </div>
    
            <div class="optionsPrompt">serverComm options (type) [ default value ]:</div>
    
            <div class="options" style="margin-top:0.75em;">
    
                <pre>// serverComm options default values are available in $.serverComm.optionDefaults</pre>
    
            </div>
    
            <div class="options" style="margin-top:0.75em;">
    
                <pre>$.serverComm.options.<span class="bold">url</span> (string) [ empty string ]</pre>
                The URL to assign to the $.ajax() URL property
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">method</span> (string) [ "POST" ]</pre>
                The method to assign to the $.ajax() type property
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">dataObject</span> (object) [ null ]</pre>
                An object to be assigned to the $.ajax() data property
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">autoRetrys</span> (integer) [ 4 ]</pre>
                The number of times to automatically retry the request
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">autoTimeout</span> (integer) [ 7000 ]</pre>
                The number of milliseconds to wait for a response from the server
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class=bold>giveupCallback</span> (function(error)) [ null ]</pre>
                A function that will be called after the last automatic retry.  It is passed the error that was returned
                by the server-side script or $.ajax()
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">errorCallback</span> (function(error, request)) [ null ]</pre>
                A function that will be called before initiating each automatic retry.    It is passed the error that was returned
                by the server-side script or $.ajax() and the number of request attempts.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">successCallback</span> (function(response)) [ null ]</pre>
                A function that will be called after each successful connection attempt.    It is passed the text string that
                $.ajax() passes to its success callback.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">contactPromptText</span> (string) [ "Contacting server" ]</pre>
                A string shown in UI prompt during the first connection attempt.  The string can include HTML that can be
                contained within inline element.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">giveupPromptText</span> (string) [ "The problem hasn't gone away &mdash; try again later" ]</pre>
                A string shown in UI prompt after the last automatic retry has failed.  The string can include HTML that 
                can be contained within inline element.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">successPromptText</span> (string) [ "Contacting server &mdash; SUCCESS!" ]</pre>
                A string shown in UI prompt after a successful automatic retry.  The string can include HTML that 
                can be contained within inline element.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">contactImagePath</span> (string) [ "images/busy999.gif" ]</pre>
                The path to an image that will be displayed in the UI prompt during the initial connection attempt.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">problemImagePath</span> (string) [ "images/busy666.gif" ]</pre>
                The path to an image that will be displayed in the UI prompt during automatic retries.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">closeBoxImagePath</span> (string) [ "images/close.gif" ]</pre>
                The path to an image that will be used as a close box in the UI prompt that is shown after all automatic
                retries have failed.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.options.<span class="bold">responseSeparator</span> (string) [ "|" ]</pre>
                The character used by the server-side script to separate the connection status from data being returned
                to the client.
    
            </div>
    
            <div class="optionsPrompt">serverComm public methods:</div>
    
            <div class="options" style="margin-top:0.75em;">
    
                <pre>$.serverComm.<span class="bold">configure</span>( config )</pre>
                sets serverComm option defaults where config is an object containing new values that will act as defaults
                for subsequent requests.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.<span class="bold">activeConnection</span>()</pre>
                returns a boolean; true if there is an active serverComm request otherwise false.
    
            </div>
    
            <div class="options">
    
                <pre>$.serverComm.<span class="bold">inprocessWarning</span>()</pre>
                Displays an absolutely positioned prompt in the center of the user's window that says, "Please Wait!".
                Used in conjunction with activeConnection() to prevent simultaneous serverComm requests.
    
            </div>
    
            <div class="options" style="margin-bottom:2em; ">
    
                <pre>$.serverComm.<span class="bold">contactServer</span>( config )</pre>
                Initiates a serverComm request where config is an optional object containing serverComm options as key/value pairs.
    
            </div>
        
        </div>

        <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js">
            // load jQuery from jQuery's CDN
        </script>

        <script type="text/javascript" src="jquery/sc-combined.min.js">
            // load the jquery.hoverIntent and jquery.cookie plugin
        </script>

        <script type="text/javascript" src="jquery.servercomm.min.js">
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


            // check to see if cookies are enabled
            ( function($) {

                //noinspection JSUnusedAssignment
                var enabled = false;

                // try to set a cookie
                $.cookie("test", "enabled");

                // try to retrieve it
                enabled = $.cookie("test");

                if (enabled) {

                    // delete the test cookie
                    $.cookie("test", null);
                }
                else {

                    $("#pageWarning, #asterick").css( { display:"inline" } );
                }

            }(jQuery) );


            ( function($) {

                var pageLoad = {

                    eventHandler_submit_button : function() {

                        var returnDisplay = $("#return").find("span"),
                            dataDisplay   = $("#data").find("span");

                        $("button:contains(Submit)").bind("click", function() {

                            var dataToSend = $("input:radio[name=test_value]:checked").val(),
                                button,
                                warning;

                            if (!dataToSend) {

                                button = $(this);

                                warning = $("<div />", {
                                    "class" : "demo_warning",
                                    text: " First, select a connection test type!",
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

                                    // no need for a URL, the default has been configured in the onload event
                                    method     : "POST",
                                    dataObject : { test_value : dataToSend },

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
                    },

                    eventHandler_help : function () {

                        var tooltipData = {

                            success           : { top:0, left:20, text:"This test shows a successful connection where data might be stored in a database and the server-side script returns \"success\"." },
                            success_with_data : { top:0, left:20, text:"This test shows a successful connection where data might be retreived from a database.  The server-side script returns \"success\" along with an addition string (e.g., JSON) of data." },
                            failure_success   : { top:0, left:20, text:"Our primary user group experienced long latency and frequent dropped connections.  This test shows the auto-retry feature of the plugin resulting in a successful connection." },
                            failure           : { top:0, left:20, text:"This test simulates a completely failed connection." }
                        },
                            labels = $("label"),
                            tooltip,
                            helpImage,
                            tdata;

                        labels.each( function() {
                            this.innerHTML += " &ndash; "
                        });

                        $("<img />", {
                            src : "images/help.gif"
                        })
                        .hoverIntent(function() {

                            helpImage = $(this);

                            helpImage.attr("src", "images/helpblack.gif");

                            tdata = tooltipData[ helpImage.prev().find("input")[0].value ];

                            tooltip = $("<div />", {
                                "class" : "demo_warning",
                                text: tdata.text,
                                css : {
                                    width: 250,
                                    top  : helpImage.offset().top + tdata.top,
                                    left : helpImage.offset().left + tdata.left
                                }
                            }).appendTo("body");

                        }, function() {

                            helpImage.attr("src", "images/help.gif");
                            tooltip.remove();

                        })
                        .insertAfter("label");
                    }
                };

                $(window).bind("load", function() {

                    pageLoad.eventHandler_submit_button();

                    pageLoad.eventHandler_help();

                    // set the default URL for ALL serverComm requests on this page
                    $.serverComm.configure( { url: "servercomm_demo.php" } );

                });

            }(jQuery) );

        </script>

    </body>

</html>
