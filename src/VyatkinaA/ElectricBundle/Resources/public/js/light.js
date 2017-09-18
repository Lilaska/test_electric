/**
 * Created by Alexandra on 07.09.2017.
 */
$(document).ready(function()
{
    $('div.field_place').on('click', '.cell-field', function () {
        var fields_on = [];
        var cell = $(this);
        var step = $('input[name="counter"]').val();
        if(!$(cell).hasClass('on')) {
            $.ajax({
                type: 'POST',
                url: 'check',
                data: {
                    'id': $(this).attr("id")
                },
                success: function (data) {
                    fire(data);
                    if(data.is_win) is_win();
                    if(data.joker) setTimeout(joker(data.joker), 3000);
                },
                dataType: "JSON"
            });
        }else{
            return false;
        }
    });

    $('#the_best').on('click', the_best);

    $('#new_game').on('click', newGame);

    function fire(data) {
        updateField(data.field_template);
        updateCounter(data.counter_template);
    }

    function updateCounter(template) {
        $('div.counter_place').html(template);
    }

    function updateField(template) {
        // for (var i = 0; i < data.fields.length; i++) {
        //     $('#' + data.fields[i]).toggleClass('on');
        // }
        $('div.field_place').html(template);
    }

    function newGame(){
        $.ajax({
            type: 'POST',
            url: 'new',
            // data: {'step': step, 'username': username},
            success: function (data) {
                updateCounter(data.counter_template);
                updateField(data.field_template);

            },
            dataType: "JSON"
        })
    }

    function is_win() {
            var callback = function() {
                var username = $('#name').val();
                if (username != '') {
                    $.ajax({
                            type: 'POST',
                            url: 'save',
                            data: {'username': username},
                            success: function () {
                                showModal('User saved successfully', 'Save');
                            },
                            dataType: "JSON"
                        }
                    )
                }
            };
            getSaveModal(callback);
            return true;
    }

    function getSaveModal(clbk){
        $.ajax({
            type: 'POST',
            url: 'save',
            success: function (data) {
                var save_form = data.save_template;
                showModal(save_form, 'You win!', 'Ok', clbk);
            },
            dataType: "JSON"
        });
    }

    function joker(joker) {
        $('#' + joker).removeClass('on');
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
        $('#close_btn').off('click');
        if(typeof clbk == 'function'){
            $('#close_btn').on('click', clbk);
        }
        $('#the_best_of_the_best').modal('handleUpdate');
        $('#the_best_of_the_best').modal('show');
    }
});
