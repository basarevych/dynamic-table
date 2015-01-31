/*
    Example: <input data-on-enter="alert('foobar')">

    When the user presses Enter key they will see an alert
*/
$(document).on('keypress', '[data-on-enter]', function (e) {
    if (e.keyCode == 13) {
        eval($(this).attr('data-on-enter'));
        return false;
    }
});

/*
    Example: <input data-on-blur="alert('foobar')">

    0.5 s delayed alert when focus leaves the input
*/
$(document).on('blur', '[data-on-blur]', function (e) {
    var code = $(this).attr('data-on-blur');
    setTimeout(function () { eval(code); }, 500);
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
        field: 'my-input',
        form: {
            'security': 'scrf value',
            'my-input': 'the value',
            'id': '123',
            // ... rest of the form controls
        }
    }
    'field' field is the name of the control to be validated, 'form' field is all the form controls.

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

    var data = {};
    $.each(form.serializeArray(), function (index, item) {
        data[item['name']] = item['value'];
    });

    $.ajax({
        url: form.attr('action'),
        data: {
            query: 'validate',
            field: name,
            form: data
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
}
