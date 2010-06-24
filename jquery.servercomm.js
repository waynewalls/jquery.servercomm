/**
 *  jQuery.servercomm plugin -- Simple UI for $.ajax()
 *  Copyright (c) 2010 Wayne Walls - wfwalls(at)gmail(dot)com
 *  License: MIT License or the GNU General Public License (GPL) Version 2
 *  Date:
 *  @author Wayne Walls
 *  @version
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


    //
    // --PRIVATE VARIABLES
    //
    var ie6 = ($.browser.msie && parseInt($.browser.version, 10) === 6),

        // the serverComm stylesheet
        styleText = [
            // stop "jittering" during scrolling in IE6 [ http://www.webmasterworld.com/css/3592524.htm ]
            (ie6) ? "body { background: url(images/clearSpacer.gif) fixed; }" : "",
            "#sc_contactServerPrompt" + "{ font-weight:bold; text-align:center; left:0px; width:100%;",
            // position:fixed -- include IE6 too
            (ie6) ? "position: absolute; top: expression((document.documentElement || document.body).scrollTop);" : "position:fixed; top:0px;",
            "}",
            "#sc_contactServerPrompt img { vertical-align:-2px; }",
            ".sc_hand { cursor:pointer; }",
            ".sc_blue { padding:0 1em; background-color:#999; color:#8ef; }",
            ".sc_yellow { padding:0 1em; background-color:#666; color:yellow; }",
            ".sc_green { padding:0 1em; background-color:#666; color:#5f5; }",
            ".sc_red { padding:0 1em; background-color:#444; color:#f66; }",
            ".sc_inprocess { font-size:130%; font-weight:bold; color:white; background-color:red; padding:10px; border:solid 5px #d00; position:absolute }"
        ].join(""),

        // style element to be inserted once the document is ready
        styleElement = $('<style />').attr("type", "text/css"),

        // local cache for the ajax UI prompt -- it will be appended to
        // and detached from the DOM as required
        // see [ http://www.ajaxload.info/ ] for public domain animated gifs
        contactPromptElement = $("<div />", {
            id : "sc_contactServerPrompt",
            html : "<span class='sc_blue'><img src='busy999.gif'> <span>Contacting server</span></span>"
        }),

        contactContainer = contactPromptElement.find("> span"),

        contactGear = contactPromptElement.find("img[src*=busy]"),

        contactText = contactPromptElement.find("span span"),

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
        // function called after all automatic attempts have failed
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
                contactContainer.removeClass("sc_red").addClass("sc_blue");
                contactText.text("Contacting server");
                contactGear.removeAttr("style")
                    .attr("src", "busy999.gif");

                // we're done
                inprocess = false;
            });

        },

        // function called when $.ajax() returns an error
        ajaxProblem = function(errorMessage) {

            // possible values for errorMessage are:
            // RETURNED BY PHP PAGE: auth failure, database failure, POST failure, Unauthorized access
            // RETURNED BY $.ajax(): timeout, "internet error" (e.g., 404),
            // RETURNED BY $.servercomm: ajax failure, unknown

            // set the activeRequest to null so that we can tell the difference between
            // an active XHR connection (activeRequest && inprocess) and the "please try
            // again later" state (!activeRequest && inprocess)

            errorMessage = errorMessage || "";

            console.log(errorMessage);

            activeRequest = null;

            // increment the request attempts counter
            requestAttempts += 1;

            // try the request automatically
            if (requestAttempts <= $.serverComm.options.autoRetrys) {
                contactContainer.removeClass("sc_blue").addClass("sc_yellow");
                contactGear.attr("src", "busy666.gif");
                contactText.html("There's been a problem &mdash; Trying again. . . " +
                    requestAttempts + " of " + $.serverComm.options.autoRetrys);

                inprocess = false;

                // send the next request
                $.serverComm.contactServer();
            }
            // give up after the number of auto retries configured in options
            else {
                contactContainer.removeClass("sc_yellow").addClass("sc_red");
                contactGear.css( { display:"none" } );
                contactText.html("The problem hasn't gone away &mdash; try again later&nbsp;&nbsp;<img class='sc_hand' src='close.gif'>");

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
    // --INITIALIZATIONS THAT CAN BE DONE IMMEDIATELY
    //
    // -- none so far


    //
    // --INITIALIZATIONS THAT HAVE TO WAIT UNTIL THE DOM IS READY
    //
    $(document).ready(function() {

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

    });


    //
    // --PLUGIN NAMESPACE ** PUBLIC PROPERTIES AND METHODS
    //
    $.serverComm = {

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

            // callback for page specific processing in success()
            successCallback : null
        },

        //function to init ServerComm
        init   : function(callback) {
            // do page specific initialization stuff in callback
            if (callback) {
                if ($.isFunction(callback)) {
                    callback.call();
                }
            }
        },

        activeConnection : function() {

            // (!activeRequest && !inprocess) --> no active connection -- FALSE
            // (!activeRequest && inprocess) --> please try again prompt is showing -- FALSE
            // (activeRequest && inprocess) --> active connection -- TRUE
            return (activeRequest && inprocess);
        },

        // display the in-process warning
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

        // function called to send an ajax request to the server
        contactServer : function(config) {

            // get the user submitted configuration options for this call
            config = config || {};
            this.options = $.extend(this.options, config);

            //see if there is an active xhr request -- if so wait
            timerID = setInterval(function() {

                console.log(activeRequest, inprocess, requestAttempts);

                if (!activeRequest && !inprocess) {

                    clearInterval(timerID);

                    inprocess = true;

                    // show the "contacting" server prompt
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

                                if (response !== "success") {

                                    ajaxProblem(response);

                                }

                                else {

                                    // if there were multiple attempts to contact the server -- put up a success message
                                    if (requestAttempts > 1) {

                                        activeRequest = null;
                                        contactPromptElement.detach();
                                        contactGear.css( { display:"none" } );
                                        contactContainer.removeClass("sc_yellow").addClass("sc_green");
                                        contactText.html("Contacting server &mdash; SUCCESS!");
                                        contactPromptElement.appendTo("body");

                                        // give the user a chance to see the success prompt
                                        setTimeout(function() {

                                            contactPromptElement.fadeTo("slow", 0, function() {

                                                // reset the "contacting" prompt for the next request
                                                contactPromptElement.detach()
                                                    .removeAttr("style"); // remove the opacity value
                                                contactContainer.removeClass("sc_green").addClass("sc_blue");
                                                contactText.text("Contacting server");
                                                contactGear.removeAttr("style")
                                                    .attr("src", "busy999.gif");
                                            });

                                        }, 3000);

                                    }

                                    else {

                                        activeRequest = null;

                                        setTimeout(function() {

                                            contactPromptElement.fadeTo("slow", 0, function() {
                                                contactPromptElement.detach()
                                                    .removeAttr("style"); // remove the opacity value
                                            });

                                        }, 1000);

                                    }

                                    // invoke the successCallback function
                                    if ($.serverComm.options.successCallback) {
                                        if ($.isFunction($.serverComm.options.successCallback)) {
                                            $.serverComm.options.successCallback.call();
                                        }
                                    }

                                    // we are done
                                    // set requestAttempts back to 1 in case there were multiple requests
                                    requestAttempts = 1;
                                    // set the in process global flag back to false
                                    inprocess = false;
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

