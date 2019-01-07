window.onload = function () {
    if (!window.OneSignal)
        throw Error("Please import OneSignal javascript. Read more: https://documentation.onesignal.com/docs/web-push-custom-code-setup#section--span-class-step-step-3-span-upload-onesignal-sdk ");

    if (typeof (onesignal_tag_params) == "undefined" || onesignal_tag_params == null || onesignal_tag_params == '') {
        throw Error("Missing onesignal_tag_params variable!");
    } else if(typeof(onesignal_tag_params.trackingUrl) == 'undefined' || onesignal_tag_params.trackingUrl == '' || onesignal_tag_params.trackingUrl == null) {
        throw Error("Missing properties trackingUrl in onesignal_tag_params variable!");
    }

    OneSignal.getUserId(function (id) {
        if(typeof(id) != 'undefined' && id != '' && id != null) {
            var playerId = id;
            setCookie(OneSignalPlayerId, playerId, 30);
        } else if(getCookie('OneSignalPlayerId') != ''){
            var playerId = getCookie('OneSignalPlayerId');
        }

        if (typeof (playerId) != 'undefined' && playerId != '' && playerId != null) {
            onesignal_tag_params.playerId = playerId;
            var trackingUrl = onesignal_tag_params.trackingUrl;
            delete onesignal_tag_params.trackingUrl;
            var xhttp = new XMLHttpRequest();
            xhttp.open("POST", trackingUrl, true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            // xhttp.onreadystatechange = function() {
            //     if (this.readyState == 4 && this.status == 200) {
            //         console.log("Tracking Complete...");
            //     }
            // }

            xhttp.send(encodeQueryData(onesignal_tag_params));
        }
    });


    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') {
            c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
          }
        }
        return "";
    }

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function encodeQueryData(data) {
        const ret = [];
        for (var d in data)
          ret.push(encodeURIComponent(d) + '=' + encodeURIComponent(data[d]));
        return ret.join('&');
     }
}