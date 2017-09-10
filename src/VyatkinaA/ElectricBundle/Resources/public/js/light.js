/**
 * Created by Alexandra on 07.09.2017.
 */
$('.cell-field').on('click', function() {
    var fields_on = [];
    var cell = $(this);
    $.ajax({
        type: 'POST',
        url: 'check',
        data: {
            'id': $(this).attr("id"),
            'step': $('.counter').html(),
        },
        success: function (data) {
            fire(data, cell);

            $('.cell-field.on').map(function (el, val) {
                fields_on.push(val.getAttribute('id'));
            });
            if(!is_win(fields_on)){
                //joker(fields_on);
            }

        },
        dataType: "JSON"
    });
});

function fire(data, cell){
    for (var i = 0; i < data.fields.length; i++) {
        $('#' + data.fields[i]).toggleClass('on');
    }
    ;
    $(cell).toggleClass('on');
    $('.counter').html(data.step);
}

function is_win(fields_on) {
    if (fields_on.length == 25) {
        var step = $('.counter').html();
        var username = prompt('u win! result: '+step, 'username');
        if(step > 0 && username > 0) {
            $.ajax({
                    type: 'POST',
                    url: 'save',
                    data: {'step': step, 'username': username},
                    success: function () {
                        alert('user saved');
                    },
                    dataType: "JSON"
                }
            )
        }

        return true;
    }else{
        return false;
    }
}

function joker(fields_on) {
    $.ajax({
        type: 'POST',
        url: 'joker',
        data: {
            'fields_on': fields_on
        },
        success: function (data) {
            if (data.answer) {
                $('#' + data.joker).removeClass('on');
            }
        },
        dataType: "JSON"
    });
}
