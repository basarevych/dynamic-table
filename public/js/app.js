/*
    Example: <input data-on-enter="alert($(this).val())">

    When the user presses Enter key they will see an alert
*/
$(document).on('keypress', '[data-on-enter]', function (e) {
    if (e.keyCode == 13) {
        eval($(this).attr('data-on-enter'));
        return false;
    }
});

/*
    This will initiate focus for the form
*/
function setFormFocus(form) {
    if (!form.is(':visible'))
        return;

    var parents = form.find('.has-error');
    if (parents.length == 0) 
        parents = form;

    parents.find('.form-control, input')
          .each(function (index, element) {
                var el = $(element);
                if (el.is(':visible') && !el.prop('disabled') && !el.prop('readonly')) {
                    el.focus();
                    return false;
                }
          });
}

/*
    Validate a form field (server-side).

    We expect the form to be created like this:
    <form action="the/action/here">
        <div class="form-group">
            <label class="col-sm-4 control-label" for="my-input">
                The label
            </label>
            <div class="col-sm-8">
                <input type="text" class="form-control" name="my-input" id="my-input">
                <div class="help-block"></div>
            </div>
        </div>
    </form>

    The following will validate the field and display errors in the help-block div:
    validateFormField($('#my-input'));
        
    Validation request is sent as GET to form 'action' address with the following data:
    {
        query: 'validate',
        name: 'my-input',
        value: 'the current value of the input',
        hidden: {
            security: 'scrf value',
            id: 123
        }
    }
    'hidden' field here is all the form input[type=hidden].

    When the field is valid the server should respond with json data:
    {
        valid: true,
        messages: []
    }

    And when the field is invalid:
    {
        valid: false,
        messages: [
            'error message 1,
            'error message 2
        ]
    }
*/
function validateFormField(element) {
    var form = element.closest('form');
    var name = element.attr('name');
    var value = element.val();
    var timestamp = new Date().getTime();

    var hidden = {};
    form.find('[type=hidden]')
        .each(function (index, el) {
            el = $(el);
            hidden[el.attr('name')] = el.val();
        });

    $.ajax({
        url: form.attr('action'),
        data: {
            query: 'validate',
            name: name,
            value: value,
            hidden: hidden
        },
        success: function (data) {
            var validation = form.data('validation-' + name);
            if (typeof validation == 'undefined') {
                validation = {
                    valid: true,
                    timestamp: 0,
                    messages: []
                };
            }

            // Handle out-of-order replies
            if (timestamp < validation['timestamp'])
                return;

            validation = {
                valid: data.valid,
                timestamp: timestamp,
                messages: data.messages
            };
            form.data('validation-' + name, validation);

            var group = element.closest('.form-group');
            var helpBlock = group.find('div.help-block');

            if (validation['valid']) {
                group.removeClass('has-error');
                helpBlock.empty();
            } else {
                group.addClass('has-error');

                var newBlock = $('<div class="help-block"></div>');
                var ul = $('<ul class="list-unstyled icon-list error-list"></ul>');
                $.each(validation['messages'], function (index, item) {
                    $('<li></li>')
                        .text(item)
                        .appendTo(ul);
                });
                newBlock.append(ul);
                helpBlock.replaceWith(newBlock);
            }
        },
    });
}

/*
    This function will install event handlers for the modal ajax form:
    * 'submit' buttons will ajaxSubmit() the form
    * Fields with class 'validate-me' will be validated on focus change
    * If focus is set to 'cancel-validation' class element, validation will not run
*/
function initModalForm(modal)
{
    modal.find('button[type=submit]')
        .off('click')
        .on('click', function () {
            modal.find('form').ajaxSubmit({ // jQuery form plugin
                success: function (data) {
                    modal.find('.modal-body').html(data);
                }
            });
        });

    modal.find('.validate-me')
         .off('focusout')
         .on('focusout', function () {
            $(this).data('dirty', true);    // On loosing focus mark field to be validated
         });

    modal.find('.cancel-validation')
         .off('focusin')
         .on('focusin', function () {
            return false;                   // Stop the bubble
         });

    // Validate dirty fields on focus change
    modal.on('focusin', function () {
        modal.find('.validate-me')
             .each(function (index, element) {
                var el = $(element);
                if (el.data('dirty')) {
                    el.data('dirty', false);
                    validateFormField(el);
                }
             });
    });
}
