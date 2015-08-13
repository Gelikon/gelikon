function LoadFile(file){
    var link = document.createElement("link");
    link.setAttribute("rel", "stylesheet");
    link.setAttribute("type", "text/css");
    link.setAttribute("href", file);
    document.getElementsByTagName("head")[0].appendChild(link)
    }    function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
    }
var num = getRandomInt(1,3);
var link = 'css/main'+num+'.css';
//$("head script").eq(0).before($("<link rel='stylesheet' href='"+link+"' type='text/css' media='screen' />"));
LoadFile(link);

