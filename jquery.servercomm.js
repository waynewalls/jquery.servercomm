/**
 *  jQuery.servercomm plugin -- UI and API for $.ajax() requests
 *  Copyright (c) 2010 Wayne Walls - wfwalls(at)gmail(dot)com
 *  License: MIT License or GNU General Public License (GPL) Version 2
 *  Date: 28 June 2010
 *  @author Wayne Walls
 *  @version 0.9
 *
 */


/*jslint browser: true, devel: true, onevar: true, undef: true, nomen: true, eqeqeq: true, bitwise: true, regexp: true, newcap: true, immed: true */
/*global window, jQuery */


/**
 * The following anonymous function creates a closure that defines
 * the jquery.servercomm plugin.
 *
 * Inside this function you will find, in order, the following sections:
 * --PRIVATE VARIABLES
 * --PRIVATE FUNCTIONS
 * --INITIALIZATIONS THAT CAN BE DONE IMMEDIATELY
 * --INITIALIZATIONS THAT HAVE TO WAIT UNTIL THE DOM IS READY
 * --PLUGIN NAMESPACE ** PUBLIC PROPERTIES AND METHODS
 *
 */
( function(window, document, $) {


    var
        //
        // --PRIVATE VARIABLES
        //

        // see if we are serving to IE6 -- TODO: change to feature detection
        ie6 = ($.browser.msie && parseInt($.browser.version, 10) === 6),

        // the serverComm plugin stylesheet
        styleText = [
            // stop "jittering" during scrolling in IE6 [ http://www.webmasterworld.com/css/3592524.htm ]
            (ie6) ? "body { background: url(images/clear1.gif) fixed; }" : "",
            "#sc_contactServerPrompt" + "{ font-weight:bold; text-align:center; left:0px; width:100%;",
            // position:fixed -- include IE6 too
            (ie6) ? "position: absolute; top: expression((document.documentElement || document.body).scrollTop);" : "position:fixed; top:0px;",
            "}",
            "#sc_contactServerPrompt img { vertical-align:-2px; }",
            "#sc_contactServerPrompt span { cursor:default; }",
            ".sc_hand { cursor:pointer; }",
            ".sc_blue { padding:0 1em; background-color:#999; color:#8ef; }",
            ".sc_yellow { padding:0 1em; background-color:#666; color:yellow; }",
            ".sc_green { padding:0 1em; background-color:#666; color:#5f5; }",
            ".sc_red { padding:0 1em; background-color:#FD2F00; color:white; }",
            ".sc_inprocess { font-size:130%; font-weight:bold; color:white; background-color:red; padding:10px; border:solid 5px #d00; position:absolute }"
        ].join(""),

        // style element to be inserted once the DOM is ready
        styleElement = $('<style />').attr("type", "text/css"),

        // local cache for the ajax UI prompt -- it will be appended to
        // and detached from the DOM as required
        // see [ http://www.ajaxload.info/ ] for public domain animated gifs
        contactPromptElement = $("<div />", {
            id : "sc_contactServerPrompt",
            html : "<span class='sc_blue'><img /> <span></span></span>"
        }),

        // this is the part of the UI prompt that is visible to the user
        contactContainer = contactPromptElement.find("> span"),

        // usually an animated gif
        contactGear = contactPromptElement.find("img"),

        // element (span) containing the UI prompt text
        contactText = contactPromptElement.find("span span"),

        // used with setInterval()
        timerID,

        // counter for the number of attempts during for the current "connection"
        requestAttempts = 1,

        // a reference to the XHR object for the currently active ajax request
        activeRequest   = null,

        // global flag indicating when an ajax request is in-process
        // maybe different than activeRequest when an ajax connection is
        // complete but callbacks are still inprocess
        inprocess       = false,


        //
        // --PRIVATE FUNCTIONS
        //

        /**
         * giveUp() is called after all automatic attempts have failed
         *
         * @param   {String} errorMessage contains the message determined
         * in the error callback defined in contactServer()
         *
         */
        giveUp = function(errorMessage) {

            var options = $.serverComm.options;

            errorMessage = errorMessage || "";

            // invoke the giveupCallback function
            if (options.giveupCallback) {

                if ($.isFunction(options.giveupCallback)) {
                    options.giveupCallback.call();
                }
            }

            // set requestAttempts back to 1 to get ready for the next time
            requestAttempts = 1;

            contactPromptElement.fadeTo("fast", 0, function() {

                contactPromptElement.detach()
                    .removeAttr("style"); // remove the opacity value
                contactContainer.removeClass("sc_red").addClass("sc_blue")
                    .removeAttr("title");
                contactText.html(options.contactPromptText);
                contactGear.removeAttr("style")
                    .attr("src", options.contactImagePath);

                // we're done
                inprocess = false;
            });

        },

        /**
         * ajaxProblem() is called when $.ajax() returns an error
         *
         * @param   {String} errorMessage contains the message determined
         * in the error callback defined in contactServer()
         *
         * possible values for errorMessage are:
         * RETURNED BY PHP PAGE: auth failure, database failure, POST failure, Unauthorized access
         * RETURNED BY $.ajax(): timeout, "internet error" (e.g., 404),
         * RETURNED BY $.servercomm: ajax failure, unknown
         *
         */
        ajaxProblem = function(errorMessage) {

            // set the activeRequest to null so that we can tell the difference between
            // an active XHR connection (activeRequest && inprocess) and the "please try
            // again later" state (!activeRequest && inprocess)

            var options = $.serverComm.options;

            errorMessage = errorMessage || "";

            activeRequest = null;


            // invoke the errorCallback function
            if (options.errorCallback) {

                if ($.isFunction(options.errorCallback)) {
                    options.errorCallback.call(null, errorMessage, requestAttempts);
                }
            }


            // increment the request attempts counter
            requestAttempts += 1;

            // try the request automatically
            if (requestAttempts <= $.serverComm.options.autoRetrys) {

                contactContainer.removeClass("sc_blue").addClass("sc_yellow");
                contactGear.attr("src", options.problemImagePath);
                contactText.html("There's been a problem &mdash; Trying again. . . " +
                    requestAttempts + " of " + $.serverComm.options.autoRetrys);

                inprocess = false;

                // send the next request
                $.serverComm.contactServer();
            }
            // give up after the number of auto retries configured in options
            else {

                contactContainer.removeClass("sc_yellow").addClass("sc_red")
                    .attr("title", errorMessage);
                contactGear.css( { display:"none" } );
                contactText.html(options.giveupPromptText + "&nbsp;&nbsp;<img class='sc_hand' src='" + options.closeBoxImagePath + "'>");

                // bind a "ONE" click event handler to the document element...
                // whatever the user clicks on next will run giveUp() and
                // then unbind the click handler -- required to ensure that
                // the giveupCallback is invoked.
                $(document).one("click", function() {

                    if (contactPromptElement.length === 1) {

                        giveUp(errorMessage);
                    }
                });
            }
        };

    //
    // END OF var STATEMENT
    //


    //
    // --INITIALIZATIONS THAT CAN BE DONE IMMEDIATELY
    //
    // -- none so far


    //
    // --INITIALIZATIONS THAT HAVE TO WAIT UNTIL THE DOM IS READY
    //
    $(document).ready(function() {

        var options = $.serverComm.options;

        // prepend the serverComm stylesheet to the head element
        // [ http://www.phpied.com/dynamic-script-and-style-elements-in-ie/ ]
        // if this is IE
        if (styleElement[0].styleSheet) {

            styleElement[0].styleSheet.cssText = styleText;
        }
        // all other browsers
        else {

            styleElement.text(styleText);
        }

        styleElement.prependTo("head");

        // set the initial UI prompt text
        contactText.html(options.contactPromptText);

        // set the initial UI "busy" image src attribute
        contactGear.attr("src", options.contactImagePath);
    });


    //
    // --PLUGIN NAMESPACE ** PUBLIC PROPERTIES AND METHODS
    //
    $.serverComm = {

        // PUBLIC PROPERTY -- serverComm default option settings
        options : {
            
            // url that the request will be sent to
            url             : "",

            // data to be submitted to the server
            dataObject      : null,

            // how many automatic retries should be made before the user is offered manual retrys
            autoRetrys      : 4,

            // timeout value for automatic attempts to contact the server
            autoTimeout     : 7000,

            // callback for page specific processing in giveup()
            giveupCallback  : null,

            // callback for page specific processing in ajaxProblem()
            errorCallback   : null,

            // callback for page specific processing in success()
            successCallback : null,

            // prompt text for the initial "contacting" prompt
            contactPromptText : "Contacting server",

            // prompt text to be shown when auto retries are exhausted and serverComm gives up
            giveupPromptText : "The problem hasn't gone away &mdash; try again later",

            // text for prompt after an automatic retry results in a successful connection
            successPromptText : "Contacting server &mdash; SUCCESS!",

            // path to the image used in the initial "contacting" prompt
            contactImagePath : "images/busy999.gif",

            // path to the image used in the "there is a problem" prompt
            problemImagePath : "images/busy666.gif",

            // path to the image used as the close box in "giveup" prompt
            closeBoxImagePath : "images/close.gif",

            // character used to separate response status (e.g., success, database failure)
            // from data in the XHR text response string
            responseSeparator : "|"

        },

        //function to init ServerComm

        /**
         * PUBLIC METHOD
         * configure() is called to set serverComm options that will act as
         * default values for all subsequent requests.
         *
         * @param   {Object} config contains the option properties and their
         * values to be changed
         *
         */
        configure : function(config) {

            // get the user submitted configuration options for this call
            config = config || {};
            this.options = $.extend(this.options, config);

        },

        /**
         * PUBLIC METHOD
         * activeConnection() returns the current state of the XHR connection
         *
         * (!activeRequest && !inprocess) --> no active connection -- FALSE
         * (!activeRequest &&  inprocess) --> please try again prompt is showing -- FALSE
         * ( activeRequest &&  inprocess) --> active connection -- TRUE
         *
         * @return  {Boolean} containing the current state of the XHR connection.
         */
        activeConnection : function() {

            return (activeRequest && inprocess);

        },

        // display the in-process warning
        /**
         * PUBLIC METHOD
         * inprocessWarning() displays an absolute positioned prompt in the
         * middle fo the user's screen saying "Please Wait!"
         *
         * Used in conjunction with activeConnection() to prevent users
         * from starting multiple XHR connections.
         * 
         */
        inprocessWarning : function() {

            var $window = $(window),
            inprocessElement;

            inprocessElement = $("<div />", {
                className : "sc_inprocess",
                text: "Please Wait!"
            }).appendTo("body");

            // once it has been appended set the top and left properties
            inprocessElement.css ( {
                top  : ($window.height() - inprocessElement.outerHeight()) / 2 + $window.scrollTop(),
                left : ($window.width() - inprocessElement.outerWidth()) / 2 + $window.scrollLeft()
            } );

            setTimeout(function() {

                inprocessElement.fadeTo("slow", 0, function() {
                    inprocessElement.remove();
                });

            }, 1000);

        },

        /**
         * PUBLIC METHOD
         * contactServer() sends an XHR request to the server
         *
         * ALL requests use POST -- ALL requests use a text datatype
         *
         * @param   {Object} config contains the option properties and their
         * values to be changed
         *
         */
        contactServer : function(config) {

            // get the user submitted configuration options
            config = config || {};
            this.options = $.extend(this.options, config);

            //see if there is an active xhr request -- if so wait
            timerID = setInterval(function() {

                if (!activeRequest && !inprocess) {

                    clearInterval(timerID);

                    inprocess = true;

                    // show the "contacting" server prompt
                    if (requestAttempts === 1) {

                        contactText.html($.serverComm.options.contactPromptText);
                        contactGear.attr("src", $.serverComm.options.contactImagePath);
                    }
                    contactPromptElement.appendTo("body");

                    try {

                        activeRequest = $.ajax( {

                            url      : $.serverComm.options.url,
                            type     : "POST",
                            data     :  $.serverComm.options.dataObject,
                            datatype : "text",
                            timeout  : $.serverComm.options.autoTimeout,

                            error : function(xhr, jMessage, e) {

                                var errorMessage = "";

                                if (jMessage === "timeout") {

                                    errorMessage = jMessage;
                                }

                                else {

                                    if (jMessage === "error") {

                                        //errorMessage = xhr.status + ": " + xhr.statusText;
                                        errorMessage = "internet error";
                                    }

                                    else {

                                        errorMessage = "unknown";
                                    }
                                }

                                ajaxProblem(errorMessage);
                            },

                            success : function(response, status) {

                                var options = $.serverComm.options,
                                    responseStatus;

                                // the response text string can have multiple components separated
                                // by $.serverComm.options.responseSeparator the first component MUST
                                // be the return status (e.g., "success", "database failure")
                                // the subsequent component can be data returned in the response string

                                responseStatus = (response.indexOf(options.responseSeparator) === -1) ? response : response.split("|")[0];

                                // if the server-side script has returned an error
                                if (responseStatus !== "success") {

                                    ajaxProblem(responseStatus);

                                }
                                // else there have been auto retries resulting in a success or the first connection was a success
                                else {
                                    // mulitple with success
                                    if (requestAttempts > 1) {

                                        activeRequest = null;
                                        contactPromptElement.detach();
                                        contactGear.css( { display:"none" } );
                                        contactContainer.removeClass("sc_yellow").addClass("sc_green");
                                        contactText.html(options.successPromptText);
                                        contactPromptElement.appendTo("body");

                                        // give the user a chance to see the success prompt
                                        setTimeout(function() {

                                            contactPromptElement.fadeTo("slow", 0, function() {

                                                // reset the "contacting" prompt for the next request
                                                contactPromptElement.detach()
                                                    .removeAttr("style"); // remove the opacity value
                                                contactContainer.removeClass("sc_green").addClass("sc_blue");
                                                contactText.html(options.contactPromptText);
                                                contactGear.removeAttr("style")
                                                    .attr("src", options.contactImagePath);

                                                // set the in process global flag back to false
                                                inprocess = false;
                                            });

                                        }, 2000);

                                    }
                                    // else the original attempt was a success
                                    else {

                                        activeRequest = null;

                                        setTimeout(function() {

                                            contactPromptElement.fadeTo("slow", 0, function() {

                                                contactPromptElement.detach()
                                                    .removeAttr("style"); // remove the opacity value

                                                // set the in process global flag back to false
                                                inprocess = false;
                                            });

                                        }, 750);

                                    }

                                    // invoke the successCallback function
                                    if ($.serverComm.options.successCallback) {

                                        if ($.isFunction($.serverComm.options.successCallback)) {
                                            $.serverComm.options.successCallback.call(null, response, requestAttempts);
                                        }
                                    }

                                    // we are done
                                    // set requestAttempts back to 1 in case there were multiple requests
                                    requestAttempts = 1;
                                }
                            }
                        });

                    } // end of try block here

                    catch (e) {

                        //alert(e.name + ":" + e.message);
                        ajaxProblem("ajax failure");
                    }
                }

            }, 100);
        }
    };

}(window, document, jQuery) );


