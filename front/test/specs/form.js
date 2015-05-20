'use strict'

describe("Form", function() {
    beforeEach(function (done) {
        $.ajax({
            url: 'test/fixtures/form.html',
            dataType: 'html',
            success: function (html) {
                var fixture = $('<div id="fixture"></div>');
                $('body').append(fixture.append(html));
                done();
            }
        });
    });

    afterEach(function () {
        $('#fixture').remove();
    });

    it("sets focus", function () {
        var modal = $('#modal-form');
        modal.modal('show');
        setFormFocus(modal.find('form'));

        expect($('#field')).toBeFocused();
    });
});
