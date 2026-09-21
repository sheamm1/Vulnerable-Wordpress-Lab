/* Simple File List Javascript | Mitchell Bennis | Element Engage, LLC | mitch@elementengage.com */

// console.log('eeSFL Frontside Head JS Loaded');

// var eeSFL_FileFormats = '';
var eeSFL_isTouchscreen = false;
var eeSFL_FileID = false;
var eeSFL_ID = 1;

// File Size Formatting
function eeSFL_GetFileSize(bytes, si) {
    
    var thresh = si ? 1000 : 1024;
    
    if(Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    
    var units = si
        ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
        : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
    var u = -1;
    
    do {
        bytes /= thresh;
        ++u;
    } while(Math.abs(bytes) >= thresh && u < units.length - 1);
    
    return bytes.toFixed(1)+' '+units[u];
}