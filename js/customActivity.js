'use strict';

// Postmonger connection setup
var connection = new Postmonger.Session();

// Global payload object
var payload = {};

// Waits for the document to be ready, then calls onRender
$(window).ready(onRender);

// Subscribes to Journey Builder events
connection.on('initActivity', initialize);
connection.on('clickedNext', save);


/**
 * The client-side code that executes when the Custom Activity editor is rendered.
 */
function onRender() {
    // Signal to Journey Builder that the UI is ready
    connection.trigger('ready');
}

/**
 * This function is called when Journey Builder initializes the activity.
 * It receives the previously saved configuration.
 * @param {object} data - The activity's saved configuration.
 */
function initialize(data) {
    if (data) {
        payload = data;
    }

    var inArguments = payload['arguments'].execute.inArguments || [];
    var args = {};

    // We transform the inArguments array into a more usable key-value object.
    // E.g., from [{ "name": "John" }, {"lastName": "Doe"}] to { "name": "John", "lastName": "Doe" }
    inArguments.forEach(arg => {
        for (let key in arg) {
            args[key] = arg[key];
        }
    });

    // Now, we use jQuery to populate the form fields with the saved values.
    $('#name').val(args.name || '');
    $('#lastName').val(args.lastName || '');
    $('#edad').val(args.edad || '');
}

/**
 * This function is called when the user clicks "Next" or "Done" in the Journey Builder UI.
 * It saves the current configuration of the activity.
 */
function save() {
    // Read the current values from the form fields using jQuery.
    var name = $('#name').val();
    var lastName = $('#lastName').val();
    // Ensure 'edad' is treated as a number if it has a value.
    var edad = $('#edad').val() ? parseInt($('#edad').val(), 10) : '';

    // We build the inArguments array in the format that Journey Builder expects.
    payload['arguments'].execute.inArguments = [
        { "name": name },
        { "lastName": lastName },
        { "edad": edad }
    ];
    
    // Mark the activity as configured and save it.
    payload['metaData'] = payload['metaData'] || {};
    payload['metaData'].isConfigured = true;

    connection.trigger('updateActivity', payload);
}