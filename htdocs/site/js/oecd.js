/*
 * library that manages user interface actions and display
 * $Id$ 
 */

var DEBUG = false;

var urls = {
    apipage: "/site/api.php",
    pagesize: 15,
    rowtpl: $.template( null,
    "<tr class='history_row'>" +
        "<td class='url first'><div><a class='info' title='${title}' href='${url}'>${$item.noproto(url)}</a></div></td>" +
        "<td class='url'><div><a class='info' title='${title}' href='${shorturl}'>${$item.noproto(shorturl)}</a></div></td>" +
        "<td>${$item.parsedate(timestamp)}</td>" +
        "<td class='clicks'><a href='${shorturl}+'>${$item.clicks(clicks)}</a></td>" +
        "<td><a class='details' href='${shorturl}+'><div id='${$item.getid(shorturl)}'>${$item.spark(shorturl)}</div></a></td>" +
        "<td><a class='details' href='${shorturl}+'>Details&nbsp;»</a></td>" +
    "</tr>")


};

function clearSearch(){
    $("#search").val("");
}

/**
 * Affichage de la sparkline pour chaque ligne
 */
function drawSpark(){
    
    var h = urls.history;
    
    $.get(urls.apipage,
            {
                action:"stats_traffic",
                urls:h.shorturls,
                period:h.resolution[h.currentresolution],
                start: h.links.length,
                limit: h.batchsize,
                namespace: $('#current_namespace').val(),
                format: "json"
            },
            function(data) {              
                
                if(data.statusCode != 200){
                    
                    urls.message.show("Error while retrieving data", "error");
                    
                } else {
                 
                    if(data.traffic){
                    
                        var r = [];
                        $.each(data.traffic, function(k, v) {

                            r[k] = v;

                        });                                  

                        var selector = "";
                        $.each(h.shorturls, function(key, value){

                            selector = '#'+value;

                            if(r[value]){

                                c = r[value];

                                data = []
                                $.each(c, function(k, v){

                                    data.push(v.clicks);                            
                                })
                                
                                var cptFiller = data.length;
                                while(cptFiller < 20){
                                    
                                    data.push(0);
                                    cptFiller++;
                                }
                                
                                $(selector).sparkline(data,{type: 'line', barColor: 'blue'});
                            
                            } else {
                                
                                $(selector).sparkline([0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],{type: 'line', barColor: 'blue'});
                            }                                                

                        });
                    
                    } 
                
                }
          }
        );
    
    
    
}

urls.history = {
    batchsize: 150,         // get 10 pages of history in one go
    batchstart: 0,
    total: 0,               // contains total links in database
    links: [],              // holds actual retrieved history link data
    shorturls: [],
    resolution: ['2h', 'day', 'week', 'month', 'all'],
    currentresolution: 4,
    defaultresolution: 4,
    namespace: 'get_all',
    loadingrow: '<tr id="history_pending"><td colspan="6"><img id="pagination_pending" src="/site/spin_16_e5ecf9.gif" alt=""> Loading history ... </td></tr>',

    _render: function(pagetoshow) {

        var h = urls.history;
        var start = pagetoshow * urls.pagesize;
        var end = start + urls.pagesize;
        $("#no_data").hide();
        $("#history tbody .history_row").empty().remove();
        $("#history_pending").hide();
        
        h.shorturls = [];
        
        $.tmpl( urls.rowtpl, h.links.slice(start, end), {
            noproto: function (str) {
                return str.replace(/^https?:\/\//, '')
            },
            parsedate: function(str) {
                return Date.parse(str).toString("d MMM yyyy")
            },
            clicks: function(str) {
                return str;
            },
            spark: function(str) {
                
                return "";
            },
            getid: function(str){
                
                var s = str.split("/");
                
                h.shorturls.push(s[3]);
                
                return s[3];
            }
        }).appendTo($("#history tbody"));
        //DEBUG ? urls.message.show("links: " + h.links.length + " - total: " + h.total + " - currentpage: " + pagetoshow) : null;
                
        urls.pager.update(pagetoshow, h.total);
        
        if(h.total == 0){
            
            $("#no_data").show();
        }
        
        drawSpark();
    },
 
    // display data
    show: function(pagetoshow) {
        var h = urls.history;
        var nextpage = pagetoshow * urls.pagesize + urls.pagesize;
        if (h.links.length <= 0 || (nextpage > h.links.length && nextpage < h.total)) {
            //DEBUG ? urls.message.show("need to load remote data", "debug") : null;
            h.load(pagetoshow,h.currentresolution,null,$("#search").val());
        } else {
            h._render(pagetoshow);
        }
},
    // load data
    load: function(pagetoshow,resolution,linkClicked,search) {
        var h = urls.history;
        
        // A click on one of the period buttons won't clear the search anymore
        /*
        if(linkClicked){
            clearSearch();
        }
        */
        
        // Update current_resolution
        if (linkClicked) {
            $('#current_resolution').val(resolution);
            
            // If the namespaces are in a drop-down element, update the menu value and close it
            if ($('.menu-button').length) {
                $('.menu-button').html($(this).html()+'&nbsp;&#9660;'); // Dropdown triangle
                dropdown_hide();
            }
        }
        
        // Send AJAX request
        $.get(urls.apipage,
        {
            action: "stats_period",
            filter: "last",
            period: h.resolution[resolution],
            search: search,
            start: h.links.length,
            limit: h.batchsize,
            namespace: $('#current_namespace').val(),
            format: "json"
        },
        function(data)
        {
                
            // Error handling
            if(data.statusCode != 200) {
                urls.message.show("Error while retrieving data", "error");
            }
            else
            {
                h.currentresolution = resolution;
                
                if(data.stats.total_links > 0)
                {
                    var r = [];
                    $.each(data.links, function(k, v) {r.push(v);});
                    h.total = data.stats.total_links;
                    h.links = h.links.concat(r); // now we have an array of max 150 link objects.
                    h._render(pagetoshow);
                    //DEBUG ? urls.message.show("remote data retrieved successfully", "debug") : null;        
                }
                else
                {
                    h.links = [];
                    h.total = 0;
                    h._render(pagetoshow);
                }
                
                // Appearance of click statistics buttons
                if (linkClicked) {
                    $('#resolution_buttons button').removeClass('button-selected');
                    $(linkClicked).addClass('button-selected');
                }
                
             	// Appearance of namespaces buttons
                $('#namespace_filter button').removeClass('button-selected');
                $('#namespace_'+$('#current_namespace').val()).addClass('button-selected');
            }
    	});
    },
    addrow: function(data) {
        $.tmpl( urls.rowtpl, [data], {
            noproto: function (str) {
                return str.replace(/^https?:\/\//, '')
            },
            parsedate: function(str) {
                return "seconds ago";
            },
            clicks: function(str) {
                return "0";
            }
        }).insertAfter("#history_pending").addClass("na");
        this.hiderow();
        urls.pager.update(urls.pager.currentpage, urls.history.total);
    },
    hiderow: function() {
        while($(".history_row").length > urls.pagesize)
            $(".history_row:last").remove();
    }
};

urls.pager = {
    currentpage: -1, // zero-based!
    nextbtn: $("#older"),
    prevbtn: $("#newer"),
    update: function(firstItem, total) {
        var p = urls.pager;
        p.currentpage = firstItem;
        displaypage = p.currentpage + 1;
        total = Math.ceil(total / urls.pagesize);
        
        // display (or not) pagination links
        (p.currentpage <= 0) ? $("#newer").hide() : $("#newer").show();
        (displaypage >= total) ? $("#older").hide() : $("#older").show();

        $("#current_page_displayed").html(displaypage);  // add one for display
        $("#total_pages").html(total);
    }
};

$(document).ready(function(){
    $("#shorten").focus();
    urls.history.show(0);
    $("#no_data").hide();
    
    // $("#history").tablesorter();
    
    // Not giving the desired result, moved to CSS
    /*
    $("a.info").live("mouseover mouseout", function() {
        ($(Event).type == 'mouseover')
            ? $(this).addClass("underline")
            : $(this).removeClass("underline");
    });
    */

    // event handler for next/prev pager clicks
    $("#newer").click(function() {
        //DEBUG ? urls.message.hide() : null;
        urls.history.show(urls.pager.currentpage - 1);
    });
    $("#older").click(function(){
        //DEBUG ? urls.message.hide() : null;
        urls.history.show(urls.pager.currentpage + 1);
    });

    // event handler when x close button is clicked
    $("#message_dismiss").click(function(){
        urls.message.hide();
    });

    $("#keyword").click(function() {
        $(this).select();
    });
    // shortening event handler
    $("#shorten_form").submit(function() {

        if ($("#shorten").val() == "") {
            urls.message.show("Please enter a URL before pressing the Shorten button.", "error");
            return false;
        }

        var struct = {
            action: "shorturl",
            url: $("#shorten").val(),
            format: "json"
        };
        
        var kw = $("#keyword").val();
        if (kw != "..." && kw.length > 0) {
            struct.keyword = kw;
        }
        
        $("#shorten_pending").attr({style: "visibility: visible;"})
        $.post(urls.apipage, struct,
                        
        // on success:
        function(data) {
            if (data.status == "fail") {
                urls.message.show(data.message, "warn");
            } else {
                $("#keyword").val(data.url.keyword);
                urls.message.show(data.message, "info")
                // TODO: test this approach!
                var newlink = {
                    shorturl: data.shorturl,
                    url: data.url.url,
                    clicks: "0",
                    title: data.url.title,
                    timestamp: data.url.date,
                    ip: data.url.ip
                };
                urls.history.links.unshift(newlink);
                urls.history.total++;
                urls.history.addrow(newlink);
            }
            $("#shorten_pending").attr({style: "visibility: hidden;"});
        }, "json");
        return false; // do not submit the form
    });
    
    $("#search_form").submit(function(){
        
        // Not needed anymore because of namespaces
        /*
        if ($("#search").val() == "") {
            urls.message.show("Please type something before pressing the Search button.", "error");
            return false;
        }
        */
        
        var resolution = $('#current_resolution').val();
        
        urls.history.links = [];
        urls.history.load(0,resolution,null,$("#search").val());
        
        return false;
        
    });

    // event handler when ajax error
    $('#message').ajaxError(function(e, xhr, settings, exception) {
        // TODO: manage several cases (shortening didn't work; problem retrieving history)
        urls.message.show(xhr.response, "error");
        $("#shorten_pending").attr({style: "visibility: hidden;"});
    });
    
    /** Action done when the user clicks on a namespace selector in line */
    $('#namespace_filter .button').click(function() {
        var namespace = $(this).attr('id').substring(10);
        
        // Update the current_namespace field with namespace
        $('#current_namespace').val(namespace);
        
     // If the namespaces are in a drop-down element, update the menu value and close it
        if ($('.menu-button').length) {
            $('.menu-button').html($(this).html()+'&nbsp;&#9660;'); // Dropdown triangle
            dropdown_hide();
        }
        
        // Submit the search form
        $("#search_form").submit();
    });
    
    /** Action done when the user clicks on the dropdown button */
    $('.menu-button').click( function() {
        // Check of visibility
        if ( $( this ).hasClass( 'menu-button-selected' ) ) {
            dropdown_hide();
        }
        else {
            dropdown_show();
        }
    });
 });

/** Show the drop-down menu for the namespaces */
function dropdown_show()
{
    $('#namespace_dropdown').show();
    var button = $( '.menu-button' );
    button.addClass( 'menu-button-selected' );
    button.html( button.html().substr( 0, button.html().length - 1 ) + '&#9650;' ); // Upwards triangle
}

/** Hide the drop-down menu for the namespaces */
function dropdown_hide()
{
    $('#namespace_dropdown').hide();
    var button = $('.menu-button');
    button.removeClass('menu-button-selected');
    button.html( button.html().substr( 0, button.html().length - 1 ) + '&#9660;' ); // Downwards triangle
}
