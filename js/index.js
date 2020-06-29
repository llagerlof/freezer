$(document).ready(function() {
    // Load all configured connections and fill the combobox.
    $.ajax({
        dataType: 'json',
        url: 'json/freezer.combo.json.php',
        success: function(result) {
            $.each(result, function(i, config) {
                $('#configs').append(`<option value="${config}">${config}</option>`);
            });
        },
        error: function(result) {
            console.log(result);
        }
    });

    function loading(show) {
        if (show) {
            $('#freeze, #whatsnew, #configs').prop('disabled', true);
            $('.lds-ellipsis').css('display', 'inline-block');
            $('#messages, #errors, #diff').html('');
        } else {
            $('.lds-ellipsis').css('display', 'none');
            $('#freeze, #whatsnew, #configs').prop('disabled', false);
        }
    }

    // Store on the server all last records' IDs.
    $('#freeze').on('click', function() {
        loading(true);
        $.ajax({
            dataType: 'json',
            url: 'json/freezer.freeze.json.php',
            data: {
                'config': $('#configs').find(':selected').text()
            },
            success: function(result) {
                if (result && Array.isArray(result.messages)) {
                    result.messages.forEach(function(v, i) {
                        $('#messages').append(`<p>&gt; ${v}</p>`);
                    });
                }
                if (result && Array.isArray(result.errors)) {
                    result.errors.forEach(function(v, i) {
                        $('#errors').append(`<p>&gt; ${v}</p>`);
                    });
                }
                loading(false);
            },
            error: function(result) {
                console.log(result);
                $('#errors').append(`<p>Server error: ${result.responseText}</p>`);
                loading(false);
            }
        });
    });

    // Load all new records between the last freeze and now.
    $('#whatsnew').on('click', function() {
        loading(true);
        $.ajax({
            dataType: 'json',
            url: 'json/freezer.diff.json.php',
            data: {
                'config': $('#configs').find(':selected').text()
            },
            success: function(result) {
                if (result == null || result.length == 0) {
                    $('#messages').append('No new records since last [Freeze].');
                    loading(false);
                    return;
                }
                var output = '<div class="container-table-diff">';
                $.each(result, function(tablename, v) {
                    var loaded_headers = false;
                    output += `<h3>${tablename}</h3>`;
                    output += '<div>';
                    output += '<table class="pure-table pure-table-bordered">';
                    $.each(this, function(i2, v2) {
                        if (!loaded_headers) {
                            output += '<thead><tr>';
                            $.each(this, function(column, cell) {
                                output += `<td>${column}</td>`;
                            });
                            output += '</tr></thead>';
                            loaded_headers = true;
                        }
                        output += '<tr>';
                        $.each(this, function(i3, cell) {
                            output += `<td>${cell}</td>`;
                        });
                        output += '</tr>';
                    });
                    output += '</table>';
                    output += '</div>';
                });
                output += '</div>';
                $('#diff').append(output);
                loading(false);
            },
            error: function(result) {
                console.log(result);
                $('#errors').append(`<p>Server error: ${result.responseText}</p>`);
                loading(false);
            }
        });
    });
});
