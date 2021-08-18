var biblegateway;
var lutherald; 
var psalter; 
var festivals; 

jQuery(document).ready(function () {
    biblegateway = new BibleGateway('NKJV');
    biblegateway.loadAllVerses();

    lutherald = new LutheranHerald(jQuery('[data-date]').attr('data-date')); 
    lutherald.getDevotions(); 

    psalter = new Psalter();
    psalter.bind(); 

    festivals = new Festivals();
    festivals.init();
});

class Psalter{
    constructor(){
        
    }

    bind(){
        jQuery('#psalter-selection a').click(function(event){
            event.preventDefault();
            //Get the psalm value
            var psalm = jQuery(this).html();
            jQuery('#psalter-title').html(psalm);
            jQuery('#psalm-display').html('<p data-bible="'+psalm + '">Loading Psalm...</p>');
            biblegateway.getVerse(psalm);
        });
    }
}

class BibleGateway {

    constructor(version) {
        this.version = version;

    }

    getURL(verse) {
        verse = encodeURIComponent(verse);
        return location.origin + '/wp-content/plugins/lutheran-herald/bible-reading-api.php?lookup=' + verse;
    }

    loadAllVerses() {
        var verses = [];
        jQuery.each(jQuery('[data-bible]'), function (index, item) {
            var verse = item.dataset.bible;
            verses.push(verse);
        });
        for (var i = 0; i < verses.length; i++) {
            this.getVerse(verses[i]);
        }

    }

    getVerse(verse) {
        jQuery.get(this.getURL(verse), function (data) {
            jQuery('[data-bible="' + verse + '"]').html(data);
        });
    }
}

class LutheranHerald {
    constructor(date){
        this.date = date; 
    }

    static getDate (){
        return jQuery('[data-date]').attr('data-date');
    }


    getDevotions() {
        var date = this.date;
        var url = this.getURL(date);
        jQuery.getJSON(url, function (data) {
            if (data.length < 1) { 
                console.log('No devotions found');
                return;
            }
            jQuery.each(data, function(key, val){
                var entrydate = val['date']; 
                console.log(entrydate);
                entrydate = entrydate.split('T')[0];

                if(entrydate == date){
                    LutheranHerald.renderDevotions(val['content']['rendered'], val['link']);         
                } else {
                    console.log(val);
                }
            });
            

        });
    }

    static renderDevotions(content, url){
        var elements = jQuery(content);
        var header = jQuery('strong:first', elements);
        
        var output = '<i>' +header.html() + '</i>';

        //Split the content
        var devotionalContent = content.split('<strong>Devotion</strong>')[1];
        var jDContent = jQuery(devotionalContent);
        var paragraphs = jQuery('div._1mf span', jDContent); 
        
        if(paragraphs.length === 0){
            jDContent.each(function(index){
                if(this.length > 0){
                    console.log(this);
                } else {
                    var p = this.innerHTML; 
                    if(!p.includes('Scripture taken from the New King James')){
                        output += "<p>" + this.innerHTML + "</p>";
                    }
                    
                }
            })


        } else{
            jQuery.each(paragraphs, function(i, c){
            var para = jQuery(c).html();
            if(para.length > 6){
                output += '<p>' + para + '</p>'; 
            } else{
                console.log(para);
            }
            
        });
        }

        output += '<a href="'+ url + '" target="_blank">' + "Read more </a>" ;
        jQuery('[data-date]').html(output);
    }

    getURL(date) {
        date = date + 'T23:59:00';
        return location.protocol + '//eldona.org/wp-json/wp/v2/posts?before=' + date;
    }
}

class Festivals{
    constructor(){
        
    }

    init(){
        var loaderbutton = jQuery('#festival-loader');

        this.state = 'default'; 
        let that = this; 

        var introit = jQuery('.tlh-lectionary #introit').prop('outerHTML');

        if(loaderbutton.length){
            loaderbutton.click(function(event){
                event.preventDefault();

                var lectionJSON = JSON.parse(document.getElementById('lection-data').textContent);
                var festivalJSON = JSON.parse(document.getElementById('festival-data').textContent);
                var json = lectionJSON;
                var otherJSON = festivalJSON;
                
                //If default, button click will load feasts
                if(that.state == 'default'){
                    json = festivalJSON;
                    otherJSON = lectionJSON;
                    that.state = 'festival';
                } else {
                    that.state = 'default';
                }
                loaderbutton.html('Load readings for ' + otherJSON.display);
                
                Festivals.loadFeast(json, introit);
            });
        }
    }

    static loadFeast(json, psalms){
        console.log(json);
        var display = json.display; 
        var firstreading = json.readings['epistle'];
        var secondreading = json.readings['gospel'];

        if(!firstreading){
            firstreading = json.readings[0]; 
            secondreading = json.readings[1];
        }

        var color = json.color;
        var collect = json.collect; 
        var introit = json.introit; 
        var gradual = json.gradual; 

        var lectionary = '.tlh-lectionary ';
        //Change title 
        jQuery(lectionary + '#display').html(display);

        //Change color 
        var colorElement = jQuery(lectionary + '#color');
        colorElement.html('Liturgical Color: ' + Festivals.capitalizeFirst(color));
        colorElement.removeClass();
        colorElement.addClass(color);

        var headerTag = jQuery(lectionary + '#first-reading').children().eq(0).prop('tagName');
        headerTag = headerTag.toLowerCase();
        
        //Set the readings
        jQuery(lectionary + '#first-reading ' + headerTag).html('First Reading: ' + firstreading);
        jQuery(lectionary + '#first-reading p').attr('data-bible', firstreading);

        jQuery(lectionary + '#second-reading ' + headerTag).html('Second Reading: ' + secondreading);
        jQuery(lectionary + '#second-reading p').attr('data-bible', secondreading);

        //Set the gradual
        if(gradual){
            jQuery(lectionary + '#gradual').html('<' + headerTag  +'>Gradual</' + headerTag +'>' + gradual); 
        } else {
            jQuery(lectionary + '#gradual').html('');
        }
        

        //Set the collect 
        jQuery(lectionary + '#collect p').html(collect);
        

        //Set the introit 
        if(introit){
            jQuery(lectionary + '#introit').html('<' + headerTag + '>Introit</' +headerTag + '><div>' + introit + '</div>');
        } else{
            jQuery(lectionary + '#introit').html(psalms);
            psalter.bind();
        }
        
        

        biblegateway.loadAllVerses();
    }
    


    static capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
      


}