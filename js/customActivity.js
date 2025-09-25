'use strict';

var connection = new Postmonger.Session();
var payload = {};
var authTokens = {};
var lastStepEnabled = false;

// Inicialización de la actividad
$(window).ready(onRender);

connection.on('initActivity', initialize);
connection.on('clickedNext', save);

function onRender() {
    connection.trigger('ready');
}

function initialize(data) {
    if (data) {
        payload = data;
    }

    var inArguments = payload['arguments'].execute.inArguments || [];
    var args = {};
    inArguments.forEach(arg => {
        for (let key in arg) {
            args[key] = arg[key];
        }
    });

    // Rellenar el formulario con los datos guardados
    $('#channel-number').val(args.channelNumber || '');
    $('#auth-token').val(args.authToken || '');
    $('#template-name').val(args.templateName || '');
    $('#template-params').val(args.templateParams || '');
}

function save() {
    // Leer los valores del formulario
    var channelNumber = $('#channel-number').val();
    var authToken = $('#auth-token').val();
    var templateName = $('#template-name').val();
    var templateParams = $('#template-params').val();

    // Guardar los valores en el payload de Journey Builder
    payload['arguments'].execute.inArguments = [{
        "channelNumber": channelNumber,
        "authToken": authToken,
        "templateName": templateName,
        "templateParams": templateParams,
        "phoneNumber": "{{Contact.Attribute.DE.PhoneNumber}}" // Dato del contacto que viaja en el Journey
    }];

    payload.metaData = payload.metaData || {};
    payload.metaData.isConfigured = true;

    connection.trigger('updateActivity', payload);
}