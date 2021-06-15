/*
 * library that manages user interface actions and display
 * $Id: detail_page.js 73 2011-02-14 01:05:36Z Fix_J $ 
 */

/**
 * Génération du graphique Traffic History
 */
function drawGraph(res){
        var d = urls.details_body;
        
        var data = new google.visualization.DataTable();
        var hAxisFormat = '';
        var min = '';
        var max = '';
        
        // Récupération des valeurs min et max calculées
        if(d.min != null && d.max != null){
            
            min = parseDate(d.min);
            max = parseDate(d.max);
        }
        
        // Définition du format de données pour l'axe horizontal en fonction de la période
        if(d.resolution[res]=='2h' || d.resolution[res]=='day'){
            
            hAxisFormat = 'HH:mm';
            data.addColumn('datetime', 'Time');
             
        } else if(d.resolution[res]=='month' || d.resolution[res]=='week'){
            
            hAxisFormat = 'd MMM';
            data.addColumn('date', 'Time');
            
        } else {
            
            hAxisFormat = 'MMM yyyy';
            data.addColumn('date', 'Time');
        }
        
        data.addColumn('number', 'Clicks');
                        
        // Alimentation de la table de données
        $.each(d.traffic, function(k,v){
            
            var dateEchelle = parseDate(v.echelle);
            data.addRow([dateEchelle, parseInt(v.clicks)]); 
        });
        
        // Calcul du nombre de palier et du format pour l'axe vertical
        var vGridlines = 3;
        var format = 0;
        if(data.getNumberOfRows() > 10){
            
            vGridlines = 5;
            
        } else if(data.getNumberOfRows() == 0){
            
            vGridlines = 0;
        
        } else if(data.getNumberOfRows() > 0 && data.getNumberOfRows() <= 2){
            
            format = '#.#';
        }

        // définition des options du graphique
        var options = {
            hAxis:{format:hAxisFormat,viewWindow:{min:min,max:max},minorGridlines:{count:5}},
            vAxis:{minValue:0,format:format,gridlines:{count:vGridlines}},
            pointSize:2
        };
       
        var chart = new google.visualization.LineChart(document.getElementById('chartDiv1'));
        
        chart.draw(data,options);
        
    }

/**
 * Extraction shorturl de l'URL
 */
function getShorturl(){
    
    var path = location.pathname;
    var length = path.length;
    
    return path.substr(1,length - 2);
}

/**
 * Création de date pour la génération du graphique
 */
function parseDate(dateToParse){
    
    var dateTime = dateToParse.split(' ');
    
    var date = dateTime[0].split('-');
    
    if(dateTime[1] != null){
        
        var time = dateTime[1].split(':');
    }
    
    var year = date[0];
    var month = date[1];
    var day = date[2];
    
    var hours = 0;
    var minutes = 0;
    var seconds = 0;
    
    if(time != null){
        
        hours = time[0];
        minutes = time[1];
        seconds = time[2];
    }
    
    var res = new Date();
    res.setDate(day);
    res.setMonth(month-1);
    res.setYear(year);
    res.setHours(hours, minutes, seconds);
    
    return res;
}

/**
 * Fonction de création d'une table de la page détail 
 */
function buildTable(data, typeTable){
    
    var content;
    var tabSelector = '#'+typeTable+' tbody tr';
    $(tabSelector).not(".first").remove();

    var others = "";
    $.each(data, function(key, value){
        
        if(value.item != "Others"){
                
            if(typeTable == "referrers"){
                
                if(value.item != "direct" && parseInt(value.clicks) >= 0) {
                    
                    content = content+"<tr><td class='td_detail'><a class='referrer_link' href='"+value.item+"'>"+value.item.replace(/^https?:\/\//, '')+"</a></td><td>"+value.clicks+"</td></tr>";
                    
                } else {
                    
                    content = content+"<tr><td class='td_detail'>"+value.item+"</td><td>"+value.clicks+"</td></tr>";
                }
                    
            } else if(typeTable == "shorturls") {
                
                content = content+"<tr><td class='td_detail'><a class='referrer_link' href='"+value.site+"/"+value.item+"+'>"+value.item+"</a></td><td>"+value.clicks+"</td></tr>";
        
            } else {
                
                content = content+"<tr><td class='td_detail'>"+value.item+"</td><td>"+value.clicks+"</td></tr>";
            }
                
        } else {
                
            others = "<tr><td class='td_detail'>"+value.item+"</td><td>"+value.clicks+"</td></tr>";
        }
    });
    
    if(others != ""){
            
        content = content+others;
    }
    
    var outputSelector = '#'+typeTable+' tbody';
    $(outputSelector).not(".first").append(content);
}

var DEBUG = false;

var urls = {
    apipage: "/site/api.php"
};


urls.details_body = {
    countries: [],
    referrers: [],
    traffic: [],
    platforms: [],
    browsers: [],
    shorturls: [],
    resolution: ['2h', 'day', 'week', 'month', 'all'],
    min:'',
    max:'',
    defaultresolution: "all",

    _render: function(res) {
        
        var d = urls.details_body;
        
        // construction des blocs
        buildTable(d.countries,'countries');
        buildTable(d.platforms,'platforms');
        buildTable(d.referrers,'referrers');
        buildTable(d.browsers,'browsers');
        
        if(d.shorturls.length > 0){
           
           $("#shorturls").show();
           buildTable(d.shorturls,'shorturls');
        }
                
        // génération du graphique
        drawGraph(res);
    },
    // load data
    load: function(res, shorturl, element) {
        
        if (shorturl == "") {
            
            urls.message.show("shorturl parameter is missing", "error");
        
        } else {
            
            var d = urls.details_body;
            $.get(urls.apipage,
                {
                    action:"stats_period_url",
                    filter:"last",
                    period:d.resolution[res],
                    shorturl:shorturl,
                    format: "json"
                },
                function(data) {              
                    
                    if(data.statusCode != 200){
                        
                        urls.message.show("Error while retrieving data", "error");
                    
                    } else {
                        
                        var r = [];
                        $.each(data.countries, function(k, v) {r.push(v);});
                        d.countries = [];
                        d.countries = d.countries.concat(r);

                        r = [];
                        $.each(data.referrers, function(k, v) {r.push(v);});
                        d.referrers = [];
                        d.referrers = d.referrers.concat(r);

                        r = [];
                        $.each(data.traffic, function(k, v) {
                            if(!isNaN(parseInt(v.clicks))){

                                r.push(v);
                            }

                        });
                        d.traffic = [];
                        d.traffic = d.traffic.concat(r);

                        r = [];
                        $.each(data.platforms, function(k, v) {r.push(v);});
                        d.platforms = [];
                        d.platforms = d.platforms.concat(r);

                        r = [];
                        $.each(data.browsers, function(k, v) {r.push(v);});
                        d.browsers = [];
                        d.browsers = d.browsers.concat(r);
                        
                        r = [];
                        if(data.shorturls) {
                            
                            $.each(data.shorturls, function(k, v) {r.push(v);});
                            d.shorturls = [];
                            d.shorturls = d.shorturls.concat(r);
                        }
                                                
                        d.min = data.min;
                        d.max = data.max;

                        d._render(res);
                        
                        if(element){
                            
                            $('#resolutions a').attr('class','');
                            $(element).attr('class', 'selected');
                        }
                        
                        
                    }
                }
            );
            
        }     
        
    }
    
};

$(document).ready(function(){
       
    urls.details_body.load(4,getShorturl());
    $("#shorturls").hide();
    
    $("a.info").live("mouseover mouseout", function() {
        ($(Event).type == 'mouseover')
            ? $(this).addClass("underline")
            : $(this).removeClass("underline");
    });

    // event handler when x close button is clicked
    $("#message_dismiss").click(function(){
        urls.message.hide();
    });

    // event handler when ajax error
    $('#message').ajaxError(function(e, xhr, settings, exception) {
        // TODO: manage several cases (shortening didn't work; problem retrieving history)
        urls.message.show(xhr.response, "error");
        //$("#shorten_pending").attr({style: "visibility: hidden;"});
    });
 });