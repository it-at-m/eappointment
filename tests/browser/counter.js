/* global fixture, test */
import { Selector } from 'testcafe';
import Config from './lib/config';
import { userRegular } from './lib/roles';
import { getPageUrl } from './lib/client';

fixture `Counter`
    .page `${Config.baseUrl}/`
    .httpAuth(Config.credentials)
;

test('Loaded', async t => {
    await t
        .useRole(userRegular)
        .navigateTo(`${Config.baseUrl}/counter/`)
        .expect(Selector('.queue-table .board__actions .date').textContent).contains('01. April 2016')
        .expect(Selector('.calendar-page .header').textContent).contains('April 2016')
        .expect(Selector('.queue-info-appointment-times').textContent).contains('08:00')
    ;
});

test('NewAppointment', async t => {
    await t
        .useRole(userRegular)
        .navigateTo(`${Config.baseUrl}/workstation/select/`)
        .typeText('input[name=workstation]', " ", {replace: true})
        .click('button.type-login')
        .expect(getPageUrl()).contains('counter')
        .click('a[data-date="2016-05-30"]')
        .wait(200)
        .expect(Selector('input#process_selected_date').value).eql('2016-05-30', 'date should change in form')
        .click('button.process-reserve')
        .expect(Selector('.has-error .required-symbol').textContent).eql('*', 'expect at least one required field on appointments')
        .click('.service-checkbox input')
        .typeText('input[name=familyName]', "Test e2e", {replace: true})
        .click('button.process-reserve')
    ;
    const dialogContent = await Selector('.dialog .message__body').textContent;
    const processId = dialogContent.match(/\d{6,}/)[0];
    await t
        .expect(Selector('.dialog .message__body').textContent).contains('Vorgangsnummer', 'use right term for an appointment')
        .click('.lightbox .dialog .button-ok')
        .expect(Selector('.queue-table a[data-id="'+processId+'"]').exists).ok()
        .click(Selector('.queue-table a[data-id="'+processId+'"].process-edit'))
        .click('button.process-delete')
        .click('.lightbox .dialog .button-ok')
        .click('.lightbox .dialog .button-ok')
        .click('.queue-table .reload')
        .wait(500)
        .expect(Selector('.queue-table a[data-id="'+processId+'"]').exists).notOk()
    ;
});

test('ValidationQueue', async t => {
    await t
        .useRole(userRegular)
        .navigateTo(`${Config.baseUrl}/workstation/select/`)
        .typeText('input[name=workstation]', "33", {replace: true})
        .click(Selector('select[name="scope"]'))
        .click(Selector('select[name="scope"] option').withAttribute('value', "142")) //BA Wilmersdorfer Str.
        .click('button.type-login')
        .expect(getPageUrl()).contains('workstation')
        // Check if queue entry is without validation as expected
        .click('button.process-queue')
        ;
    const processId = await Selector('.dialog .message__body button[name="printWaitingNumber"]').getAttribute("data-id");
    await t
        .expect(Selector('.dialog .message__body').textContent).contains('Wartenummer', 'use right term for an queue entry')
        .click('.lightbox .dialog .button-ok')
    ;
    const queueStatusVisible = await Selector('div.appointmentsOnly input[type=checkbox]').checked;
    if (!queueStatusVisible) {
        await t
            .click('div.appointmentsOnly input[type=checkbox]')
        ;
    }
    await t
        .expect(Selector('.queue-table a[data-id="'+processId+'"]').exists).ok()
        .click(Selector('.queue-table a[data-id="'+processId+'"].process-delete'))
        .expect(Selector('.dialog .board__body').textContent).contains(processId, 'use right term for an appointment')
        .click('.lightbox .dialog .button-abort')
        .click(Selector('.queue-table a.reload'))
        .expect(Selector('.queue-table a[data-id="'+processId+'"]').exists).ok()
        .click(Selector('.queue-table a[data-id="'+processId+'"].process-delete'))
        .click('.lightbox .dialog .button-ok')
        .click('.lightbox .dialog .button-ok')
        .click(Selector('.queue-table a.reload'))
        .expect(Selector('.queue-table a[data-id="'+processId+'"]').exists).notOk()
    ;
});

test('ValidationAppointment', async t => {
    await t
        .useRole(userRegular)
        .navigateTo(`${Config.baseUrl}/workstation/select/`)
        .typeText('input[name=workstation]', "33", {replace: true})
        .click(Selector('select[name="scope"]'))
        .click(Selector('select[name="scope"] option').withAttribute('value', "142")) //BA Wilmersdorfer Str.
        .click('button.type-login')
        .expect(getPageUrl()).contains('workstation')
        // Now check appointment
        .click('a[data-date="2016-05-30"]')
        .wait(200)
        .expect(Selector('input#process_selected_date').value).eql('2016-05-30', 'date should change in form')
        .click('button.process-reserve')
        .expect(Selector('.has-error .required-symbol').textContent).eql('*', 'expect at least one required field on appointments')
        .click('.service-checkbox input')
        .typeText('input[name=familyName]', "Test e2e", {replace: true})
        // Check telephone validation
        .typeText('input[name=telephone]', "Test e2e", {replace: true})
        .click('button.process-reserve')
        .expect(Selector('.has-error input[type=text]').getAttribute('name')).eql('telephone')
        .typeText('input[name=telephone]', "012345678901", {replace: true})
        // Check mail validation
        .typeText('input[name=email]', "012345678901", {replace: true})
        .click('button.process-reserve')
        .expect(Selector('.has-error input[type=text]').getAttribute('name')).eql('email')
        .typeText('input[name=email]', "test@example.com", {replace: true})
        .click('button.process-reserve')
        ;
    const processId = await Selector('.dialog .message__body button[name="printWaitingNumber"]').getAttribute("data-id");
    await t
        .expect(Selector('.dialog .message__body').textContent).contains('Vorgangsnummer', 'use right term for an queue entry')
        .click('.lightbox .dialog .button-ok')
        .expect(Selector('.queue-table a[data-id="'+processId+'"]').exists).ok()
        .click(Selector('.queue-table a[data-id="'+processId+'"].process-delete'))
        .click('.lightbox .dialog .button-ok')
        .click('.lightbox .dialog .button-ok')
        ;
});
