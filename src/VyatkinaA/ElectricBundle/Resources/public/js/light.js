/**
 * Created by Alexandra on 07.09.2017.
 */
$(document).ready(function()
{
    $('.cell-field').on('click', function () {
        var fields_on = [];
        var cell = $(this);
        var step = $('input[name="counter"]').val();
        $.ajax({
            type: 'POST',
            url: 'check',
            data: {
                'id': $(this).attr("id"),
                'step': step,
            },
            success: function (data) {
                fire(data, cell);

                $('.cell-field.on').map(function (el, val) {
                    fields_on.push(val.getAttribute('id'));
                });
                if (!is_win(fields_on)) {
                    joker(fields_on);
                }

            },
            dataType: "JSON"
        });
    });

    $('#the_best').on('click', function () {
        the_best();
    });

    function fire(data, cell) {
        for (var i = 0; i < data.fields.length; i++) {
            $('#' + data.fields[i]).toggleClass('on');
        }
        ;
        $(cell).toggleClass('on');
        // $('input[name="counter"]').val(data.step)
        updateCounter(data.counter_template);
    }

    function updateCounter(template) {
        $('div.counter_place').html(template);
    }

    function is_win(fields_on) {
        if (fields_on.length == 25) {
            var step = $('input[name="counter"]').val();
            var callback = function() {
                var username = $('#name').val();
                if (step > 0 && username != '') {
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
            }
            getSaveModal(step, callback);
            return true;
        } else {
            return false;
        }
    }

    function getSaveModal(step, clbk){
        $.ajax({
            type: 'POST',
            url: 'save',
            data: {'step' : step},
            success: function (data) {
                var save_form = data.save_template;
                showModal(save_form, 'You win!', 'Ok', clbk);
            },
            dataType: "JSON"
        });
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

    function the_best() {
        $.ajax({
            type: 'POST',
            url: 'best',
            success: function (data) {
                showModal(data, 'The best of the best', 'Close');
            },
            dataType: "HTML"
        });
    }

    function showModal(content, title, buttons, clbk) {
        $('div.modal-body').html(content);
        $('h4.modal-title').html(title);
        $('.btn').html(buttons);
        if(typeof clbk == 'function'){
            $('#close_btn').on('click', clbk);
        }
        $('#the_best_of_the_best').modal('handleUpdate');
        $('#the_best_of_the_best').modal('show');
    }
});
