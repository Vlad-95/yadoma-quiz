$(document).ready(function() {
    for (var i = utm_params.length - 1; i >= 0; i--) {
        utm_name = utm_params[i]
        utm_value = getCookie(utm_name);

        $('input[name=' + utm_name + ']').val(utm_value);
    }
});

function getCookie(cname) {
    name = cname + "=";
    ca = document.cookie.split(';');

    for(var i = 0; i < ca.length; i++) {
        c = ca[i];

        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }

        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }

    return "";
}
