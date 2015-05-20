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

    it("initializes modal", function (done) {
        var modal = $('#modal-form'),
            button = modal.find('button[type=submit]');

        spyOn($.fn, 'ajaxSubmit').and.callFake(function (params) {
            expect(button).toHaveClass('disabled');
            expect(button).toHaveProp('disabled', true);

            params.success('FOOBAR');
            expect(modal.find('.modal-body')).toHaveText('FOOBAR');

            done();
        });

        initModalForm(modal);
        button.trigger('click');
    });

    it("sets focus", function () {
        var modal = $('#modal-form');
        modal.modal('show');
        setFormFocus(modal.find('form'));

        expect($('#field')).toBeFocused();
    });

    it("validates correct form field", function (done) {
        spyOn($, 'ajax').and.callFake(function (params) {
            expect(params['url']).toBe('/example/form');
            expect(params['data']).toEqual({
                query: 'validate',
                field: 'field',
                form: {
                    security: 'hash',
                    field: 'foobar',
                },
            });

            params.success({
                valid: true,
                messages: [],
            });

            expect($('#field').closest('.form-group')).not.toHaveClass('has-error');
            done();
        });

        validateFormField($('#field'));
    });

    it("validates invalid form field", function (done) {
        spyOn($, 'ajax').and.callFake(function (params) {
            expect(params['url']).toBe('/example/form');
            expect(params['data']).toEqual({
                query: 'validate',
                field: 'field',
                form: {
                    security: 'hash',
                    field: 'foobar',
                },
            });

            params.success({
                valid: false,
                messages: [ 'MESSAGE' ],
            });

            var group = $('#field').closest('.form-group');
            expect(group).toHaveClass('has-error');
            expect(group.find('.help-block li')).toHaveText('MESSAGE');
            done();
        });

        validateFormField($('#field'));
    });
});
