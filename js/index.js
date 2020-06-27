$(document).ready(function() {
    // Load all configured connections and fill the combobox.
    $.ajax({
        dataType: 'json',
        url: 'json/freezer.combo.json.php',
        success: function(result) {
            $.each(result, function(i, config) {
                $('#configs').append(new Option(config, config));
            });
        },
        error: function(result) {
            console.log(result);
        }
    });

    // Store on the server all last records ids.
    $('#freeze').on('click', function(){
        $('#freeze').prop('disabled', true);
        $('#messages, #errors').html('');
        $('#diff').html('');
        $.ajax({
            dataType: 'json',
            url: 'json/freezer.freeze.json.php',
            data: {
                'config': $('#configs').find(':selected').text()
            },
            success: function(result) {
                if (result && Array.isArray(result.messages)) {
                    result.messages.forEach(function(v, i){
                        $('#messages').append('<p>&gt; ' + v + '</p>');
                    });
                }
                if (result && Array.isArray(result.errors)) {
                    result.errors.forEach(function(v, i){
                        $('#errors').append('<p>&gt; ' + v + '</p>');
                    });
                }
                $('#freeze').prop('disabled', false);
            },
            error: function(result) {
                console.log(result);
                $('#errors').append('<p>Server error: ' + result.responseText + '</p>');
                $('#freeze').prop('disabled', false);
            }
        });
    });

    // Load all new records between the last freeze and now.
    $('#whatsnew').on('click', function(){
        $('#whatsnew').prop('disabled', true);
        $('#messages, #errors').html('');
        $('#diff').html('');
        $.ajax({
            dataType: 'json',
            url: 'json/freezer.diff.json.php',
            data: {
                'config': $('#configs').find(':selected').text()
            },
            success: function(result) {
                if (result == null || result.length == 0) {
                    $('#messages').append('No new records since last [Freeze].');
                    $('#whatsnew').prop('disabled', false);
                    return;
                }
                var output = '<div class="container">';
                $.each(result, function(tablename, v) {
                    var loaded_headers = false;
                    output += '<h3>' + tablename + '</h3>';
                    output += '<table style="border: 1px solid black;">';
                    $.each(this, function(i2, v2) {
                        if (!loaded_headers) {
                            output += '<thead><tr>';
                            $.each(this, function(column, cell) {
                                output += '<td>';
                                output += column;
                                output += '</td>';
                            });
                            output += '</tr></thead>';
                            loaded_headers = true;
                        }
                        output += '<tr>';
                        $.each(this, function(i3, cell) {
                            output += '<td>';
                            output += cell;
                            output += '</td>';
                        });
                        output += '</tr>';
                    });
                    output += '</table>';
                });
                output += '</div>';
                $('#diff').append(output);
                $('#whatsnew').prop('disabled', false);
            },
            error: function(result) {
                console.log(result);
                $('#errors').append('<p>Server error: ' + result.responseText + '</p>');
                $('#whatsnew').prop('disabled', false);
            }
        });
    });
});
