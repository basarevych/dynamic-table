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
