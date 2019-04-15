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
        .expect(Selector('.queue-table .header .title').textContent).contains('01. April 2016')
        .expect(Selector('.calendar-page .header').textContent).contains('April 2016')
        .expect(Selector('.queue-info-appointment-times').textContent).contains('08:00')
    ;
});

test('NewAppointment', async t => {
    await t
        .useRole(userRegular)
        .navigateTo(`${Config.baseUrl}/workstation/select/`)
        .typeText('input[name=workstation]', " ", {replace: true})
        .click('button.button-login')
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
    const dialogContent = await Selector('.dialog .body').textContent;
    const processId = dialogContent.match(/\d{6,}/)[0];
    await t
        .expect(Selector('.dialog .body').textContent).contains('Vorgangsnummer', 'use right term for an appointment')
        .click('.lightbox .dialog .button-ok')
        .expect(Selector('.queue-table a[data-id="'+processId+'"]').exists).ok()
        .click('button.process-delete')
        .click('.lightbox .dialog .button-ok')
        .click('.lightbox .dialog .button-ok')
        .click('.queue-table .reload')
        .wait(500)
        .expect(Selector('.queue-table a[data-id="'+processId+'"]').exists).notOk()
    ;
});
