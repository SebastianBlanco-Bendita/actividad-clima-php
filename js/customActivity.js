'use strict';

// Conexión con Journey Builder a través de Postmonger
var connection = new Postmonger.Session();

// Objeto global para almacenar la configuración de la actividad
var payload = {};

// Se ejecuta cuando la interfaz de usuario está lista
$(window).ready(onRender);

// Escucha los eventos de Journey Builder
connection.on('initActivity', initialize);
connection.on('clickedNext', save);

/**
 * Se llama cuando la interfaz de la actividad se ha cargado.
 */
function onRender() {
    // Notifica a Journey Builder que la interfaz está lista.
    connection.trigger('ready');
}

/**
 * Se llama cuando Journey Builder inicia la actividad.
 * Carga los datos previamente guardados.
 * @param {object} data - La configuración guardada de la actividad.
 */
function initialize(data) {
    if (data) {
        payload = data;
    }

    var inArguments = payload['arguments'] && payload['arguments'].execute && payload['arguments'].execute.inArguments ? payload['arguments'].execute.inArguments : [];
    var args = {};

    // Convierte el array de inArguments en un objeto clave-valor para un uso más fácil
    inArguments.forEach(arg => {
        for (let key in arg) {
            args[key] = arg[key];
        }
    });

    // Rellena los campos del formulario con los valores guardados
    $('#channel-number').val(args.channelNumber || '');
    $('#auth-token').val(args.authToken || '');
    $('#template-name').val(args.templateName || '');
    $('#template-params').val(args.templateParams || '');
}

/**
 * Se llama cuando el usuario hace clic en "Siguiente" o "Hecho".
 * Guarda la configuración actual de la actividad.
 */
function save() {
    // Obtiene los valores actuales de los campos del formulario
    var channelNumber = $('#channel-number').val();
    var authToken = $('#auth-token').val();
    var templateName = $('#template-name').val();
    var templateParams = $('#template-params').val();
    
    // El número de teléfono del contacto se obtiene dinámicamente de la Data Extension del Journey.
    // Asegúrate de que la ruta sea correcta para tu modelo de datos.
    var phoneNumber = '{{Contact.Attribute.DE.PhoneNumber}}';

    // Construye el payload en la estructura que Journey Builder espera para 'execute'
    payload['arguments'] = payload['arguments'] || {};
    payload['arguments'].execute = payload['arguments'].execute || {};
    payload['arguments'].execute.inArguments = [
        { "channelNumber": channelNumber },
        { "authToken": authToken },
        { "templateName": templateName },
        { "templateParams": templateParams },
        { "phoneNumber": phoneNumber }
    ];

    // Marca la actividad como configurada
    payload['metaData'] = payload['metaData'] || {};
    payload['metaData'].isConfigured = true;

    // Envía la configuración actualizada a Journey Builder
    connection.trigger('updateActivity', payload);
}
