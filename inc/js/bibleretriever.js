var biblegateway; 

jQuery(document).ready(function(){
    biblegateway = new BibleGateway('NKJV');
});

class BibleGateway{
    
    constructor(version){
        this.version = version; 

    }

    getURL(verse){
        verse = encodeURIComponent(verse);
        return location.origin + '/wp-content/plugins/lutheran-herald/bible-reading-api.php?lookup=' + verse;
    }

    loadAllVerses(){
        var verses = [];
        jQuery.each(jQuery('[data-bible]'), function(index, item){
            var verse = item.dataset.bible;
            verses.push(verse);
        });
        for(var i=0; i<verses.length; i++){
            
            console.log(this.getVerse(verses[i]));
        }
        
    }

    getVerse(verse){
        var response = jQuery.get(this.getURL(verse));
        return response; 
    }
}

