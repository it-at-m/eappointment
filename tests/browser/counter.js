import { Selector } from 'testcafe';
import { ClientFunction } from 'testcafe';
import Config from './lib/config';
import { userRegular } from './lib/roles';

const getPageUrl = ClientFunction(() => window.location.href);

fixture `Counter`
    .page `${Config.baseUrl}/`;

test('NewAppointment', async t => {
    await t
        .useRole(userRegular)
        .navigateTo(`${Config.baseUrl}/workstation/select/`)
        .typeText('input[name=workstation]', "\r", {replace: true})
        .click('button.button-login')
        .expect(getPageUrl()).contains('counter')
        .click('a[data-date="2016-05-30"]')
        .wait(200)
        .expect(Selector('input#process_selected_date').value).eql('2016-05-30')
        .click('button.process-reserve')
    ;
});
