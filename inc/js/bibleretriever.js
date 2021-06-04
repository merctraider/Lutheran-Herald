var biblegateway;
var lutherald; 
var psalter; 

jQuery(document).ready(function () {
    biblegateway = new BibleGateway('NKJV');
    biblegateway.loadAllVerses();

    lutherald = new LutheranHerald(jQuery('[data-date]').attr('data-date')); 
    lutherald.getDevotions(); 

    psalter = new Psalter();
    psalter.bind(); 
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
        jQuery.getJSON(this.getURL(date), function (data) {
            if (data.length < 1) { 
                console.log('No devotions found');
                return;
            }
            jQuery.each(data, function(key, val){
                var entrydate = val['date']; 
                console.log(entrydate);
                entrydate = entrydate.split('T')[0];

                if(entrydate == date){
                    LutheranHerald.renderDevotions(val['content']['rendered']);         
                } else {
                    console.log(val);
                }
            });
            

        });
    }

    static renderDevotions(content){
        var elements = jQuery(content);
        var header = jQuery('div._1mf strong:first', elements); 
        var output = '<i>' +header.html() + '</i>';

        //Split the content
        var devotionalContent = content.split('<strong>Devotion</strong>')[1];
        var jDContent = jQuery(devotionalContent);
        var paragraphs = jQuery('div._1mf span', jDContent); 

        jQuery.each(paragraphs, function(i, c){
            var content = jQuery(c).html();
            if(content.length > 6){
                output += '<p>' + content + '</p>'; 
            }
            
        });
        jQuery('[data-date]').html(output);
    }

    getURL(date) {
        date = date + 'T23:59:00';
        return location.protocol + '//eldona.org/wp-json/wp/v2/posts?before=' + date;
    }
}

