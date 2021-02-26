const $ = require('jquery');
require('bootstrap');
require('bootstrap/dist/css/bootstrap.css');
require('../styles/cultural_offers.css');

$(document).ready(function () {
    $('#offers_categories').change(function () {
        $('#offers_categories_form').submit();
    });
});
